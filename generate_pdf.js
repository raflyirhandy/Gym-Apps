const fs = require('fs');

class SimplePDF {
  constructor() {
    this.pages = [];
    this.currentPage = null;
    this.pageWidth = 595.28;  // A4
    this.pageHeight = 841.89; // A4
    this.marginLeft = 40;
    this.marginRight = 40;
    this.marginTop = 40;
    this.marginBottom = 40;
    this.contentWidth = this.pageWidth - this.marginLeft - this.marginRight;
    this.y = this.pageHeight - this.marginTop;
  }

  addPage() {
    this.currentPage = {
      stream: []
    };
    this.pages.push(this.currentPage);
    this.y = this.pageHeight - this.marginTop;
  }

  checkPageBreak(neededHeight) {
    if (this.y - neededHeight < this.marginBottom) {
      this.addPage();
    }
  }

  drawRect(x, y, w, h, fillColor = null, strokeColor = null) {
    let s = '';
    if (fillColor) {
      s += `${fillColor[0]} ${fillColor[1]} ${fillColor[2]} rg `;
    }
    if (strokeColor) {
      s += `${strokeColor[0]} ${strokeColor[1]} ${strokeColor[2]} RG `;
    }
    s += `${x.toFixed(2)} ${(y - h).toFixed(2)} ${w.toFixed(2)} ${h.toFixed(2)} re `;
    if (fillColor && strokeColor) {
      s += 'B\n';
    } else if (fillColor) {
      s += 'f\n';
    } else if (strokeColor) {
      s += 'S\n';
    }
    this.currentPage.stream.push(s);
  }

  drawLine(x1, y1, x2, y2, strokeColor = [0.2, 0.2, 0.2], lineWidth = 1) {
    let s = `${lineWidth} w ${strokeColor[0]} ${strokeColor[1]} ${strokeColor[2]} RG ${x1.toFixed(2)} ${y1.toFixed(2)} m ${x2.toFixed(2)} ${y2.toFixed(2)} l S\n`;
    this.currentPage.stream.push(s);
  }

  escapeText(text) {
    return text.replace(/\\/g, '\\\\').replace(/\(/g, '\\(').replace(/\)/g, '\\)');
  }

  drawText(text, x, y, font = 'F1', size = 10, color = [0.1, 0.1, 0.1]) {
    const safe = this.escapeText(text);
    const s = `BT /${font} ${size} Tf ${color[0]} ${color[1]} ${color[2]} rg 1 0 0 1 ${x.toFixed(2)} ${y.toFixed(2)} Tm (${safe}) Tj ET\n`;
    this.currentPage.stream.push(s);
  }

  addHeading(title, subtitle = null) {
    this.checkPageBreak(50);
    this.drawRect(this.marginLeft, this.y, this.contentWidth, 24, [0.94, 0.96, 0.98], [0.8, 0.85, 0.9]);
    this.drawRect(this.marginLeft, this.y, 4, 24, [0.1, 0.3, 0.7], null);
    this.drawText(title, this.marginLeft + 12, this.y - 16, 'F2', 12, [0.08, 0.2, 0.5]);
    this.y -= 32;
  }

  addParagraph(text, font = 'F1', size = 9.5, color = [0.2, 0.2, 0.2], leading = 13) {
    const words = text.split(' ');
    let line = '';
    const maxChars = Math.floor(this.contentWidth / (size * 0.52));

    for (let i = 0; i < words.length; i++) {
      const testLine = line ? line + ' ' + words[i] : words[i];
      if (testLine.length > maxChars) {
        this.checkPageBreak(leading);
        this.drawText(line, this.marginLeft, this.y - leading, font, size, color);
        this.y -= leading;
        line = words[i];
      } else {
        line = testLine;
      }
    }
    if (line) {
      this.checkPageBreak(leading);
      this.drawText(line, this.marginLeft, this.y - leading, font, size, color);
      this.y -= leading;
    }
    this.y -= 4;
  }

  addKeyValue(key, val, keyWidth = 140) {
    this.checkPageBreak(16);
    this.drawRect(this.marginLeft, this.y, this.contentWidth, 16, [0.98, 0.98, 0.99], [0.9, 0.92, 0.95]);
    this.drawText(key, this.marginLeft + 6, this.y - 12, 'F2', 8.5, [0.15, 0.2, 0.3]);
    this.drawText(val, this.marginLeft + keyWidth, this.y - 12, 'F1', 8.5, [0.1, 0.1, 0.1]);
    this.y -= 16;
  }

  addTable(headers, rows, colWidths) {
    const rowHeight = 18;
    this.checkPageBreak(rowHeight * (rows.length + 1) + 10);

    // Header
    let curX = this.marginLeft;
    for (let c = 0; c < headers.length; c++) {
      this.drawRect(curX, this.y, colWidths[c], rowHeight, [0.9, 0.93, 0.97], [0.75, 0.8, 0.88]);
      this.drawText(headers[c], curX + 4, this.y - 13, 'F2', 8.5, [0.1, 0.2, 0.4]);
      curX += colWidths[c];
    }
    this.y -= rowHeight;

    // Rows
    for (let r = 0; r < rows.length; r++) {
      this.checkPageBreak(rowHeight);
      curX = this.marginLeft;
      const bg = r % 2 === 0 ? [1, 1, 1] : [0.97, 0.98, 0.99];
      for (let c = 0; c < rows[r].length; c++) {
        this.drawRect(curX, this.y, colWidths[c], rowHeight, bg, [0.85, 0.88, 0.92]);
        this.drawText(String(rows[r][c]), curX + 4, this.y - 13, 'F1', 8, [0.2, 0.2, 0.2]);
        curX += colWidths[c];
      }
      this.y -= rowHeight;
    }
    this.y -= 8;
  }

  build() {
    const objects = [];
    const addObject = (content) => {
      objects.push(content);
      return objects.length;
    };

    const font1Obj = addObject('<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>');
    const font2Obj = addObject('<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>');
    const font3Obj = addObject('<< /Type /Font /Subtype /Type1 /BaseFont /Courier >>');

    const pageObjIds = [];
    const contentObjIds = [];

    // Pre-reserve page objects
    for (let i = 0; i < this.pages.length; i++) {
      pageObjIds.push(objects.length + 1);
      objects.push(null); // placeholder
      contentObjIds.push(objects.length + 1);
      objects.push(null); // placeholder
    }

    const pagesObjId = objects.length + 1;
    objects.push(null); // placeholder for pages catalog

    const catalogObjId = addObject(`<< /Type /Catalog /Pages ${pagesObjId} 0 R >>`);

    // Fill page & content objects
    for (let i = 0; i < this.pages.length; i++) {
      const pageNum = i + 1;
      const totalPages = this.pages.length;
      
      // Footer page numbering
      const footerText = `Hal. ${pageNum} dari ${totalPages}  |  MachoGym Cyber Security Report  |  https://machogym.my.id/`;
      const footerStream = `BT /F1 7.5 Tf 0.5 0.5 0.5 rg 1 0 0 1 ${this.marginLeft} 25 Tm (${this.escapeText(footerText)}) Tj ET\n`;
      this.pages[i].stream.push(footerStream);

      const streamData = this.pages[i].stream.join('');
      const streamLen = Buffer.byteLength(streamData, 'utf-8');

      // Update content object
      const cIdx = contentObjIds[i] - 1;
      objects[cIdx] = `<< /Length ${streamLen} >>\nstream\n${streamData}\nendstream`;

      // Update page object
      const pIdx = pageObjIds[i] - 1;
      objects[pIdx] = `<< /Type /Page /Parent ${pagesObjId} 0 R /MediaBox [0 0 ${this.pageWidth} ${this.pageHeight}] /Resources << /Font << /F1 ${font1Obj} 0 R /F2 ${font2Obj} 0 R /F3 ${font3Obj} 0 R >> >> /Contents ${contentObjIds[i]} 0 R >>`;
    }

    // Fill pages catalog
    const kidsStr = pageObjIds.map(id => `${id} 0 R`).join(' ');
    objects[pagesObjId - 1] = `<< /Type /Pages /Kids [${kidsStr}] /Count ${this.pages.length} >>`;

    // Serialize PDF
    let out = '%PDF-1.4\n%\xE2\xE3\xCF\xD3\n';
    const xrefOffsets = [0];

    for (let i = 0; i < objects.length; i++) {
      xrefOffsets.push(Buffer.byteLength(out, 'utf-8'));
      out += `${i + 1} 0 obj\n${objects[i]}\nendobj\n`;
    }

    const xrefOffset = Buffer.byteLength(out, 'utf-8');
    out += `xref\n0 ${objects.length + 1}\n0000000000 65535 f \n`;
    for (let i = 1; i <= objects.length; i++) {
      const off = String(xrefOffsets[i]).padStart(10, '0');
      out += `${off} 00000 n \n`;
    }

    out += `trailer\n<< /Size ${objects.length + 1} /Root ${catalogObjId} 0 R >>\nstartxref\n${xrefOffset}\n%%EOF\n`;
    return Buffer.from(out, 'utf-8');
  }
}

// Instantiate and generate report
const doc = new SimplePDF();
doc.addPage();

// Document Header
doc.drawRect(40, 805, 515.28, 60, [0.1, 0.2, 0.45], null);
doc.drawText('TUGAS PROJECT: PENGEMBANGAN & PENGAMANAN APLIKASI WEB', 52, 782, 'F2', 13, [1, 1, 1]);
doc.drawText('LAPORAN DEPLOYMENT, FITUR, DAN SELF-TESTING KEAMANAN', 52, 767, 'F2', 9.5, [0.98, 0.8, 0.4]);
doc.drawText('Mata Kuliah: Cyber Security  |  Format Pengumpulan: Pra UTS  |  Tahun Akademik 2026/2027', 52, 753, 'F1', 8, [0.9, 0.9, 0.95]);
doc.y = 730;

// Section 1
doc.addHeading('1. IDENTITAS MAHASISWA');
doc.addKeyValue('Nama Lengkap', 'Rafly Irhandy');
doc.addKeyValue('NIM', '[Isi NIM Anda]');
doc.addKeyValue('Kelas / Program Studi', 'Cyber Security / Teknik Informatika');
doc.addKeyValue('Judul Proyek Web', 'MachoGym — Web Management System & Fitness Membership');
doc.y -= 10;

// Section 2
doc.addHeading('2. URL APLIKASI AKTIF (LIVE HOSTING)');
doc.addParagraph('Aplikasi telah berhasil di-deploy secara online dan dapat diakses publik melalui FQDN valid dengan protokol terenkripsi HTTPS:');
doc.addKeyValue('URL Aplikasi Aktif', 'https://machogym.my.id/');
doc.addKeyValue('Status Sertifikat SSL/TLS', 'Aktif & Valid (Let\'s Encrypt / Cloudflare SSL - Port 443)');
doc.addKeyValue('Status Akses', 'Bebas IP mentah / non-localhost (Sesuai Ketentuan Poin 3c)');
doc.y -= 10;

// Section 3
doc.addHeading('3. URL REPOSITORY GIT');
doc.addParagraph('Seluruh kode sumber (Native PHP MVC, assets, dan database schema) diunggah pada Git repository publik berikut:');
doc.addKeyValue('URL Repository GitHub', 'https://github.com/raflyirhandy/Gym-Apps.git');
doc.addKeyValue('Arsitektur Aplikasi', 'Native PHP 8.x (MVC Sederhana) + MySQL PDO');
doc.addKeyValue('Catatan Keamanan Git', 'Kredensial produksi dipisahkan dari repository publik');
doc.y -= 10;

// Section 4
doc.addHeading('4. AKUN UJI COBA (TESTING CREDENTIALS)');
doc.addParagraph('Untuk memfasilitasi pengujian otorisasi dan kontrol akses peran oleh dosen penguji, akun berikut telah disiapkan:');
doc.addTable(
  ['Role / Peran', 'Email / Username', 'Password', 'Cakupan Hak Akses'],
  [
    ['Admin', 'admin@gym.com', 'password123', 'Akses penuh: Member, Trainer, Kelas, Verifikasi Tagihan, Laporan'],
    ['Trainer', 'trainer1@gym.com', 'password123', 'Akses terbatas: Jadwal bimbingan, program workout, chat member'],
    ['Member', 'member1@gym.com', 'password123', 'Akses anggota: Booking kelas, tracking latihan, pembayaran membership']
  ],
  [65, 110, 85, 255.28]
);

// Section 5
doc.addHeading('5. PENJELASAN APLIKASI');
doc.addParagraph('A. Latar Belakang & Tujuan: MachoGym dirancang untuk mengatasi kendala operasional gym fisik konvensional, seperti pencatatan manual, jadwal kelas yang tumpang tindih, dan tidak amannya penyimpanan data anggota. Sistem ini memberikan solusi terpadu berbasis web dengan kontrol akses multi-role (Admin, Trainer, Member).');
doc.addParagraph('B. Fitur Utama: Registrasi mandiri & login aman, CRUD manajemen data (member, trainer, jadwal kelas), verifikasi bukti pembayaran, dan laporan rekapitulasi data pembayaran operasional.');
doc.addParagraph('C. Arsitektur & Tech Stack:');
doc.addTable(
  ['Komponen', 'Teknologi yang Digunakan', 'Fungsi & Peranan'],
  [
    ['Frontend / View', 'HTML5, CSS3 (Glassmorphism), Vanilla JS', 'Antarmuka web responsif, form validasi, AJAX polling chat'],
    ['Backend / Logic', 'Native PHP 8.x (MVC), PHPMailer 7.x', 'Router (index.php), controller bisnis, manajemen sesi PHP'],
    ['Database Layer', 'MySQL 8.0, PHP PDO (Prepared)', 'Penyimpanan data relasional dengan query terparameterisasi'],
    ['Hosting & Network', 'machogym.my.id (Apache + SSL/TLS)', 'Penyedia akses publik terenkripsi SSL/TLS']
  ],
  [110, 155.28, 250]
);

doc.addParagraph('D. Penerapan Aspek Keamanan (Secure Coding):');
doc.addTable(
  ['Mekanisme Keamanan', 'Implementasi pada MachoGym', 'Alasan & Tujuan Proteksi'],
  [
    ['Password Hashing', 'password_hash() / Bcrypt PHP', 'Melindungi password agar tidak plaintext di database'],
    ['Cegah SQL Injection', 'PHP PDO Prepared Statements', 'Memisahkan instruksi SQL dari parameter input user'],
    ['Cegah XSS Attack', 'htmlspecialchars() & strip_tags()', 'Mencegah eksekusi script berbahaya pada browser'],
    ['Manajemen Sesi', '$_SESSION + Cookie HttpOnly/Secure', 'Mencegah pencurian ID sesi via serangan XSS / DOM'],
    ['Role-Based Access', 'Pengecekan $_SESSION[\'role\']', 'Mencegah IDOR dan akses menu admin oleh member'],
    ['Validasi Upload File', 'Cek ekstensi & ukuran file bukti', 'Mencegah upload file berbahaya (PHP web shell)'],
    ['HTTPS & SSL/TLS', 'Sertifikat SSL/TLS valid (443)', 'Melindungi kerahasiaan transmisi data di jaringan publik'],
    ['Secret Isolation', 'Konfigurasi DB & SMTP terpisah', 'Menghindari kredensial produksi bocor di GitHub']
  ],
  [115, 140, 260.28]
);

// Section 6
doc.addHeading('6. POTENSI PENGEMBANGAN KE SKRIPSI');
doc.addParagraph('Topik Rencana Skripsi: "Penerapan Model Zero-Trust Security dan Otomasi Deteksi Anomali Akses pada Sistem Manajemen Pusat Kebugaran Berbasis Web".');
doc.addParagraph('Rumusan Masalah: (1) Bagaimana mengimplementasikan otentikasi adaptif berbasis MFA untuk mengeliminasi risiko pembajakan akun? (2) Bagaimana merancang deteksi dini anomali login mencurigakan pada sistem keanggotaan gym?');
doc.addParagraph('Fitur Lanjutan: Payment Gateway otomatis (Webhook HMAC-SHA512), 2FA/MFA via TOTP Google Authenticator, dan audit trail log anti-tampering.');
doc.y -= 10;

// Section 7
doc.addHeading('7. PENGGUNAAN AI AGENT (SESUAI KETENTUAN POIN 3E)');
doc.addTable(
  ['Aspek', 'Deskripsi Penggunaan AI Agent'],
  [
    ['Alat AI yang Dipakai', 'Google DeepMind Antigravity, Gemini, dan Cursor Agent'],
    ['Bagian Pekerjaan yang Dibantu', 'Perancangan skema relasi database MySQL (gym_db.sql), penyusunan query PHP PDO, styling CSS Glassmorphism, dan penyusunan rancangan skenario uji keamanan.'],
    ['Contoh Prompt Utama', '"Buatkan fungsi login dan proteksi sesi di Native PHP menggunakan PDO prepared statement serta pengecekan role session agar member tidak bisa membuka halaman admin."'],
    ['Metode Verifikasi Mahasiswa', 'Seluruh kode PHP dari AI ditelaah manual, diuji di XAMPP lokal dan server hosting, serta dipastikan tidak memuat kredensial nyata dalam prompt.']
  ],
  [140, 375.28]
);

// Section 8
doc.addHeading('8. HASIL SELF-TESTING KEAMANAN (MINIMAL 5 SKENARIO)');
doc.addTable(
  ['No', 'Skenario Uji', 'Metode Pengujian', 'Hasil Awal', 'Perbaikan & Status Akhir'],
  [
    ['1', 'SQL Injection (SQLi)', 'Injeksi payload "\' OR \'1\'=\'1\' --" di input login', 'LOLOS', 'Diproses via PDO prepared statement; akses ditolak aman.'],
    ['2', 'Cross-Site Scripting (XSS)', 'Injeksi "<script>alert(1)</script>" pada form', 'LOLOS', 'Disanitasi dengan htmlspecialchars() & strip_tags().'],
    ['3', 'Kontrol Akses (IDOR)', 'Akun Member membuka ?page=admin_members', 'LOLOS', 'Validasi $_SESSION[\'role\'] menolak dan me-redirect user.'],
    ['4', 'Session & Logout Test', 'Akses halaman dashboard setelah klik Logout', 'LOLOS', 'session_destroy() menghapus sesi; diarahkan ke login.'],
    ['5', 'HTTPS & Secret Leak', 'Cek sertifikat SSL machogym.my.id & git commit', 'LOLOS', 'SSL valid; tidak ada password/secret produksi di Git.']
  ],
  [18, 115, 142, 60, 180.28]
);

// Section 9
doc.addHeading('9. TANGKAPAN LAYAR (BUKTI IMPLEMENTASI & HOSTING)');
doc.addParagraph('Aplikasi telah aktif secara live pada domain resmi: https://machogym.my.id/');
doc.addTable(
  ['Bukti Tangkapan Layar', 'Deskripsi & Bukti Validitas'],
  [
    ['1. Halaman Autentikasi / Login', 'Menampilkan antarmuka login publik ber-protokol HTTPS dengan validasi input.'],
    ['2. Halaman Pengelolaan Data (CRUD)', 'Dashboard Admin menampilkan tabel member, edit status, dan manajemen kelas.'],
    ['3. Halaman Laporan & Rekapitulasi', 'Tabel rekapitulasi data pembayaran dan status membership aktif.'],
    ['4. Bukti HTTPS & Domain Aktif', 'Browser address bar menunjukkan ikon gembok aman pada https://machogym.my.id/.'],
    ['5. Bukti Konfigurasi Hosting / DNS', 'DNS record domain machogym.my.id terhubung secara stabil ke web server.'],
    ['6. Bukti Self-Testing Keamanan', 'Respon penolakan input payload berbahaya dan log proteksi rate limit.']
  ],
  [160, 355.28]
);

// Section 10
doc.addHeading('10. KENDALA DAN RENCANA PERBAIKAN');
doc.addTable(
  ['Kendala yang Dihadapi', 'Solusi & Tindakan yang Dilakukan'],
  [
    ['Migrasi Koneksi PDO dari Localhost ke Hosting', 'Menyesuaikan parameter host, db_name, username, dan password pada config/database.php serta mengaktifkan redirect HTTPS.'],
    ['Keamanan Folder Upload Bukti Pembayaran', 'Menambahkan validasi ekstensi gambar di PaymentController dan pembatasan eksekusi PHP di direktori upload melalui .htaccess.']
  ],
  [210, 305.28]
);

// Signature
doc.checkPageBreak(80);
doc.y -= 15;
const signX = doc.pageWidth - doc.marginRight - 180;
doc.drawText('Cirebon, 2026', signX, doc.y, 'F1', 9, [0.3, 0.3, 0.3]);
doc.y -= 14;
doc.drawText('Mahasiswa Penyusun,', signX, doc.y, 'F1', 9, [0.3, 0.3, 0.3]);
doc.y -= 45;
doc.drawText('Rafly Irhandy', signX, doc.y, 'F2', 10, [0.1, 0.1, 0.1]);
doc.drawLine(signX, doc.y - 2, signX + 140, doc.y - 2, [0.2, 0.2, 0.2], 0.8);
doc.y -= 14;
doc.drawText('NIM: [Isi NIM Mahasiswa]', signX, doc.y, 'F1', 8.5, [0.4, 0.4, 0.4]);

// Write PDF file
const pdfBuffer = doc.build();
const outputPath = 'C:/xampp/htdocs/gym_management/TugasProject_CyberSec_RaflyIrhandy_NIM.pdf';
fs.writeFileSync(outputPath, pdfBuffer);
console.log('PDF generated successfully at:', outputPath, 'Bytes:', pdfBuffer.length);
