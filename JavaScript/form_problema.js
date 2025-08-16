// Perguntas dinâmicas por categoria
const questionsData = {
    ti: [
        {
            question: "Que tipo de problema em TI você passando?",
            options: [
                "Desenvolvimento de website",
                "Aplicativo móvel",
                "Sistema de e-commerce",
                "Software personalizado",
                "Computador quebrado",
                "Monitor não esta dando imagem",
                "Outro"
            ]
        },
        {
            question: "Qual é o prazo estimado para resolução do problema?",
            options: [
                "Urgente (menos de 1 semana)",
                "Curto prazo (1-2 semanas)",
                "Médio prazo (1-3 meses)",
                "Longo prazo (mais de 3 meses)",
                "Ainda não definido"
            ]
        }
    ],
    design: [
        {
            question: "Que tipo de serviço de design você precisa?",
            options: [
                "Logo e identidade visual",
                "Design de website",
                "Ilustrações",
                "Edição de vídeo",
                "Design gráfico"
            ]
        },
        {
            question: "Você já tem algum material de referência?",
            options: [
                "Sim, tenho referências claras",
                "Tenho algumas ideias",
                "Não, preciso de ajuda para definir"
            ]
        }
    ],
    traducao: [
        {
            question: "Que tipo de conteúdo precisa ser traduzido?",
            options: [
                "Documentos técnicos",
                "Site ou aplicativo",
                "Conteúdo acadêmico",
                "Material de marketing",
                "Outro"
            ]
        },
        {
            question: "De/para quais idiomas?",
            options: [
                "Português para Inglês",
                "Inglês para Português",
                "Espanhol para Português",
                "Outra combinação"
            ]
        }
    ],
    marketing: [
        {
            question: "Que tipo de serviço de marketing você precisa?",
            options: [
                "Gestão de redes sociais",
                "Publicidade online",
                "E-mail marketing",
                "SEO e conteúdo",
                "Estratégia de marca"
            ]
        },
        {
            question: "Qual o orçamento aproximado?",
            options: [
                "Até R$ 500",
                "R$ 500 - R$ 1.500",
                "R$ 1.500 - R$ 3.000",
                "Acima de R$ 3.000",
                "Ainda não definido"
            ]
        }
    ],
    suporte: [
        {
            question: "Que tipo de suporte administrativo você precisa?",
            options: [
                "Atendimento ao cliente",
                "Organização de documentos",
                "Agendamentos",
                "Digitação e transcrição",
                "Outro"
            ]
        }
    ],
    juridico: [
        {
            question: "Que tipo de serviço jurídico você precisa?",
            options: [
                "Contratos",
                "Consultoria legal",
                "Registro de marca",
                "Direito trabalhista",
                "Outro"
            ]
        }
    ],
    financas: [
        {
            question: "Que tipo de serviço financeiro você precisa?",
            options: [
                "Contabilidade",
                "Declaração de impostos",
                "Planejamento financeiro",
                "Análise de investimentos",
                "Outro"
            ]
        }
    ]
};


// Elementos do DOM
const checkboxes = document.querySelectorAll('.category-checkbox');
const continueBtn = document.getElementById('continueBtn');
const categoriesSection = document.getElementById('categoriesSection');
const questionsSection = document.getElementById('questionsSection');
const dynamicQuestions = document.getElementById('dynamicQuestions');
const questionsTitle = document.getElementById('questionsTitle');
const submitBtn = document.getElementById('submitBtn');
const backBtn = document.getElementById('backBtn');


// Habilita o botão Continuar quando pelo menos uma checkbox estiver marcada
checkboxes.forEach(checkbox => {
    checkbox.addEventListener('change', () => {
        const atLeastOneChecked = [...checkboxes].some(cb => cb.checked);
        continueBtn.disabled = !atLeastOneChecked;
    });
});


// Mostra perguntas dinâmicas ao clicar em Continuar
continueBtn.addEventListener('click', () => {
    const selectedCategories = [...checkboxes]
        .filter(cb => cb.checked)
        .map(cb => cb.value);
   
    // Esconde a seção de categorias e mostra a de perguntas
    categoriesSection.style.display = 'none';
    questionsSection.style.display = 'block';
   
    // Gera as perguntas dinâmicas
    generateDynamicQuestions(selectedCategories);
});


// Volta para a seleção de categorias
backBtn.addEventListener('click', () => {
    questionsSection.style.display = 'none';
    categoriesSection.style.display = 'block';
});


// Envia o formulário (simulação)
submitBtn.addEventListener('click', () => {
    alert('Formulário enviado com sucesso!');
    // Aqui você pode adicionar o código para enviar os dados para o servidor
});


// Função para gerar perguntas dinâmicas
function generateDynamicQuestions(categories) {
    dynamicQuestions.innerHTML = '';
   
    // Atualiza o título com as categorias selecionadas
    const categoryNames = categories.map(cat => {
        switch(cat) {
            case 'ti': return 'TI e Programação';
            case 'design': return 'Design e Multimedia';
            case 'traducao': return 'Tradução e conteúdos';
            case 'marketing': return 'Marketing e Vendas';
            case 'suporte': return 'Suporte Administrativo';
            case 'juridico': return 'Jurídico';
            case 'financas': return 'Finanças e Administração';
            default: return cat;
        }
    });
   
    questionsTitle.textContent = `Perguntas sobre ${categoryNames.join(' e ')}`;
   
    // Adiciona perguntas de cada categoria selecionada
    categories.forEach(category => {
        if (questionsData[category]) {
            questionsData[category].forEach((q, qIndex) => {
                const questionGroup = document.createElement('div');
                questionGroup.className = 'question-group';
               
                const questionTitle = document.createElement('div');
                questionTitle.className = 'question-title';
                questionTitle.textContent = q.question;
               
                const questionOptions = document.createElement('div');
                questionOptions.className = 'question-options';
               
                q.options.forEach((option, oIndex) => {
                    const optionId = `q${qIndex}_${category}_o${oIndex}`;
                   
                    const optionLabel = document.createElement('label');
                    optionLabel.className = 'option-label';
                   
                    const optionRadio = document.createElement('input');
                    optionRadio.type = 'radio';
                    optionRadio.name = `q${qIndex}_${category}`;
                    optionRadio.id = optionId;
                    optionRadio.value = option;
                    optionRadio.className = 'option-radio';
                   
                    const optionText = document.createTextNode(option);
                   
                    optionLabel.appendChild(optionRadio);
                    optionLabel.appendChild(optionText);
                    questionOptions.appendChild(optionLabel);
                });
               
                questionGroup.appendChild(questionTitle);
                questionGroup.appendChild(questionOptions);
                dynamicQuestions.appendChild(questionGroup);
            });
        }
    });
}