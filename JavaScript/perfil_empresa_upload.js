// solumatch_atualizado/JavaScript/perfil_empresa_upload.js

document.addEventListener('DOMContentLoaded', function() {
    // Elementos da página
    const picContainer = document.querySelector('.profile-pic-container');
    const fileInput = document.getElementById('profilePicInput');
    const profilePicDisplay = document.getElementById('empresaProfilePic'); // Imagem principal no perfil
    const headerProfileIcon = document.getElementById('headerProfileIcon'); // Ícone no menu dropdown

    // Se o container da imagem existir, adiciona o evento de clique
    if (picContainer) {
        picContainer.addEventListener('click', () => {
            fileInput.click(); // Aciona o clique no input de arquivo oculto
        });
    }

    // Quando um arquivo for selecionado no input
    if (fileInput) {
        fileInput.addEventListener('change', function() {
            const file = this.files[0];

            if (file) {
                // 1. Pré-visualiza a imagem selecionada
                const reader = new FileReader();
                reader.onload = function(e) {
                    // Atualiza a imagem no perfil e no ícone do header
                    if (profilePicDisplay) profilePicDisplay.src = e.target.result;
                    if (headerProfileIcon) headerProfileIcon.src = e.target.result;
                };
                reader.readAsDataURL(file);

                // 2. Envia a imagem para o servidor
                uploadProfilePicture(file);
            }
        });
    }

    // Função para enviar o arquivo via Fetch API
    function uploadProfilePicture(file) {
        const formData = new FormData();
        formData.append('profile_picture', file);

        // O endpoint PHP é o mesmo usado pelo perfil do usuário
        fetch('../PHP/upload_foto_perfil.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Foto do perfil atualizada com sucesso!');
                // Opcional: recarregar a página para garantir que o novo caminho da imagem seja permanente
                // window.location.reload(); 
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