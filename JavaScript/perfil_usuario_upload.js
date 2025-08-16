// solumatch_atualizado/JavaScript/perfil_usuario_upload.js

document.addEventListener('DOMContentLoaded', function() {
    // Elementos da página de perfil do usuário
    const picContainer = document.querySelector('.profile-pic-container');
    const fileInput = document.getElementById('profilePicInput');
    const profilePicDisplay = document.getElementById('profilePicDisplay'); // Imagem principal no perfil
    const headerProfileIcon = document.getElementById('headerProfileIcon'); // Ícone no menu dropdown

    // Aciona o input de arquivo ao clicar no container da foto
    if (picContainer) {
        picContainer.addEventListener('click', () => {
            if (fileInput) fileInput.click();
        });
    }

    // Gerencia a seleção e o upload do arquivo
    if (fileInput) {
        fileInput.addEventListener('change', function() {
            const file = this.files[0];

            if (file) {
                // 1. Gera a pré-visualização da imagem
                const reader = new FileReader();
                reader.onload = function(e) {
                    if (profilePicDisplay) profilePicDisplay.src = e.target.result;
                    if (headerProfileIcon) headerProfileIcon.src = e.target.result;
                };
                reader.readAsDataURL(file);

                // 2. Envia a imagem para o servidor
                uploadProfilePicture(file);
            }
        });
    }

    // Função que envia o arquivo para o backend PHP
    function uploadProfilePicture(file) {
        const formData = new FormData();
        formData.append('profile_picture', file);

        // Usamos o mesmo script de backend que o perfil da empresa
        fetch('../PHP/upload_foto_perfil.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Foto do perfil atualizada com sucesso!');
            } else {
                alert('Erro ao atualizar a foto do perfil: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Erro na requisição:', error);
            alert('Erro de conexão ao servidor.');
        });
    }
});