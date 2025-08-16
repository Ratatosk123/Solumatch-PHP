// JavaScript/meus_chats.js

document.addEventListener('DOMContentLoaded', () => {
    // Elementos da Interface
    const inboxContainer = document.querySelector('.inbox-container');
    const conversationList = document.querySelector('.list-content');
    const chatHeader = document.getElementById('chatHeader');
    const chatHeaderPic = document.getElementById('chatHeaderPic');
    const chatProfileLink = document.getElementById('chatProfileLink');
    const chatWithUserName = document.getElementById('chatWithUserName');
    const chatAboutVaga = document.getElementById('chatAboutVaga');
    const chatMessages = document.getElementById('chatMessages');
    const chatInputArea = document.getElementById('chatInputArea');
    const messageInput = document.getElementById('messageInput');
    const sendMessageBtn = document.getElementById('sendMessageBtn');
    const backToListBtn = document.getElementById('backToListBtn');
    const attachmentBtn = document.getElementById('attachmentBtn');
    const attachmentInput = document.getElementById('attachmentInput');

    let messagePollingInterval = null;
    let activeChat = { vagaId: null, otherUserId: null };
    // Variável para contar as mensagens e detectar novas
    let currentMessageCount = 0;

    async function fetchAndRenderMessages() {
        if (!activeChat.vagaId || !activeChat.otherUserId) return;

        try {
            const response = await fetch(`../PHP/get_direct_messages.php?vaga_id=${activeChat.vagaId}&other_user_id=${activeChat.otherUserId}`);
            if (!response.ok) throw new Error('Falha ao buscar mensagens.');
            
            const messages = await response.json();

            // Lógica para anunciar novas mensagens recebidas
            if (messages.length > currentMessageCount && currentMessageCount > 0) {
                const lastMessage = messages[messages.length - 1];
                
                // Anuncia apenas se a última mensagem for RECEBIDA
                if (parseInt(lastMessage.remetente_id) === activeChat.otherUserId) {
                    const senderName = document.getElementById('chatWithUserName').textContent;
                    
                    // Dispara um evento para que o script de acessibilidade o capture e fale.
                    document.dispatchEvent(new CustomEvent('request-speech', {
                        detail: { text: `${senderName} enviou uma nova mensagem.` }
                    }));
                }
            }
            // Atualiza a contagem de mensagens para a próxima verificação
            currentMessageCount = messages.length;

            renderMessages(messages);
        } catch (error) {
            console.error("Erro em fetchAndRenderMessages:", error);
        }
    }

    function renderMessages(messages) {
        chatMessages.innerHTML = '';
        if (messages.length === 0) {
             chatMessages.innerHTML = '<p class="no-chat-selected" role="alert">Ainda não há mensagens nesta conversa. Envie a primeira!</p>';
             return;
        }
        messages.forEach(msg => {
            const messageDiv = document.createElement('div');
            messageDiv.classList.add('message-bubble');
            messageDiv.classList.add(parseInt(msg.remetente_id) === LOGGED_IN_USER_ID ? 'enviada' : 'recebida');

            const fileRegex = /\[arquivo:(.+)\]/;
            const fileMatch = msg.mensagem.match(fileRegex);

            if (fileMatch) {
                const parts = fileMatch[1].split('|');
                const uniqueName = parts[0];
                const originalName = parts[1] || 'Ficheiro Anexado';
                messageDiv.innerHTML = `<a href="../uploads/chat_attachments/${uniqueName}" target="_blank" download="${originalName}">📎 ${originalName}</a>`;
            } else {
                messageDiv.textContent = msg.mensagem;
            }
            chatMessages.appendChild(messageDiv);
        });
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    function openChat(conversationData) {
        if (messagePollingInterval) clearInterval(messagePollingInterval);

        // Reseta a contagem de mensagens ao abrir um novo chat
        currentMessageCount = 0;

        activeChat.vagaId = conversationData.vaga_id;
        activeChat.otherUserId = conversationData.other_user_id;

        chatHeaderPic.src = `../PHP/get_profile_picture.php?id=${conversationData.other_user_id}`;
        
        const profilePage = conversationData.other_user_is_company ? 'perfil_empresa.php' : 'meu_perfil.php';
        chatProfileLink.href = `${profilePage}?id=${conversationData.other_user_id}`;
        chatProfileLink.setAttribute('aria-label', `Ver perfil de ${conversationData.other_user_name}`);
        
        chatHeader.style.display = 'flex';
        chatInputArea.style.display = 'flex';
        chatWithUserName.textContent = conversationData.other_user_name;
        chatAboutVaga.textContent = `Conversa sobre a vaga: "${conversationData.vaga_titulo}"`;
        
        document.querySelectorAll('.conversation-item').forEach(el => el.classList.remove('active'));
        const activeItem = conversationList.querySelector(`[data-vaga-id='${activeChat.vagaId}'][data-other-user-id='${activeChat.otherUserId}']`);
        if (activeItem) activeItem.classList.add('active');
        
        inboxContainer.classList.add('chat-active');
        
        fetchAndRenderMessages();
        messagePollingInterval = setInterval(fetchAndRenderMessages, 5000); // Polling a cada 5 segundos
    }
    
    function renderConversationList(conversations) {
        const currentActiveKey = `${activeChat.vagaId}-${activeChat.otherUserId}`;
        conversationList.innerHTML = '';
        if (conversations.length === 0) {
            conversationList.innerHTML = '<p style="text-align: center; padding: 20px; color: #888;" role="status">Nenhuma conversa iniciada.</p>';
            return;
        }

        conversations.forEach(convo => {
            const item = document.createElement('div');
            item.className = 'conversation-item';
            if (`${convo.vaga_id}-${convo.other_user_id}` === currentActiveKey) {
                item.classList.add('active');
            }
            item.dataset.vagaId = convo.vaga_id;
            item.dataset.otherUserId = convo.other_user_id;
            
            item.setAttribute('role', 'button');
            item.setAttribute('tabindex', '0');

            const fileRegex = /\[arquivo:(.+)\]/;
            const fileMatch = convo.last_message.match(fileRegex);
            const lastMessageText = fileMatch ? 'Arquivo anexado' : convo.last_message;

            const unreadText = convo.unread_count > 0 ? `${convo.unread_count} mensagens não lidas.` : '';
            item.setAttribute('aria-label', `Conversa com ${convo.other_user_name} sobre a vaga ${convo.vaga_titulo}. ${unreadText} Última mensagem: ${lastMessageText}. Clique para abrir o chat.`);
            
            item.innerHTML = `
                <img src="../PHP/get_profile_picture.php?id=${convo.other_user_id}" alt="Perfil" class="conversation-item-pic">
                <div class="conversation-item-details">
                    ${convo.unread_count > 0 ? `<span class="unread-count">${convo.unread_count}</span>` : ''}
                    <span class="item-user-name">${convo.other_user_name}</span>
                    <p class="item-vaga-title">Vaga: ${convo.vaga_titulo}</p>
                    <p class="item-last-message">${lastMessageText}</p>
                </div>
            `;
            
            item.addEventListener('click', () => openChat(convo));
            item.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    openChat(convo);
                }
            });

            conversationList.appendChild(item);
        });
    }

    async function fetchConversations() {
        try {
            const response = await fetch('../PHP/get_conversations.php');
            if (!response.ok) throw new Error('Falha ao buscar a lista de conversas.');
            const conversations = await response.json();
            renderConversationList(conversations);
        } catch (error) {
            console.error("Erro em fetchConversations:", error);
            conversationList.innerHTML = '<p class="error-message">Não foi possível carregar suas conversas.</p>';
        }
    }

    async function sendMessage() {
        const messageText = messageInput.value.trim();
        if (messageText === '' || !activeChat.vagaId) return;

        try {
            const response = await fetch('../PHP/send_direct_message.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    vaga_id: activeChat.vagaId,
                    destinatario_id: activeChat.otherUserId,
                    mensagem: messageText
                })
            });
            const result = await response.json();
            
            if (result.success) {
                messageInput.value = '';
                await fetchAndRenderMessages();
                await fetchConversations();
            } else {
                throw new Error(result.message);
            }
        } catch (error) {
            alert(error.message);
        }
    }
    
    async function sendAttachment(file) {
        if (!file || !activeChat.vagaId) {
            alert("Selecione um ficheiro e uma conversa para enviar.");
            return;
        }

        const formData = new FormData();
        formData.append('attachment', file);
        formData.append('vaga_id', activeChat.vagaId);
        formData.append('destinatario_id', activeChat.otherUserId);

        try {
            const response = await fetch('../PHP/upload_chat_attachment.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();
            if (result.success) {
                await fetchAndRenderMessages();
                await fetchConversations(); 
            } else {
                throw new Error(result.message || 'Erro desconhecido ao enviar o ficheiro.');
            }
        } catch (error) {
            alert(`Erro ao enviar anexo: ${error.message}`);
        }
    }

    sendMessageBtn.addEventListener('click', sendMessage);
    messageInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });

    backToListBtn.addEventListener('click', () => {
        inboxContainer.classList.remove('chat-active');
    });
    
    attachmentBtn.addEventListener('click', () => {
        attachmentInput.click();
    });

    attachmentInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            sendAttachment(file);
        }
        this.value = ''; 
    });
    
    fetchConversations();
    
    if (ACTIVE_VAGA_ID && ACTIVE_OTHER_USER_ID && ACTIVE_OTHER_USER_NAME && ACTIVE_VAGA_TITLE) {
        const conversationData = {
            vaga_id: ACTIVE_VAGA_ID,
            other_user_id: ACTIVE_OTHER_USER_ID,
            other_user_name: ACTIVE_OTHER_USER_NAME,
            vaga_titulo: ACTIVE_VAGA_TITLE,
            other_user_is_company: ACTIVE_OTHER_USER_IS_COMPANY
        };
        openChat(conversationData);
    }
    
    setInterval(fetchConversations, 10000);
});