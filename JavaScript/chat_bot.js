// ... (código existente) ...

document.addEventListener('DOMContentLoaded', () => {
    const CHAT_API_URL = 'http://localhost/solumatch_atualizado/PHP/chat_api.php'; // Verifique se este caminho está correto!
    const modalChatbot = document.getElementById('modalChatbot');
    const botoesAbrirChatbot = document.querySelectorAll('.contact-button');
    const botaoFechar = document.querySelector('.fechar-btn');
    const inputChat = document.getElementById('inputChat');
    const botaoEnviarMensagem = document.getElementById('enviarMensagem');
    const areaChat = document.getElementById('areaChat');

    let sessionId = sessionStorage.getItem('chatSessionId');
    if (!sessionId) {
        sessionId = Date.now().toString();
        sessionStorage.setItem('chatSessionId', sessionId);
    }

    function exibirMensagem(texto, tipo) {
        const mensagemDiv = document.createElement('div');
        mensagemDiv.classList.add('mensagem', `${tipo}-mensagem`);
        
        // Trata quebras de linha para exibição HTML
        mensagemDiv.innerHTML = texto.replace(/\n/g, '<br>');
        
        areaChat.appendChild(mensagemDiv);
        areaChat.scrollTop = areaChat.scrollHeight;
    }

    async function enviarMensagem(messageText) {
        if (!messageText.trim() && messageText !== '') return; // Não envia mensagens vazias, a menos que seja para iniciar o chat

        // Apenas exibe a mensagem do usuário se não for um comando de inicialização oculto
        if (messageText !== 'INICIAR_CHAT') {
            exibirMensagem(messageText, 'user');
        }
        
        inputChat.value = ''; // Limpa o input
        
        // Adiciona um loading enquanto espera a resposta
        const loadingDiv = document.createElement('div');
        loadingDiv.classList.add('mensagem', 'bot-mensagem', 'loading');
        loadingDiv.textContent = 'Digitando...'; // Ou um spinner CSS
        areaChat.appendChild(loadingDiv);
        areaChat.scrollTop = areaChat.scrollHeight;

        try {
            const response = await fetch(CHAT_API_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    message: messageText,
                    session_id: sessionId
                }),
            });

            // Remove o loading
            areaChat.removeChild(loadingDiv);

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(`HTTP error! status: ${response.status}, detalhe: ${JSON.stringify(errorData)}`);
            }

            const data = await response.json();
            exibirMensagem(data.reply, 'bot');
            sessionId = data.session_id; // Atualiza session_id caso o PHP tenha gerado um novo
            sessionStorage.setItem('chatSessionId', sessionId); // Salva o novo session_id
        } catch (error) {
            // Remove o loading mesmo em caso de erro
            if (areaChat.contains(loadingDiv)) {
                 areaChat.removeChild(loadingDiv);
            }
            exibirMensagem(`Erro ao enviar mensagem para o chatbot: ${error.message}`, 'bot');
            console.error('Erro na requisição:', error);
        } finally {
            areaChat.scrollTop = areaChat.scrollHeight;
        }
    }

    // Lógica para o botão de enviar mensagem
    botaoEnviarMensagem.addEventListener('click', () => {
        enviarMensagem(inputChat.value);
    });

    // Lógica para enviar mensagem ao pressionar Enter
    inputChat.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            enviarMensagem(inputChat.value);
        }
    });

    // Abre o modal do chatbot
    botoesAbrirChatbot.forEach(botao => {
        botao.addEventListener('click', async () => {
            modalChatbot.style.display = 'flex';
            
            // Verifica se é a primeira vez que o modal é aberto na sessão
            // ou se o chat está vazio. Se sim, solicita o menu principal.
            if (areaChat.innerHTML === '' || areaChat.children.length <= 1) { // Verifica se há apenas a saudação inicial ou nada
                 areaChat.innerHTML = ''; // Limpa o chat para iniciar
                 exibirMensagem('Olá! Seja bem-vindo(a) ao Solumatch. Estou aqui para ajudar você.','bot');
                 // Envia uma mensagem vazia para o PHP, que será interpretada como pedido do menu principal
                 await enviarMensagem(''); // Isso irá disparar a exibição do menu principal pelo PHP
            }
            inputChat.focus();
            areaChat.scrollTop = areaChat.scrollHeight;
        });
    });

    // Fecha o modal do chatbot
    botaoFechar.addEventListener('click', () => {
        modalChatbot.style.display = 'none';
    });

    // Fecha o modal se clicar fora dele (opcional)
    window.addEventListener('click', (event) => {
        if (event.target == modalChatbot) {
            modalChatbot.style.display = 'none';
        }
    });
});