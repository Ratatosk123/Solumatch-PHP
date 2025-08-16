document.addEventListener("DOMContentLoaded", function () {
    const campoCPF = document.querySelector('input[name="CPF"]');
    console.log("Documento carregado. Campo CPF encontrado:", campoCPF); // DEBUG

    if (campoCPF) {
        campoCPF.maxLength = 14; // 11 dígitos + 3 caracteres especiais
        campoCPF.addEventListener('input', formatarCPF);
        campoCPF.addEventListener('keydown', gerenciarBackspace);
        console.log("Eventos adicionados ao campo CPF."); // DEBUG
    }

    function formatarCPF(e) {
        let value = e.target.value.replace(/\D/g, '');
        console.log("Valor digitado (sem formatação):", value); // DEBUG

        // Limita a 11 dígitos
        if (value.length > 11) {
            value = value.substring(0, 11);
            console.log("Valor truncado para 11 dígitos:", value); // DEBUG
        }

        // Aplica a formatação
        let formattedValue = value;
        if (value.length > 3) {
            formattedValue = formattedValue.replace(/^(\d{3})/, '$1.');
        }
        if (value.length > 6) {
            formattedValue = formattedValue.replace(/^(\d{3})\.(\d{3})/, '$1.$2.');
        }
        if (value.length > 9) {
            formattedValue = formattedValue.replace(/^(\d{3})\.(\d{3})\.(\d{3})/, '$1.$2.$3-');
        }

        console.log("Valor formatado:", formattedValue); // DEBUG

        // Mantém a posição do cursor
        const position = e.target.selectionStart;
        const extraChars = formattedValue.length - e.target.value.length;
        e.target.value = formattedValue;

        // Ajusta a posição do cursor
        if (position === 4 || position === 8 || position === 12) {
            e.target.setSelectionRange(position + extraChars, position + extraChars);
        }
    }

    function gerenciarBackspace(e) {
        const value = e.target.value;
        if (e.key === 'Backspace') {
            console.log("Backspace pressionado. Valor atual:", value); // DEBUG
            if (value.length === 4 || value.length === 8 || value.length === 12) {
                e.preventDefault();
                e.target.value = value.substring(0, value.length - 1);
                console.log("Caractere especial removido com backspace."); // DEBUG
            }
        }
    }
});

function validarCPF(cpf) {
    cpf = cpf.replace(/\D/g, '');
    console.log("Validando CPF:", cpf); // DEBUG
    return cpf.length === 11;
}
