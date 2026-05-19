<?php
require_once 'views/layouts/header.php';
?>

<div style="display: flex; height: 75vh; border: 1px solid var(--border-color); border-radius: 12px; overflow: hidden;" class="glass">
    
    <!-- Contacts List -->
    <div style="width: 300px; border-right: 1px solid var(--border-color); display: flex; flex-direction: column; background: rgba(15, 23, 42, 0.5);">
        <div style="padding: 20px; border-bottom: 1px solid var(--border-color);">
            <h3>Kontak</h3>
        </div>
        <div id="contacts-list" style="flex-grow: 1; overflow-y: auto;">
            <!-- Contacts loaded via AJAX -->
            <div style="padding: 20px; text-align: center; color: var(--text-muted);">Memuat kontak...</div>
        </div>
    </div>

    <!-- Chat Area -->
    <div style="flex-grow: 1; display: flex; flex-direction: column; background: rgba(30, 41, 59, 0.3);">
        <div id="chat-header" style="padding: 20px; border-bottom: 1px solid var(--border-color); display: none;">
            <h3 id="chat-user-name">Pilih kontak</h3>
        </div>
        
        <div id="chat-messages" style="flex-grow: 1; padding: 20px; overflow-y: auto; display: flex; flex-direction: column; gap: 15px;">
            <!-- Messages loaded via AJAX -->
            <div style="margin: auto; color: var(--text-muted);">Pilih kontak untuk mulai mengobrol</div>
        </div>
        
        <div id="chat-input-area" style="padding: 20px; border-top: 1px solid var(--border-color); display: none;">
            <form id="chat-form" style="display: flex; gap: 10px;">
                <input type="text" id="message-input" class="form-control" placeholder="Ketik pesan..." required autocomplete="off" style="flex-grow: 1;">
                <button type="submit" class="btn btn-primary"><i class="fa fa-paper-plane"></i> Kirim</button>
            </form>
        </div>
    </div>
</div>

<style>
.contact-item {
    padding: 15px 20px;
    border-bottom: 1px solid rgba(255,255,255,0.05);
    cursor: pointer;
    transition: background 0.3s;
}
.contact-item:hover, .contact-item.active {
    background: rgba(16, 185, 129, 0.1);
    border-left: 3px solid var(--primary);
}
.msg-bubble {
    max-width: 70%;
    padding: 10px 15px;
    border-radius: 15px;
    font-size: 15px;
}
.msg-sent {
    background: var(--primary);
    color: white;
    align-self: flex-end;
    border-bottom-right-radius: 2px;
}
.msg-received {
    background: rgba(255, 255, 255, 0.1);
    color: var(--text-main);
    align-self: flex-start;
    border-bottom-left-radius: 2px;
}
.msg-time {
    font-size: 11px;
    opacity: 0.7;
    margin-top: 5px;
    text-align: right;
}
</style>

<script>
let currentContactId = null;
const userId = <?php echo $_SESSION['user_id']; ?>;

document.addEventListener('DOMContentLoaded', () => {
    loadContacts();

    // Setup chat form
    document.getElementById('chat-form').addEventListener('submit', (e) => {
        e.preventDefault();
        sendMessage();
    });

    // Poll for new messages every 3 seconds
    setInterval(() => {
        if (currentContactId) {
            loadMessages(currentContactId, false);
        }
    }, 3000);
});

function loadContacts() {
    fetch('index.php?page=api_chat&action=get_users')
        .then(res => res.json())
        .then(data => {
            const list = document.getElementById('contacts-list');
            list.innerHTML = '';
            
            if (data.length === 0) {
                list.innerHTML = '<div style="padding: 20px; text-align: center; color: var(--text-muted);">Tidak ada kontak tersedia.</div>';
                return;
            }

            data.forEach(user => {
                const div = document.createElement('div');
                div.className = 'contact-item';
                div.innerHTML = `<strong>${user.name}</strong>`;
                div.onclick = () => selectContact(user.id, user.name, div);
                list.appendChild(div);
            });
        });
}

function selectContact(id, name, element) {
    currentContactId = id;
    
    // UI Updates
    document.querySelectorAll('.contact-item').forEach(el => el.classList.remove('active'));
    if(element) element.classList.add('active');
    
    document.getElementById('chat-header').style.display = 'block';
    document.getElementById('chat-user-name').innerText = name;
    document.getElementById('chat-input-area').style.display = 'block';
    
    loadMessages(id, true);
}

function loadMessages(contactId, scroll = true) {
    fetch(`index.php?page=api_chat&action=get_messages&contact_id=${contactId}`)
        .then(res => res.json())
        .then(data => {
            const chatMessages = document.getElementById('chat-messages');
            chatMessages.innerHTML = '';
            
            if (data.length === 0) {
                chatMessages.innerHTML = '<div style="margin: auto; color: var(--text-muted);">Belum ada pesan. Sapa mereka!</div>';
                return;
            }

            data.forEach(msg => {
                const isSent = (msg.sender_id == userId);
                const bubble = document.createElement('div');
                bubble.className = `msg-bubble ${isSent ? 'msg-sent' : 'msg-received'}`;
                
                const date = new Date(msg.created_at);
                const time = `${date.getHours().toString().padStart(2,'0')}:${date.getMinutes().toString().padStart(2,'0')}`;
                
                bubble.innerHTML = `
                    <div>${msg.message}</div>
                    <div class="msg-time">${time}</div>
                `;
                chatMessages.appendChild(bubble);
            });

            if (scroll) {
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }
        });
}

function sendMessage() {
    const input = document.getElementById('message-input');
    const message = input.value.trim();
    
    if (!message || !currentContactId) return;

    fetch('index.php?page=api_chat&action=send_message', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            contact_id: currentContactId,
            message: message
        })
    })
    .then(res => res.json())
    .then(data => {
        if(data.status === 'success') {
            input.value = '';
            loadMessages(currentContactId, true);
        } else {
            alert('Gagal mengirim pesan');
        }
    });
}
</script>

<?php require_once 'views/layouts/footer.php'; ?>
