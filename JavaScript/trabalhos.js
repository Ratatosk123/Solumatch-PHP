document.addEventListener('DOMContentLoaded', function() {
    // Dados mockados de jobs (simulando API)
    const jobsData = [
        {
            id: 1,
            title: "Desenvolvedor Front-end React",
            budget: 2500,
            description: "Precisamos de um desenvolvedor front-end com experiência em React para criar interfaces responsivas...",
            skills: ["React", "JavaScript", "CSS"],
            posted: "2 dias atrás",
            proposals: 15,
            category: "Programação",
            type: "fixed-price",
            highlighted: false
        },
        {
            id: 2,
            title: "Designer de Logo Profissional",
            budget: 800,
            description: "Procuramos designer para criar identidade visual completa para nova marca de cosméticos...",
            skills: ["Adobe Illustrator", "Branding"],
            posted: "1 dia atrás",
            proposals: 8,
            category: "Design",
            type: "fixed-price",
            highlighted: true
        },
        {
            id: 3,
            title: "Copywriter para Artigos",
            budget: 1200,
            description: "Necessário redator para produção de 10 artigos sobre tecnologia com 1.500 palavras cada...",
            skills: ["Redação", "SEO"],
            posted: "5 horas atrás",
            proposals: 3,
            category: "Redação",
            type: "fixed-price",
            highlighted: false
        },
        {
            id: 4,
            title: "Social Media Manager",
            budget: 45,
            description: "Gerenciamento de redes sociais com criação de conteúdo para Instagram e LinkedIn...",
            skills: ["Instagram", "LinkedIn", "Marketing Digital"],
            posted: "3 dias atrás",
            proposals: 12,
            category: "Marketing",
            type: "hourly-rate",
            highlighted: false
        }
    ];

    // Elementos DOM
    const DOM = {
        jobsList: document.querySelector('.jobs-list'),
        searchInput: document.querySelector('.search-input'),
        searchButton: document.querySelector('.search-button'),
        sortingSelect: document.querySelector('.sorting-select'),
        filterItems: document.querySelectorAll('.filter-item'),
        filterCheckboxes: document.querySelectorAll('.filter-checkbox input'),
        pagination: document.querySelector('.jobs-pagination')
    };

    // Estado da aplicação
    const state = {
        currentPage: 1,
        jobsPerPage: 5,
        activeCategory: 'Todos',
        activeFilters: {
            type: ['fixed-price', 'hourly-rate']
        },
        sortBy: 'recent',
        searchTerm: ''
    };

    // Inicialização
    init();

    function init() {
        renderJobs();
        setupEventListeners();
        renderPagination();
    }

    // Configura eventos
    function setupEventListeners() {
        // Busca
        DOM.searchButton.addEventListener('click', handleSearch);
        DOM.searchInput.addEventListener('keyup', (e) => {
            if (e.key === 'Enter') handleSearch();
        });

        // Ordenação
        DOM.sortingSelect.addEventListener('change', (e) => {
            state.sortBy = e.target.value.toLowerCase().replace(' ', '-');
            renderJobs();
        });

        // Filtros de categoria
        DOM.filterItems.forEach(item => {
            item.addEventListener('click', () => {
                DOM.filterItems.forEach(i => i.classList.remove('active'));
                item.classList.add('active');
                state.activeCategory = item.textContent;
                state.currentPage = 1;
                renderJobs();
                renderPagination();
            });
        });

        // Filtros de tipo
        DOM.filterCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', () => {
                const type = checkbox.id.replace('-', '_');
                if (checkbox.checked) {
                    if (!state.activeFilters.type.includes(type)) {
                        state.activeFilters.type.push(type);
                    }
                } else {
                    state.activeFilters.type = state.activeFilters.type.filter(t => t !== type);
                }
                state.currentPage = 1;
                renderJobs();
                renderPagination();
            });
        });

        // Paginação
        DOM.pagination.addEventListener('click', handlePaginationClick);
    }

    // Manipuladores de eventos
    function handleSearch() {
        state.searchTerm = DOM.searchInput.value.trim().toLowerCase();
        state.currentPage = 1;
        renderJobs();
        renderPagination();
    }

    function handlePaginationClick(e) {
        if (e.target.classList.contains('pagination-button')) {
            if (e.target.textContent === '...') return;
            state.currentPage = parseInt(e.target.textContent);
            renderJobs();
            updateActivePaginationButton();
        } else if (e.target.classList.contains('pagination-next')) {
            state.currentPage++;
            renderJobs();
            renderPagination();
        }
    }

    // Filtra e ordena jobs
    function getFilteredJobs() {
        let filtered = [...jobsData];

        // Filtro por categoria
        if (state.activeCategory !== 'Todos') {
            filtered = filtered.filter(job => job.category === state.activeCategory);
        }

        // Filtro por tipo
        filtered = filtered.filter(job => state.activeFilters.type.includes(job.type));

        // Filtro por busca
        if (state.searchTerm) {
            filtered = filtered.filter(job => 
                job.title.toLowerCase().includes(state.searchTerm) ||
                job.description.toLowerCase().includes(state.searchTerm) ||
                job.skills.some(skill => skill.toLowerCase().includes(state.searchTerm))
            );
        }

        // Ordenação
        switch (state.sortBy) {
            case 'menor-orçamento':
                filtered.sort((a, b) => a.budget - b.budget);
                break;
            case 'maior-orçamento':
                filtered.sort((a, b) => b.budget - a.budget);
                break;
            default: // mais recentes
                filtered.sort((a, b) => new Date(b.posted) - new Date(a.posted));
        }

        return filtered;
    }

    // Renderiza jobs na página
    function renderJobs() {
        const filteredJobs = getFilteredJobs();
        const startIdx = (state.currentPage - 1) * state.jobsPerPage;
        const jobsToShow = filteredJobs.slice(startIdx, startIdx + state.jobsPerPage);

        DOM.jobsList.innerHTML = jobsToShow.map(job => `
            <article class="job-card ${job.highlighted ? 'highlighted' : ''}">
                <div class="job-header">
                    <h2 class="job-title">${job.title}</h2>
                    <span class="job-budget">${job.type === 'hourly-rate' ? `R$ ${job.budget}/hora` : `R$ ${job.budget.toFixed(2).replace('.', ',')}`}</span>
                </div>
                <p class="job-description">${job.description}</p>
                <div class="job-skills">
                    ${job.skills.map(skill => `<span class="skill-tag">${skill}</span>`).join('')}
                </div>
                <div class="job-footer">
                    <span class="job-posted">Postado ${job.posted}</span>
                    <span class="job-proposals">${job.proposals} propostas</span>
                </div>
            </article>
        `).join('');

        // Atualiza contagem de jobs encontrados
        updateJobsCount(filteredJobs.length);
    }

    // Atualiza contador de jobs
    function updateJobsCount(count) {
        const counter = document.querySelector('.jobs-count') || document.createElement('div');
        counter.className = 'jobs-count';
        counter.textContent = `${count} ${count === 1 ? 'job encontrado' : 'jobs encontrados'}`;
        
        if (!document.querySelector('.jobs-count')) {
            DOM.jobsList.insertAdjacentElement('beforebegin', counter);
        }
    }

    // Renderiza paginação
    function renderPagination() {
        const filteredJobs = getFilteredJobs();
        const totalPages = Math.ceil(filteredJobs.length / state.jobsPerPage);
        
        if (totalPages <= 1) {
            DOM.pagination.style.display = 'none';
            return;
        }
        
        DOM.pagination.style.display = 'flex';
        let paginationHTML = '';
        
        // Botões de página
        for (let i = 1; i <= totalPages; i++) {
            if (i <= 3 || i > totalPages - 2 || Math.abs(i - state.currentPage) <= 1) {
                paginationHTML += `<button class="pagination-button ${i === state.currentPage ? 'active' : ''}">${i}</button>`;
            } else if (paginationHTML.slice(-1) !== '...') {
                paginationHTML += `<span class="pagination-ellipsis">...</span>`;
            }
        }
        
        // Botão próximo
        if (state.currentPage < totalPages) {
            paginationHTML += `<button class="pagination-next">Próximo &raquo;</button>`;
        }
        
        DOM.pagination.innerHTML = paginationHTML;
    }

    // Atualiza botão ativo na paginação
    function updateActivePaginationButton() {
        document.querySelectorAll('.pagination-button').forEach(button => {
            button.classList.toggle('active', parseInt(button.textContent) === state.currentPage);
        });
    }
});

// Adicionar transições suaves
DOM.jobsList.style.transition = 'opacity 0.3s ease';
DOM.jobsList.style.opacity = '0';
setTimeout(() => {
    DOM.jobsList.style.opacity = '1';
}, 300);