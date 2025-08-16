function formatarCEP(campo) {
    // Pega a posição atual do cursor
    const posicaoCursor = campo.selectionStart;
    
    // Remove tudo que não é dígito
    let valor = campo.value.replace(/\D/g, "");
    
    // Aplica a formatação
    if (valor.length > 5) {
        valor = valor.substring(0, 5) + "-" + valor.substring(5, 8);
    }
    
    // Atualiza o valor no campo
    campo.value = valor;
    
    // Ajusta a posição do cursor
    if (posicaoCursor === 6 && valor.length > 5) {
        // Se estava na posição onde será inserido o hífen
        campo.setSelectionRange(posicaoCursor + 1, posicaoCursor + 1);
    } else {
        campo.setSelectionRange(posicaoCursor, posicaoCursor);
    }
}

document.addEventListener("DOMContentLoaded", () => {
    const campoCEP = document.querySelector('input[name="cep"]');
    if (campoCEP) {
        campoCEP.addEventListener("input", function() {
            formatarCEP(this);
        });
        
        // Opcional: busca automática ao perder o foco
        campoCEP.addEventListener("blur", function() {
            const cep = this.value.replace(/\D/g, "");
            if (cep.length === 8) {
                buscarEnderecoPorCEP(cep);
            }
        });
    }
});