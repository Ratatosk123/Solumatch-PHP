// JavaScript/acessibilidade_tts.js

document.addEventListener('DOMContentLoaded', () => {
    // --- VERIFICAÇÃO DE COMPATIBILIDADE ---
    const isTTSSupported = 'speechSynthesis' in window;
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    const isRecognitionSupported = !!SpeechRecognition;

    // --- ELEMENTOS DA INTERFACE ---
    const ttsButton = document.getElementById('header-tts-toggle') || document.getElementById('floating-tts-toggle');
    const voiceCommandButton = document.getElementById('voice-command-toggle');

    if (!isTTSSupported || !ttsButton) {
        if(ttsButton) ttsButton.style.display = 'none';
        if(voiceCommandButton) voiceCommandButton.style.display = 'none';
        return;
    }
    if (!isRecognitionSupported) {
        if(voiceCommandButton) voiceCommandButton.style.display = 'none';
    }

    // --- ESTADO DA APLICAÇÃO ---
    let isReadingEnabled = sessionStorage.getItem('tts-enabled') === 'true';
    let recognition;

    // --- FUNÇÕES DE SÍNTESE DE VOZ (FALAR) ---
    function speak(text, force = false, callback = null) {
        if ((!isReadingEnabled && !force) || !text) {
            if (callback) callback();
            return;
        }
        window.speechSynthesis.cancel();
        const utterance = new SpeechSynthesisUtterance(text);
        utterance.lang = 'pt-BR';
        utterance.onend = () => {
            if (callback) callback();
        };
        window.speechSynthesis.speak(utterance);
    }
    
    // --- LÓGICA DE RECONHECIMENTO DE VOZ (OUVIR) ---

    function startRecognition() {
        if (!isRecognitionSupported || recognition) return;

        recognition = new SpeechRecognition();
        recognition.lang = 'pt-BR';
        recognition.continuous = true;
        recognition.interimResults = false;

        recognition.onstart = () => {
            if (voiceCommandButton) voiceCommandButton.classList.add('listening');
        };

        recognition.onerror = (event) => {
            console.error('Erro no reconhecimento de voz:', event.error);
        };
        
        recognition.onend = () => {
            if (voiceCommandButton) voiceCommandButton.classList.remove('listening');
            if (isReadingEnabled) {
                setTimeout(() => {
                    if (recognition) recognition.start();
                }, 250);
            }
        };

        recognition.onresult = (event) => {
            for (let i = event.resultIndex; i < event.results.length; ++i) {
                const transcript = event.results[i][0].transcript.toLowerCase().trim();
                console.log('Comando reconhecido:', transcript);
                executeVoiceCommand(transcript);
            }
        };
        
        try {
            recognition.start();
        } catch (e) {
            console.error("Erro ao iniciar o reconhecimento:", e);
        }
    }

    function stopRecognition() {
        if (recognition) {
            recognition.stop();
            recognition = null;
        }
    }
    
    // --- FUNÇÕES DE COMANDO E INTERAÇÃO ---

    function speakGuidance() {
        if (!isReadingEnabled) {
            speak("Ative a acessibilidade no botão de áudio para usar os comandos de voz.", true);
            return;
        }
        stopRecognition();

        // **CORREÇÃO APLICADA AQUI**
        let instructions = 'Comandos disponíveis: "meu perfil", "meus chats", "voltar para o menu principal", ou diga o nome de uma categoria, como "programação". Diga "repetir" para ouvir estas instruções novamente.';
        
        speak(instructions, true, () => {
            if (isReadingEnabled) {
                startRecognition();
            }
        });
    }

    function executeVoiceCommand(transcript) {
        const cleanTranscript = transcript.endsWith('.') ? transcript.slice(0, -1) : transcript;

        if (cleanTranscript.includes('repetir')) {
            speakGuidance();
            return;
        }
        
        if (cleanTranscript.includes('voltar para o menu principal')) {
            speak("Retornando para a página principal de trabalhos.", true, () => {
                window.location.href = 'trabalhos.php';
            });
            return;
        }
        
        if (cleanTranscript.includes('meu perfil') || cleanTranscript.includes('perfil da empresa')) {
            const profileLink = document.querySelector('#dropdownMenu a[href*="perfil"]');
            if (profileLink) {
                speak("Navegando para a página de perfil.", true, () => window.location.href = profileLink.href);
            }
            return;
        }

        if (cleanTranscript.includes('meus chats')) {
            const chatsLink = document.querySelector('#dropdownMenu a[href*="meus_chats"]');
            if (chatsLink) {
                speak("Navegando para Meus Chats.", true, () => window.location.href = chatsLink.href);
            }
            return;
        }
        
        const availableCategories = Array.from(document.querySelectorAll('.filter-item')).map(el => el.textContent.toLowerCase().trim());
        for (const categoryName of availableCategories) {
            if (cleanTranscript.includes(categoryName)) {
                findCategoryAndClick(categoryName);
                return;
            }
        }
    }

    function findCategoryAndClick(categoryName) {
        const categories = document.querySelectorAll('.filter-item');
        for (const category of categories) {
            if (category.textContent.toLowerCase().trim() === categoryName) {
                speak(`Filtrando vagas por categoria: ${category.textContent}`, true);
                category.click();
                return;
            }
        }
        speak(`Categoria ${categoryName} não encontrada.`, true);
    }
    
    // --- EVENTOS DOS BOTÕES ---

    ttsButton.addEventListener('click', () => {
        isReadingEnabled = !isReadingEnabled;
        sessionStorage.setItem('tts-enabled', isReadingEnabled);
        updateButtonState();

        if (isReadingEnabled) {
            speak('Leitura de tela e comandos de voz ativados.', true);
            startRecognition();
        } else {
            speak('Acessibilidade desativada.', true);
            window.speechSynthesis.cancel();
            stopRecognition();
        }
    });

    if (voiceCommandButton) {
        voiceCommandButton.addEventListener('click', speakGuidance);
    }
    
    // O restante do código, como `processInteraction` e `updateButtonState`, permanece o mesmo.
    const interactiveSelector = 'a, button, [role="button"], input, li.filter-item, .nav-link, .cta-button, .faq-question, .job-card';
    function processInteraction(element) {
        if (!isReadingEnabled || !element) return;
        if (element.closest('.tts-toggle-button, #voice-command-toggle')) return;
        let textToSpeak = element.getAttribute('aria-label') || element.title || element.textContent.trim();
        if (textToSpeak) speak(textToSpeak);
    }
    document.body.addEventListener('click', (event) => {
        const targetElement = event.target.closest(interactiveSelector);
        if (targetElement) processInteraction(targetElement);
    });
    document.body.addEventListener('focusin', (event) => {
        const targetElement = event.target.closest(interactiveSelector);
        if (targetElement) processInteraction(targetElement);
    });
    function updateButtonState() {
        if (isReadingEnabled) {
            ttsButton.classList.add('active');
            ttsButton.setAttribute('aria-label', 'Desativar leitura de tela.');
        } else {
            ttsButton.classList.remove('active');
            ttsButton.setAttribute('aria-label', 'Ativar leitura de tela.');
        }
    }
    updateButtonState();
    if(isReadingEnabled) {
       startRecognition();
    }
});