/**
 * Formata o CNPJ no formato XX.XXX.XXX/XXXX-XX
 */
function formatarCNPJ(CNPJ) {
    let cnpjStr = CNPJ.replace(/\D/g, '');
    cnpjStr = cnpjStr.substring(0, 14);

    if (cnpjStr.length <= 2) {
        return cnpjStr;
    } else if (cnpjStr.length <= 5) {
        return `${cnpjStr.substring(0, 2)}.${cnpjStr.substring(2)}`;
    } else if (cnpjStr.length <= 8) {
        return `${cnpjStr.substring(0, 2)}.${cnpjStr.substring(2, 5)}.${cnpjStr.substring(5)}`;
    } else if (cnpjStr.length <= 12) {
        return `${cnpjStr.substring(0, 2)}.${cnpjStr.substring(2, 5)}.${cnpjStr.substring(5, 8)}/${cnpjStr.substring(8)}`;
    } else {
        return `${cnpjStr.substring(0, 2)}.${cnpjStr.substring(2, 5)}.${cnpjStr.substring(5, 8)}/${cnpjStr.substring(8, 12)}-${cnpjStr.substring(12)}`;
    }
}

/**
 * Aplica a formatação ao campo de CNPJ
 */
function aplicarFormatacaoCNPJ(input) {
    const valorFormatado = formatarCNPJ(input.value);
    input.value = valorFormatado;
}

// Vinculação do evento
document.addEventListener("DOMContentLoaded", function () {
    const campoCNPJ = document.querySelector('input[name="CNPJ"]');
    if (campoCNPJ) {
        console.log("Vinculando evento de formatação ao CNPJ");
        campoCNPJ.addEventListener("input", function () {
            aplicarFormatacaoCNPJ(campoCNPJ);
        });
    }
});