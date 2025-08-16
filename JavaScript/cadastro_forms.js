document.addEventListener('DOMContentLoaded', function() {
    // Verifica se há mensagem de erro
    const erroGeral = document.querySelector('.alert-danger');
    
    if (erroGeral) {
        // Foca no primeiro campo com erro ou no primeiro campo do formulário
        const campoComErro = document.querySelector('.campo-invalido');
        if (campoComErro) {
            campoComErro.focus();
        } else {
            const primeiroInput = document.querySelector('input');
            if (primeiroInput) {
                primeiroInput.focus();
            }
        }
    }
});