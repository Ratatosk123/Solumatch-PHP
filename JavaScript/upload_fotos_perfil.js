document.addEventListener('DOMContentLoaded', function() {
    const profilePicInput = document.getElementById('profilePicInput');
    const profilePicDisplay = document.getElementById('profilePicDisplay');
    const profileIconHeader = document.querySelector('.navbar .profile'); // A imagem do perfil no header

    // Função para carregar a foto de perfil do banco de dados na inicialização
    function loadProfilePicture() {
        // Em um ambiente real, você faria uma requisição AJAX
        // para um script PHP que retorna o caminho da imagem do usuário logado.
        // Por enquanto, vamos simular ou deixar para o PHP preencher diretamente.

        // Exemplo: se o PHP já inseriu o caminho da imagem no atributo data-src
        // ou você está lendo de uma variável JS global populada pelo PHP
        // const currentProfilePicPath = 'caminho/para/imagem/do/usuario.jpg'; // Viria do PHP
        // if (currentProfilePicPath) {
        //     profilePicDisplay.src = currentProfilePicPath;
        //     profileIconHeader.src = currentProfilePicPath;
        // }
    }

    // Pré-visualiza a imagem selecionada e envia para o servidor
    profilePicInput.addEventListener('change', function() {
        const file = this.files[0]; // Pega o primeiro arquivo selecionado

        if (file) {
            // Pré-visualiza a imagem
            const reader = new FileReader();
            reader.onload = function(e) {
                profilePicDisplay.src = e.target.result;
                profileIconHeader.src = e.target.result; // Atualiza a imagem no header também
            };
            reader.readAsDataURL(file);

            // Envia a imagem para o servidor
            uploadProfilePicture(file);
        }
    });

    function uploadProfilePicture(file) {
        const formData = new FormData();
        formData.append('profile_picture', file); // 'profile_picture' será o nome do campo no PHP

        fetch('../PHP/upload_foto_perfil.php', { // Caminho para o seu script PHP
            method: 'POST',
            body: formData
        })
        .then(response => response.json()) // Espera uma resposta JSON do PHP
        .then(data => {
            if (data.success) {
                alert('Foto de perfil atualizada com sucesso!');
                // Opcional: Atualizar a imagem com o caminho retornado pelo servidor
                // profilePicDisplay.src = data.new_path;
                // profileIconHeader.src = data.new_path;
            } else {
                alert('Erro ao atualizar a foto de perfil: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Erro na requisição:', error);
            alert('Erro de conexão ao servidor.');
        });
    }

    // Carrega a foto de perfil na inicialização
    loadProfilePicture();
});