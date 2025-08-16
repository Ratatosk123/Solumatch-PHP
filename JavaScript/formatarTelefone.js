// Adicione esta função antes do event listener
function formatarTelefone(numero) {
    let numeroStr = numero.replace(/\D/g, '');
    numeroStr = numeroStr.substring(0, 11);
    
    if (numeroStr.length <= 2) {
        return `(${numeroStr}`;
    } else if (numeroStr.length <= 7) {
        return `(${numeroStr.substring(0, 2)}) ${numeroStr.substring(2)}`;
    } else {
        return `(${numeroStr.substring(0, 2)}) ${numeroStr.substring(2, 7)}-${numeroStr.substring(7)}`;
    }
}

// Adicione esta função (que estava faltando)
function aplicarFormatacaoTelefone(input) {
    const valorFormatado = formatarTelefone(input.value);
    input.value = valorFormatado;
}

// Mantenha o restante do código
document.addEventListener("DOMContentLoaded", function () {
    const campoTelefone = document.querySelector('input[name="numero"]');
    if (campoTelefone) {
        console.log("Vinculando evento de formatação ao telefone");
        campoTelefone.addEventListener("input", function () {
            aplicarFormatacaoTelefone(campoTelefone);
        });
    }
});