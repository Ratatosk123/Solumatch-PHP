// JavaScript/dropdown_perfil.js

document.addEventListener("DOMContentLoaded", () => {
    // --- LÓGICA PARA DROPDOWN DO PERFIL ---
    const profileIcon = document.getElementById("profileIcon");
    const dropdownMenu = document.getElementById("dropdownMenu");

    if (profileIcon) {
        profileIcon.addEventListener("click", (e) => {
            e.stopPropagation();
            if (dropdownMenu) dropdownMenu.classList.toggle("show");
        });
    }

    // --- LÓGICA PARA DROPDOWN DE NOTIFICAÇÕES ---
    const notificationIcon = document.getElementById("notificationIcon");
    const notificationDropdown = document.getElementById("notificationDropdown");
    const notificationList = document.getElementById("notificationList");

    if (notificationIcon) {
        notificationIcon.addEventListener('click', async (e) => {
            e.stopPropagation(); 
            if (!notificationDropdown || !notificationList) return;

            const isVisible = notificationDropdown.style.display === 'block';
            notificationDropdown.style.display = isVisible ? 'none' : 'block';

            if (!isVisible) {
                notificationList.innerHTML = '<p class="no-notifications">Carregando...</p>';
                try {
                    const response = await fetch('/solumatch_atualizado/PHP/get_notifications.php');
                    if (!response.ok) throw new Error('Falha ao buscar notificações.');
                    
                    const notifications = await response.json();
                    
                    notificationList.innerHTML = ''; 
                    
                    if (notifications && notifications.length > 0) {
                        notifications.forEach(notif => {
                            const item = document.createElement('a');
                            item.className = 'notification-item';
                            item.href = `meus_chats.php?vaga_id=${notif.vaga_id}&conversa_com_id=${notif.remetente_id}`;
                            
                            item.innerHTML = `
                                <div>
                                    <span class="sender-name">${notif.remetente_nome}</span>
                                    enviou uma mensagem:
                                    <div class="message-snippet">"${notif.mensagem.substring(0, 40)}..."</div>
                                </div>
                            `;
                            notificationList.appendChild(item);
                        });
                        
                        const notificationDot = document.getElementById('notificationDot');
                        if(notificationDot) notificationDot.style.display = 'none';
                        // Zera a contagem na sessão para evitar recarregamentos futuros
                        sessionStorage.setItem('lastNotificationCount', '0');

                    } else {
                        notificationList.innerHTML = '<p class="no-notifications">Nenhuma nova notificação.</p>';
                    }
                } catch (error) {
                    notificationList.innerHTML = '<p class="no-notifications" style="color: red;">Erro ao carregar.</p>';
                    console.error("Erro ao buscar notificações:", error);
                }
            }
        });
    }

    // Fecha os dropdowns se o usuário clicar fora deles
    document.addEventListener("click", (e) => {
        if (dropdownMenu && profileIcon && !profileIcon.contains(e.target) && !dropdownMenu.contains(e.target)) {
            dropdownMenu.classList.remove("show");
        }
        if (notificationDropdown && notificationIcon && !notificationIcon.contains(e.target) && !notificationDropdown.contains(e.target)) {
            notificationDropdown.style.display = 'none';
        }
    });

    // --- LÓGICA DE VERIFICAÇÃO E ATUALIZAÇÃO AUTOMÁTICA ---
    const checkNotifications = async () => {
        const notificationDot = document.getElementById('notificationDot');
        if (!notificationDot) return;

        try {
            const response = await fetch('/solumatch_atualizado/PHP/get_notification_count.php');
            if (!response.ok) throw new Error('Falha ao contar notificações.');

            const data = await response.json();
            const newCount = data.unread_count;

            // Pega a contagem anterior do sessionStorage
            let lastCount = parseInt(sessionStorage.getItem('lastNotificationCount') || '0', 10);

            // ATUALIZAÇÃO: Se a nova contagem for maior que a anterior...
            if (newCount > lastCount) {
                console.log('Nova notificação detectada!');
                // ...cria e dispara o evento personalizado.
                const event = new CustomEvent('novaNotificacao');
                document.dispatchEvent(event);
            }
            
            // Atualiza a contagem no sessionStorage para a próxima verificação
            sessionStorage.setItem('lastNotificationCount', newCount);

            // Mostra ou esconde o ponto vermelho
            notificationDot.style.display = newCount > 0 ? 'block' : 'none';
            
        } catch (error) {
            console.error("Erro ao verificar notificações:", error);
        }
    }

    // Verifica as notificações assim que a página carrega
    checkNotifications();
    // Continua verificando a cada 15 segundos
    setInterval(checkNotifications, 15000);
});