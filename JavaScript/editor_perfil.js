class ProfileEditor {
    constructor(config) {
        this.config = config;
        this.dom = {};
        this.state = { userSkills: [] };
        this.initDOM();
        this.initInitialState();
        this.bindEvents();
    }

    initDOM() {
        for (const key in this.config.elements) {
            this.dom[key] = document.getElementById(this.config.elements[key]);
        }
    }

    initInitialState() {
        this.dom.userSkillsGrid?.querySelectorAll('.skill-tag .remove-item-btn').forEach(btn => {
            const skillName = btn.dataset.value;
            if (skillName) this.state.userSkills.push(skillName);
        });
    }

    bindEvents() {
        document.querySelectorAll('.close-button').forEach(btn => btn.addEventListener('click', e => this.closeModal(e.target.closest('.modal'))));
        window.addEventListener('click', e => { if (e.target.classList.contains('modal')) this.closeModal(e.target); });
        this.dom.addSkillBtn?.addEventListener('click', () => this.openSkillModal());
        this.dom.addExperienceBtn?.addEventListener('click', () => this.openItemModal('experience'));
        this.dom.addEducationBtn?.addEventListener('click', () => this.openItemModal('education'));
        this.dom.editProfileBtn?.addEventListener('click', () => this.openModal(this.dom.editProfileModal));
        this.dom.userSkillsGrid?.addEventListener('click', e => this.handleItemAction(e));
        this.dom.userExperiencesList?.addEventListener('click', e => this.handleItemAction(e));
        this.dom.userEducationList?.addEventListener('click', e => this.handleItemAction(e));
        this.dom.editProfileForm?.addEventListener('submit', e => this.saveProfileInfo(e));
        this.dom.experienceForm?.addEventListener('submit', e => this.saveItem(e, 'experience'));
        this.dom.educationForm?.addEventListener('submit', e => this.saveItem(e, 'education'));
        this.dom.skillsForm?.addEventListener('submit', e => { e.preventDefault(); this.saveSkills(); });
        this.dom.saveSkillsBtn?.addEventListener('click', () => this.saveSkills());
        this.dom.expCurrentJob?.addEventListener('change', e => this.toggleEndDate(e.target, this.dom.expEndDate));
        this.dom.eduCurrentStudy?.addEventListener('change', e => this.toggleEndDate(e.target, this.dom.eduEndDate));
    }

    openModal(modalElement) { if (modalElement) modalElement.style.display = 'flex'; }
    closeModal(modalElement) {
        if (modalElement) {
            modalElement.style.display = 'none';
            const form = modalElement.querySelector('form');
            if (form) form.reset();
        }
    }

    toggleEndDate(checkbox, dateInput) {
        if (dateInput) {
            dateInput.disabled = checkbox.checked;
            if (checkbox.checked) dateInput.value = '';
        }
    }
    
    formatDate(dateString) {
        if (!dateString) return '';
        const date = new Date(dateString);
        return new Date(date.getTime() - (date.getTimezoneOffset() * 60000)).toISOString().split('T')[0];
    }
    
    async sendRequest(formData) {
        try {
            const response = await fetch(this.config.urls.update, { method: 'POST', body: formData });
            const data = await response.json();
            if (!data.success) throw new Error(data.message);
            alert(data.message || 'Operação realizada com sucesso!');
            return true;
        } catch (error) {
            alert(`Erro: ${error.message}`);
            return false;
        }
    }

    async fetchData(url) {
        try {
            const cacheBuster = `&t=${new Date().getTime()}`;
            const response = await fetch(url + cacheBuster);
            if (!response.ok) throw new Error(`Erro de rede: ${response.statusText}`);
            const data = await response.json();
            if (!data.success) throw new Error(data.message);
            return data;
        } catch (error) {
            console.error('Erro ao buscar dados:', error.message);
            return null;
        }
    }

    async saveProfileInfo(event) {
        event.preventDefault();
        const formData = new FormData(this.dom.editProfileForm);
        formData.append('action', 'update_profile_info');
        if (await this.sendRequest(formData)) {
            const updateText = (element, content, isHTML = false) => {
                if (element) isHTML ? (element.innerHTML = content) : (element.textContent = content);
            };
            updateText(this.dom.profileName, formData.get('name'));
            updateText(this.dom.userEmail, formData.get('email'));
            updateText(this.dom.userPhone, formData.get('phone'));
            updateText(this.dom.userAddress, formData.get('address'));
            updateText(this.dom.userCEP, formData.get('cep'));
            updateText(this.dom.aboutMeText, formData.get('about_me').replace(/\n/g, '<br>'), true);
            this.closeModal(this.dom.editProfileModal);
        }
    }

    openSkillModal() {
        if (this.dom.skillsInput) {
            this.dom.skillsInput.value = this.state.userSkills.join(', ');
        }
        this.openModal(this.dom.addSkillModal);
    }

    renderUserSkills() {
        if (!this.dom.userSkillsGrid) return;
        this.dom.userSkillsGrid.innerHTML = '';
        if (this.dom.noSkillsMessage) this.dom.noSkillsMessage.style.display = this.state.userSkills.length === 0 ? 'block' : 'none';
        
        this.state.userSkills.forEach(skill => {
            const tag = document.createElement('span');
            tag.className = 'skill-tag';
            tag.innerHTML = `${skill}<span class="remove-item-btn" data-type="skill" data-value="${skill}">&times;</span>`;
            this.dom.userSkillsGrid.appendChild(tag);
        });
    }

    async saveSkills() {
        const skillsText = this.dom.skillsInput.value;
        const skillsArray = skillsText.split(',').map(s => s.trim()).filter(Boolean);
        
        const formData = new FormData();
        formData.append('action', 'update_user_skills');
        formData.append('skills', JSON.stringify(skillsArray));

        if (await this.sendRequest(formData)) {
            this.state.userSkills = skillsArray;
            this.renderUserSkills();
            this.closeModal(this.dom.addSkillModal);
        }
    }
    
    openItemModal(type, itemData = null) {
        const form = type === 'experience' ? this.dom.experienceForm : this.dom.educationForm;
        const modal = type === 'experience' ? this.dom.editExperienceModal : this.dom.editEducationModal;
        const title = type === 'experience' ? 'Experiência' : 'Educação';
        if (!form) return;
        form.reset();
        modal.querySelector('h2').textContent = itemData ? `Editar ${title}` : `Adicionar ${title}`;
        form.querySelector('input[name="id"]').value = itemData ? itemData.id : '';
        form.querySelector('input[name="titulo"]').value = itemData ? itemData.titulo : '';
        form.querySelector('input[name="data_inicio"]').value = itemData ? this.formatDate(itemData.data_inicio) : '';
        const currentCheckbox = type === 'experience' ? this.dom.expCurrentJob : this.dom.eduCurrentStudy;
        const endDateInput = type === 'experience' ? this.dom.expEndDate : this.dom.eduEndDate;
        currentCheckbox.checked = itemData ? !itemData.data_fim : false;
        endDateInput.disabled = currentCheckbox.checked;
        endDateInput.value = itemData && itemData.data_fim ? this.formatDate(itemData.data_fim) : '';
        if (type === 'experience') {
            form.querySelector('input[name="empresa"]').value = itemData ? itemData.empresa : '';
            form.querySelector('textarea[name="descricao"]').value = itemData ? itemData.descricao : '';
        } else {
            form.querySelector('input[name="instituicao"]').value = itemData ? itemData.instituicao : '';
        }
        this.openModal(modal);
    }
    
    async loadAndRenderSection(type) {
        const endpointType = type + 's';
        const url = `${this.config.urls.getData}?type=${endpointType}`;
        const data = await this.fetchData(url);
        if (!data) return;
        const items = data[endpointType] || [];
        const listElement = this.dom[`user${type.charAt(0).toUpperCase() + type.slice(1)}sList`];
        const noDataElement = this.dom[`no${type.charAt(0).toUpperCase() + type.slice(1)}sMessage`];
        if (!listElement) return;
        listElement.innerHTML = '';
        if (noDataElement) noDataElement.style.display = items.length === 0 ? 'block' : 'none';
        items.forEach(item => {
            const itemElement = document.createElement('div');
            itemElement.className = `${type}-item`;
            itemElement.dataset.id = item.id;
            const startDate = new Date(item.data_inicio);
            const endDateText = item.data_fim ? new Date(item.data_fim).getUTCFullYear() : 'Atual';
            const subTitle = item.empresa || item.instituicao;
            itemElement.innerHTML = `<h3>${item.titulo}</h3><p>${subTitle}, ${startDate.getUTCFullYear()} - ${endDateText}</p>${item.descricao ? `<p>${item.descricao.replace(/\n/g, '<br>')}</p>` : ''}<div class="item-actions"><button class="edit-item-btn" data-type="${type}" data-id="${item.id}">Editar</button><button class="remove-item-btn" data-type="${type}" data-id="${item.id}">&times;</button></div>`;
            listElement.appendChild(itemElement);
        });
    }

    async saveItem(event, itemType) {
        event.preventDefault();
        const form = event.target;
        const formData = new FormData(form);
        formData.append('action', formData.get('id') ? `update_${itemType}` : `add_${itemType}`);
        const currentCheckbox = itemType === 'experience' ? this.dom.expCurrentJob : this.dom.eduCurrentStudy;
        if (currentCheckbox.checked) formData.set('data_fim', '');
        if (await this.sendRequest(formData)) {
            this.closeModal(form.closest('.modal'));
            this.loadAndRenderSection(itemType);
        }
    }

    async handleItemAction(event) {
        const actionTrigger = event.target.closest('.edit-item-btn, .remove-item-btn');
        if (!actionTrigger) return;
        const itemType = actionTrigger.dataset.type;
        if (!itemType) return;
        if (actionTrigger.classList.contains('remove-item-btn')) {
            const id = actionTrigger.dataset.id;
            const value = actionTrigger.dataset.value;
            const itemName = itemType === 'skill' ? `a habilidade "${value}"` : "este item";
            if (confirm(`Tem certeza que deseja remover ${itemName}?`)) {
                const formData = new FormData();
                formData.append('action', `remove_${itemType}`);
                itemType === 'skill' ? formData.append('skill_name', value) : formData.append('id', id);
                if (await this.sendRequest(formData)) {
                    if (itemType === 'skill') {
                        this.state.userSkills = this.state.userSkills.filter(s => s !== value);
                        this.renderUserSkills();
                    } else {
                        this.loadAndRenderSection(itemType);
                    }
                }
            }
        } else if (actionTrigger.classList.contains('edit-item-btn')) {
            const id = actionTrigger.dataset.id;
            const endpointType = itemType + 's';
            const data = await this.fetchData(`${this.config.urls.getData}?type=${endpointType}&id=${id}`);
            if (data && data.item) {
                this.openItemModal(itemType, data.item);
            }
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const config = {
        urls: {
            update: '../PHP/update_perfil.php',
            getData: '../PHP/get_profile_data.php',
            getSkills: '../PHP/skills.php'
        },
        elements: {}
    };
    const elementIds = [
        'editProfileBtn', 'editProfileModal', 'editProfileForm', 'profileName', 'userEmail', 'userPhone', 'userAddress', 'userCEP', 'aboutMeText',
        'addSkillBtn', 'addSkillModal', 'skillsForm', 'skillsInput', 'saveSkillsBtn', 'userSkillsGrid', 'noSkillsMessage',
        'addExperienceBtn', 'editExperienceModal', 'experienceForm', 'experienceModalTitle', 'userExperiencesList', 'noExperiencesMessage', 'expCurrentJob', 'expEndDate',
        'addEducationBtn', 'editEducationModal', 'educationForm', 'educationModalTitle', 'userEducationList', 'noEducationMessage', 'eduCurrentStudy', 'eduEndDate'
    ];
    elementIds.forEach(id => config.elements[id] = id);
    new ProfileEditor(config);
});