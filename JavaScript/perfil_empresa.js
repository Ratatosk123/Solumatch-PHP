class EmpresaDashboard {
    constructor() {
        this.initDOM();
        // Apenas chama bindEvents se os elementos principais existirem
        if (this.addVagaBtn && this.editEmpresaBtn) {
            this.bindEvents();
        } else {
            console.error("Não foi possível encontrar todos os botões e modais necessários. Verifique os IDs no HTML.");
        }
    }

    initDOM() {
        // Elementos para vagas
        this.addVagaBtn = document.getElementById('postarVagaBtn');
        this.vagaModal = document.getElementById('vagaModal');
        this.vagaForm = document.getElementById('vagaForm');
        this.listaVagas = document.getElementById('listaDeVagas');
        
        // Elementos para edição do perfil da empresa
        this.editEmpresaBtn = document.getElementById('editEmpresaBtn');
        this.editEmpresaModal = document.getElementById('editEmpresaModal');
        this.editEmpresaForm = document.getElementById('editEmpresaForm');
    }

    bindEvents() {
        // Adiciona eventos apenas se os elementos existirem (código defensivo)
        if (this.addVagaBtn) this.addVagaBtn.addEventListener('click', () => this.openVagaModal());
        if (this.editEmpresaBtn) this.editEmpresaBtn.addEventListener('click', () => this.openEditEmpresaModal());

        if (this.vagaModal) {
            this.vagaModal.querySelector('.close-button').addEventListener('click', () => this.closeModal(this.vagaModal));
            if(this.vagaForm) this.vagaForm.addEventListener('submit', (e) => this.saveVaga(e));
        }

        if (this.editEmpresaModal) {
            this.editEmpresaModal.querySelector('.close-button').addEventListener('click', () => this.closeModal(this.editEmpresaModal));
            if(this.editEmpresaForm) this.editEmpresaForm.addEventListener('submit', (e) => this.saveEmpresaProfile(e));
        }

        if (this.listaVagas) this.listaVagas.addEventListener('click', (e) => this.handleVagaAction(e));

        window.addEventListener('click', (e) => {
            if (e.target === this.vagaModal) this.closeModal(this.vagaModal);
            if (e.target === this.editEmpresaModal) this.closeModal(this.editEmpresaModal);
        });
    }

    closeModal(modalElement) {
        if (modalElement) modalElement.style.display = 'none';
    }

    openEditEmpresaModal() {
        if (!this.editEmpresaForm) return;
        this.editEmpresaForm.querySelector('[name="name"]').value = document.getElementById('empresaNome').textContent;
        this.editEmpresaForm.querySelector('[name="email"]').value = document.getElementById('empresaEmail').textContent;
        this.editEmpresaForm.querySelector('[name="address"]').value = document.getElementById('empresaEndereco').textContent;
        this.editEmpresaForm.querySelector('[name="cep"]').value = document.getElementById('empresaCep').textContent;
        const sobreText = document.getElementById('empresaSobre').innerHTML.replace(/<br\s*[\/]?>/gi, "\n");
        this.editEmpresaForm.querySelector('[name="about_me"]').value = sobreText;
        
        this.editEmpresaModal.style.display = 'flex';
    }
    
    async saveEmpresaProfile(event) {
        event.preventDefault();
        const formData = new FormData(this.editEmpresaForm);
        formData.append('action', 'update_profile_info');
        try {
            const response = await fetch('../PHP/update_perfil.php', { method: 'POST', body: formData });
            const result = await response.json();
            if (!result.success) throw new Error(result.message);

            alert(result.message);
            document.getElementById('empresaNome').textContent = formData.get('name');
            document.getElementById('empresaEmail').textContent = formData.get('email');
            document.getElementById('empresaEndereco').textContent = formData.get('address');
            document.getElementById('empresaCep').textContent = formData.get('cep');
            document.getElementById('empresaSobre').innerHTML = formData.get('about_me').replace(/\n/g, "<br>");
            
            this.closeModal(this.editEmpresaModal);
        } catch (error) {
            alert(`Erro: ${error.message}`);
        }
    }

    openVagaModal(vagaData = null) {
        if (!this.vagaForm) return;
        this.vagaForm.reset();
        const modalTitle = document.getElementById('vagaModalTitle');
        if (vagaData) {
            modalTitle.textContent = 'Editar Vaga';
            this.vagaForm.querySelector('[name="id"]').value = vagaData.id;
            this.vagaForm.querySelector('[name="titulo"]').value = vagaData.titulo || '';
            this.vagaForm.querySelector('[name="descricao"]').value = vagaData.descricao || '';
            this.vagaForm.querySelector('[name="requisitos"]').value = vagaData.requisitos || '';
            this.vagaForm.querySelector('[name="categoria"]').value = vagaData.categoria || '';
            this.vagaForm.querySelector('[name="tipo_contratacao"]').value = vagaData.tipo_contratacao || '';
            this.vagaForm.querySelector('[name="localizacao"]').value = vagaData.localizacao || '';
            this.vagaForm.querySelector('[name="salario"]').value = vagaData.salario || '';
        } else {
            modalTitle.textContent = 'Adicionar Nova Vaga';
            this.vagaForm.querySelector('[name="id"]').value = '';
        }
        this.vagaModal.style.display = 'flex';
    }

    async saveVaga(event) {
        event.preventDefault();
        const formData = new FormData(this.vagaForm);
        const id = formData.get('id');
        formData.append('action', id ? 'update_vaga' : 'add_vaga');
        try {
            const response = await fetch('../PHP/vagas_handler.php', { method: 'POST', body: formData });
            const result = await response.json();
            if (!result.success) throw new Error(result.message);
            alert(result.message);
            window.location.reload();
        } catch (error) {
            alert(`Erro: ${error.message}`);
        }
    }

    handleVagaAction(event) {
        const editBtn = event.target.closest('.edit-item-btn');
        const removeBtn = event.target.closest('.remove-item-btn');

        if (editBtn) {
            try {
                const vagaData = JSON.parse(editBtn.dataset.vaga);
                this.openVagaModal(vagaData);
            } catch (e) {
                console.error("Erro ao processar dados da vaga:", e);
                alert("Não foi possível carregar os dados da vaga para edição.");
            }
            return;
        }

        if (removeBtn) {
            const vagaId = removeBtn.dataset.vagaId;
            if (confirm('Tem certeza que deseja excluir esta vaga?')) {
                this.deleteVaga(vagaId);
            }
        }
    }

    async deleteVaga(id) {
        const formData = new FormData();
        formData.append('action', 'delete_vaga');
        formData.append('id', id);
        try {
            const response = await fetch('../PHP/vagas_handler.php', { method: 'POST', body: formData });
            const result = await response.json();
            if (!result.success) throw new Error(result.message);
            
            alert(result.message);
            window.location.reload();
        } catch (error) {
            alert(`Erro: ${error.message}`);
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    new EmpresaDashboard();
});