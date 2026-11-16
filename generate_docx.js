const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

function esc(str) {
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&apos;');
}

function p(text, opts = {}) {
  const { bold = false, italic = false, size = 21, color = '334155', align = 'left', spaceAfter = 120, spaceBefore = 0, shading = null } = opts;
  const pPrParts = [
    `<w:spacing w:before="${spaceBefore}" w:after="${spaceAfter}" w:line="276" w:lineRule="auto"/>`
  ];
  if (align !== 'left') {
    pPrParts.push(`<w:jc w:val="${align}"/>`);
  }
  if (shading) {
    pPrParts.push(`<w:shd w:val="clear" w:color="auto" w:fill="${shading}"/>`);
  }

  const rPrParts = [
    `<w:rFonts w:ascii="Calibri" w:hAnsi="Calibri"/>`,
    `<w:sz w:val="${size}"/>`,
    `<w:szCs w:val="${size}"/>`,
    `<w:color w:val="${color}"/>`
  ];
  if (bold) rPrParts.push('<w:b/>');
  if (italic) rPrParts.push('<w:i/>');

  return `<w:p><w:pPr>${pPrParts.join('')}</w:pPr><w:r><w:rPr>${rPrParts.join('')}</w:rPr><w:t xml:space="preserve">${esc(text)}</w:t></w:r></w:p>`;
}

function heading(title) {
  return `<w:p>
    <w:pPr>
      <w:spacing w:before="200" w:after="100"/>
      <w:shd w:val="clear" w:color="auto" w:fill="F1F5F9"/>
      <w:pBdr>
        <w:left w:val="single" w:sz="24" w:space="8" w:color="F97316"/>
        <w:bottom w:val="single" w:sz="4" w:space="4" w:color="CBD5E1"/>
      </w:pBdr>
    </w:pPr>
    <w:r>
      <w:rPr>
        <w:rFonts w:ascii="Calibri" w:hAnsi="Calibri"/>
        <w:b/>
        <w:sz w:val="24"/>
        <w:szCs w:val="24"/>
        <w:color w:val="1E3A8A"/>
      </w:rPr>
      <w:t xml:space="preserve">${esc(title)}</w:t>
    </w:r>
  </w:p>`;
}

function makeTable(headers, rows, colWidths) {
  const totalWidth = colWidths.reduce((a, b) => a + b, 0);
  const gridCols = colWidths.map(w => `<w:gridCol w:w="${w}"/>`).join('');

  let rowsXml = '';
  if (headers && headers.length > 0) {
    const headerCells = headers.map((h, i) => `
      <w:tc>
        <w:tcPr>
          <w:tcW w:w="${colWidths[i]}" w:type="dxa"/>
          <w:shd w:val="clear" w:color="auto" w:fill="E2E8F0"/>
          <w:tcMar>
            <w:top w:w="90" w:type="dxa"/><w:bottom w:w="90" w:type="dxa"/>
            <w:left w:w="120" w:type="dxa"/><w:right w:w="120" w:type="dxa"/>
          </w:tcMar>
        </w:tcPr>
        ${p(h, { bold: true, size: 19, color: '0F172A', spaceAfter: 0 })}
      </w:tc>`).join('');
    rowsXml += `<w:tr>${headerCells}</w:tr>`;
  }

  rows.forEach((row, rIdx) => {
    const bg = rIdx % 2 === 1 ? 'F8FAFC' : 'FFFFFF';
    const cells = row.map((cellText, cIdx) => {
      const isBold = typeof cellText === 'object' ? cellText.bold : false;
      const text = typeof cellText === 'object' ? cellText.text : cellText;
      return `
      <w:tc>
        <w:tcPr>
          <w:tcW w:w="${colWidths[cIdx]}" w:type="dxa"/>
          <w:shd w:val="clear" w:color="auto" w:fill="${bg}"/>
          <w:tcMar>
            <w:top w:w="80" w:type="dxa"/><w:bottom w:w="80" w:type="dxa"/>
            <w:left w:w="120" w:type="dxa"/><w:right w:w="120" w:type="dxa"/>
          </w:tcMar>
        </w:tcPr>
        ${p(text, { bold: isBold, size: 19, color: '334155', spaceAfter: 0 })}
      </w:tc>`;
    }).join('');
    rowsXml += `<w:tr>${cells}</w:tr>`;
  });

  return `<w:tbl>
    <w:tblPr>
      <w:tblW w:w="${totalWidth}" w:type="dxa"/>
      <w:tblBorders>
        <w:top w:val="single" w:sz="4" w:space="0" w:color="CBD5E1"/>
        <w:left w:val="single" w:sz="4" w:space="0" w:color="CBD5E1"/>
        <w:bottom w:val="single" w:sz="4" w:space="0" w:color="CBD5E1"/>
        <w:right w:val="single" w:sz="4" w:space="0" w:color="CBD5E1"/>
        <w:insideH w:val="single" w:sz="4" w:space="0" w:color="E2E8F0"/>
        <w:insideV w:val="single" w:sz="4" w:space="0" w:color="E2E8F0"/>
      </w:tblBorders>
    </w:tblPr>
    <w:tblGrid>${gridCols}</w:tblGrid>
    ${rowsXml}
  </w:tbl>${p('', { spaceAfter: 80 })}`;
}

function banner() {
  return `<w:tbl>
    <w:tblPr>
      <w:tblW w:w="9638" w:type="dxa"/>
      <w:tblBorders>
        <w:top w:val="none"/><w:left w:val="none"/><w:bottom w:val="none"/><w:right w:val="none"/>
      </w:tblBorders>
    </w:tblPr>
    <w:tblGrid><w:gridCol w:w="9638"/></w:tblGrid>
    <w:tr>
      <w:tc>
        <w:tcPr>
          <w:tcW w:w="9638" w:type="dxa"/>
          <w:shd w:val="clear" w:color="auto" w:fill="1E3A8A"/>
          <w:tcMar>
            <w:top w:w="200" w:type="dxa"/><w:bottom w:w="200" w:type="dxa"/>
            <w:left w:w="240" w:type="dxa"/><w:right w:w="240" w:type="dxa"/>
          </w:tcMar>
        </w:tcPr>
        ${p('TUGAS PROJECT: PENGEMBANGAN & PENGAMANAN APLIKASI WEB', { bold: true, size: 28, color: 'FFFFFF', align: 'center', spaceAfter: 60 })}
        ${p('LAPORAN DEPLOYMENT, FITUR, DAN SELF-TESTING KEAMANAN', { bold: true, size: 21, color: 'FDBA74', align: 'center', spaceAfter: 60 })}
        ${p('Mata Kuliah: Cyber Security  |  Jenis Tugas: Individu (Pra UTS)  |  Semester Genap 2026/2027', { size: 18, color: 'E2E8F0', align: 'center', spaceAfter: 0 })}
      </w:tc>
    </w:tr>
  </w:tbl>${p('', { spaceAfter: 120 })}`;
}

const bodyContent = [
  banner(),

  heading('1. IDENTITAS MAHASISWA'),
  makeTable(null, [
    [{ text: 'Nama Lengkap', bold: true }, 'Rafly Irhandy'],
    [{ text: 'NIM', bold: true }, '[Masukkan NIM Anda]'],
    [{ text: 'Kelas / Program Studi', bold: true }, 'Cyber Security / Teknik Informatika'],
    [{ text: 'Judul Aplikasi', bold: true }, 'MachoGym — Web Management & Fitness Membership System']
  ], [2800, 6838]),

  heading('2. URL APLIKASI AKTIF (LIVE HOSTING)'),
  p('Aplikasi telah di-deploy secara online dan dapat diakses publik melalui Fully Qualified Domain Name (FQDN) dengan sertifikat SSL/TLS valid (HTTPS), tanpa menggunakan localhost atau IP address mentah:'),
  makeTable(null, [
    [{ text: 'URL Aplikasi Aktif', bold: true }, { text: 'https://machogym.my.id/', bold: true }],
    [{ text: 'Status HTTPS & SSL/TLS', bold: true }, 'Aktif & Valid (Port 443, Redirect otomatis dari HTTP)'],
    [{ text: 'Status Akses Publik', bold: true }, 'Stabil dan dapat diuji langsung oleh dosen penguji']
  ], [2800, 6838]),

  heading('3. URL REPOSITORY GIT'),
  p('Source code aplikasi diunggah secara terstruktur ke repository GitHub dengan riwayat commit bertahap dan dilengkapi README.md tanpa kredensial/secret yang bocor:'),
  makeTable(null, [
    [{ text: 'URL Repository GitHub', bold: true }, 'https://github.com/raflyirhandy/Gym-Apps.git'],
    [{ text: 'Arsitektur Aplikasi', bold: true }, 'Native PHP 8.x (MVC Sederhana) + MySQL PDO'],
    [{ text: 'Keamanan Repository', bold: true }, 'Konfigurasi kredensial dipisahkan dari repository publik']
  ], [2800, 6838]),

  heading('4. AKUN UJI COBA (TESTING CREDENTIALS)'),
  p('Akun berikut disediakan agar dosen dapat menguji fitur autentikasi, manajemen data, dan kontrol akses berbasis peran (RBAC):'),
  makeTable(
    ['Peran (Role)', 'Email / Username', 'Password', 'Cakupan Hak Akses'],
    [
      [{ text: 'Admin', bold: true }, 'admin@gym.com', 'password123', 'Akses penuh: Manajemen Member, Trainer, Jadwal Kelas, Verifikasi Pembayaran, Laporan'],
      [{ text: 'Trainer', bold: true }, 'trainer1@gym.com', 'password123', 'Akses terbatas: Daftar member bimbingan, program workout, chat member'],
      [{ text: 'Member', bold: true }, 'member1@gym.com', 'password123', 'Akses anggota: Booking kelas, tracking progres latihan, upload bukti bayar']
    ],
    [1300, 2100, 1400, 4838]
  ),

  heading('5. PENJELASAN APLIKASI'),
  p('A. Latar Belakang & Tujuan', { bold: true, size: 21, color: '0F172A', spaceAfter: 60 }),
  p('Pengelolaan pusat kebugaran secara manual rentan terhadap kesalahan pencatatan keanggotaan, bentrok jadwal kelas, dan lemahnya perlindungan data pribadi member. MachoGym hadir sebagai sistem informasi manajemen gym berbasis web yang memisahkan hak akses Admin, Trainer, dan Member secara tegas serta mengutamakan prinsip secure coding.'),
  p('B. Fitur Utama Aplikasi', { bold: true, size: 21, color: '0F172A', spaceAfter: 60 }),
  p('1. Registrasi & Login Multi-Role: Pendaftaran mandiri member, validasi input, dan proteksi rute per peran.'),
  p('2. Pengelolaan Data (CRUD Database): Manajemen Member, Trainer, Jadwal Kelas, Program Latihan, dan Pembayaran.'),
  p('3. Laporan & Monitoring: Dashboard statistik, tabel rekapitulasi pembayaran membership, serta grafik progres latihan member.'),
  p('4. Konsultasi Real-Time: Fitur chat dua arah antara Member dan Trainer.'),

  p('C. Arsitektur & Tech Stack', { bold: true, size: 21, color: '0F172A', spaceAfter: 60, spaceBefore: 100 }),
  makeTable(
    ['Layer / Komponen', 'Teknologi yang Digunakan', 'Peran dalam Sistem'],
    [
      ['Frontend / View', 'HTML5, CSS3 (Glassmorphism), Vanilla JavaScript, Font Awesome 6.x', 'Antarmuka web responsif, validasi form, dan AJAX polling untuk chat'],
      ['Backend / Logic', 'Native PHP 8.x (Arsitektur MVC Sederhana), PHPMailer 7.x', 'Router (index.php), controller logika bisnis, dan manajemen sesi PHP'],
      ['Database Layer', 'MySQL 8.0 + PHP PDO (Prepared Statements)', 'Penyimpanan data relasional (gym_db) dengan query terparameterisasi'],
      ['Hosting & Domain', 'machogym.my.id (Apache/Web Server + SSL/TLS)', 'Publikasi online ber-HTTPS aktif dan valid']
    ],
    [2000, 3400, 4238]
  ),

  p('D. Penerapan Keamanan (Secure Coding)', { bold: true, size: 21, color: '0F172A', spaceAfter: 60 }),
  makeTable(
    ['Aspek Keamanan', 'Implementasi pada MachoGym', 'Tujuan & Alasan'],
    [
      ['Password Hashing', 'password_hash() & password_verify() (Bcrypt PHP)', 'Menyimpan hash password secara aman, bukan plaintext'],
      ['Cegah SQL Injection', 'PHP PDO Prepared Statements (:param & bindParam)', 'Memisahkan instruksi SQL dari input pengguna'],
      ['Cegah XSS', 'htmlspecialchars() & strip_tags() pada input/output', 'Mencegah eksekusi skrip berbahaya di browser'],
      ['Manajemen Sesi', 'PHP Native Session ($_SESSION) dengan cookie HttpOnly & Secure', 'Melindungi ID sesi dari pencurian via script DOM/XSS'],
      ['Kontrol Akses (RBAC)', 'Pengecekan $_SESSION[\'role\'] di setiap controller & view', 'Mencegah IDOR dan akses menu admin oleh member/trainer'],
      ['Validasi Upload File', 'Pemeriksaan ekstensi & ukuran file bukti pembayaran', 'Mencegah upload file berbahaya (web shell / PHP backdoor)'],
      ['HTTPS & SSL/TLS', 'Sertifikat SSL/TLS aktif pada domain machogym.my.id', 'Menjaga kerahasiaan data saat transmisi di jaringan publik'],
      ['Secret Management', 'Pemisahan kredensial database & SMTP di konfigurasi server', 'Menjaga kredensial tidak terekspos di repository publik']
    ],
    [2100, 3400, 4138]
  ),

  heading('6. POTENSI PENGEMBANGAN KE SKRIPSI'),
  p('Topik Rencana: "Penerapan Model Zero-Trust Security dan Otomasi Deteksi Anomali Akses pada Sistem Manajemen Pusat Kebugaran Berbasis Web".', { bold: true }),
  p('Rumusan Masalah: (1) Bagaimana merancang kontrol akses adaptif berbasis Multi-Factor Authentication (MFA) untuk mencegah pembajakan sesi? (2) Bagaimana menerapkan deteksi anomali login mencurigakan pada sistem keanggotaan gym?'),
  p('Fitur Lanjutan: Integrasi Payment Gateway (Midtrans/Xendit) dengan verifikasi signature HMAC-SHA512, autentikasi 2FA (TOTP), dan audit trail log terenkripsi.'),

  heading('7. PENGGUNAAN AI AGENT (POIN 3E)'),
  makeTable(null, [
    [{ text: 'Alat AI yang Dipakai', bold: true }, 'Google DeepMind Antigravity, Gemini, dan Cursor Agent'],
    [{ text: 'Bagian yang Dibantu', bold: true }, 'Perancangan skema database MySQL (gym_db.sql), penyusunan query PHP PDO, desain antarmuka CSS Glassmorphism, serta penyusunan skenario uji keamanan mandiri.'],
    [{ text: 'Contoh Prompt Utama', bold: true }, '"Buatkan fungsi login dan proteksi sesi di Native PHP menggunakan PDO prepared statement dan pengecekan role session agar member tidak dapat mengakses halaman admin."'],
    [{ text: 'Cara Review & Verifikasi', bold: true }, 'Kode PHP ditinjau baris demi baris secara manual, diuji di XAMPP lokal dan server hosting, serta dipastikan tidak ada kredensial nyata yang dimasukkan ke dalam prompt.']
  ], [2600, 7038]),

  heading('8. HASIL SELF-TESTING KEAMANAN (MINIMAL 5 SKENARIO)'),
  makeTable(
    ['No', 'Skenario Uji', 'Cara Pengujian', 'Hasil', 'Perbaikan & Status Akhir'],
    [
      ['1', 'SQL Injection pada Login', 'Input payload "\' OR \'1\'=\'1\' --" di form login', { text: 'LOLOS', bold: true }, 'Diproses PDO prepared statement (:email); login ditolak aman.'],
      ['2', 'XSS pada Input Form', 'Input "<script>alert(1)</script>" di form registrasi/chat', { text: 'LOLOS', bold: true }, 'Input disanitasi via htmlspecialchars() & strip_tags().'],
      ['3', 'IDOR / Bypass Role Admin', 'Login Member lalu akses ?page=admin_members', { text: 'LOLOS', bold: true }, 'Validasi $_SESSION[\'role\'] menolak akses dan me-redirect.'],
      ['4', 'Session & Logout Test', 'Akses halaman dashboard setelah klik Logout', { text: 'LOLOS', bold: true }, 'session_destroy() menghapus sesi; akses ditolak ke login.'],
      ['5', 'HTTPS & Kebocoran Secret', 'Cek SSL machogym.my.id & audit riwayat commit Git', { text: 'LOLOS', bold: true }, 'Sertifikat SSL/TLS valid; tidak ada kredensial produksi bocor.']
    ],
    [500, 1900, 2500, 900, 3838]
  ),

  heading('9. TANGKAPAN LAYAR (BUKTI IMPLEMENTASI & HOSTING)'),
  p('Silakan tempel (paste) tangkapan layar asli dari browser Anda pada kolom kanan tabel di bawah ini sebelum dikumpulkan ke EdLink:', { italic: true, color: '64748B' }),
  makeTable(
    ['Jenis Bukti Tangkapan Layar', 'Area Tempel Screenshot / Keterangan'],
    [
      [{ text: '1. Halaman Registrasi / Login', bold: true }, '[ Tempel Screenshot Halaman Login https://machogym.my.id/ di sini ]'],
      [{ text: '2. Halaman Pengelolaan Data (CRUD)', bold: true }, '[ Tempel Screenshot Halaman Manajemen Member / Kelas di sini ]'],
      [{ text: '3. Halaman Laporan', bold: true }, '[ Tempel Screenshot Halaman Laporan Pembayaran / Grafik Progres di sini ]'],
      [{ text: '4. Bukti HTTPS / Domain Aktif', bold: true }, '[ Tempel Screenshot Ikon Gembok & Detail Sertifikat SSL machogym.my.id di sini ]'],
      [{ text: '5. Bukti Konfigurasi Hosting / DNS', bold: true }, '[ Tempel Screenshot Panel Hosting / DNS Record machogym.my.id di sini ]'],
      [{ text: '6. Bukti Self-Testing Keamanan', bold: true }, '[ Tempel Screenshot Hasil Uji Input / Audit Header / Scan SSL di sini ]']
    ],
    [3200, 6438]
  ),

  heading('10. KENDALA DAN RENCANA PERBAIKAN'),
  makeTable(
    ['Kendala yang Dihadapi', 'Solusi & Rencana Perbaikan'],
    [
      ['Penyesuaian konfigurasi database dan base URL saat migrasi dari XAMPP localhost ke server hosting machogym.my.id.', 'Menyesuaikan parameter koneksi PDO pada config/database.php sesuai kredensial database server produksi dan mengaktifkan redirect HTTPS.'],
      ['Keamanan folder upload bukti pembayaran (assets/uploads/) agar tidak dapat mengeksekusi script PHP berbahaya.', 'Menambahkan validasi ekstensi gambar di PaymentController serta pembatasan eksekusi PHP di direktori upload melalui .htaccess.']
    ],
    [4500, 5138]
  ),

  p('', { spaceAfter: 160 }),
  p('Cirebon, 2026', { align: 'right', spaceAfter: 40 }),
  p('Mahasiswa Penyusun,', { align: 'right', spaceAfter: 480 }),
  p('Rafly Irhandy', { bold: true, align: 'right', spaceAfter: 20 }),
  p('NIM: [Masukkan NIM Anda]', { align: 'right', color: '64748B', spaceAfter: 0 })
];

const documentXml = `<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">
  <w:body>
    ${bodyContent.join('\n    ')}
    <w:sectPr>
      <w:pgSz w:w="11906" w:h="16838"/>
      <w:pgMar w:top="1134" w:right="1134" w:bottom="1134" w:left="1134" w:header="708" w:footer="708" w:gutter="0"/>
    </w:sectPr>
  </w:body>
</w:document>`;

const contentTypesXml = `<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml" ContentType="application/xml"/>
  <Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>
</Types>`;

const relsXml = `<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>
</Relationships>`;

const tmpDir = 'C:/xampp/htdocs/gym_management/.docx_tmp';
const outDocx = 'C:/xampp/htdocs/gym_management/TugasProject_CyberSec_RaflyIrhandy_PHP.docx';

if (fs.existsSync(tmpDir)) fs.rmSync(tmpDir, { recursive: true, force: true });
if (fs.existsSync(outDocx)) fs.unlinkSync(outDocx);

fs.mkdirSync(path.join(tmpDir, '_rels'), { recursive: true });
fs.mkdirSync(path.join(tmpDir, 'word'), { recursive: true });

fs.writeFileSync(path.join(tmpDir, '[Content_Types].xml'), contentTypesXml, 'utf8');
fs.writeFileSync(path.join(tmpDir, '_rels', '.rels'), relsXml, 'utf8');
fs.writeFileSync(path.join(tmpDir, 'word', 'document.xml'), documentXml, 'utf8');

execSync(`powershell -Command "Add-Type -AssemblyName System.IO.Compression.FileSystem; [System.IO.Compression.ZipFile]::CreateFromDirectory('${tmpDir}', '${outDocx}')"`);

fs.rmSync(tmpDir, { recursive: true, force: true });
console.log('DOCX generated successfully:', outDocx, 'Size:', fs.statSync(outDocx).size);
