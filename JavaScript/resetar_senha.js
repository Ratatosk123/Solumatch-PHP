document.addEventListener('DOMContentLoaded', function() {
            const passwordInput = document.getElementById('password');
            const confirmPasswordInput = document.getElementById('confirm_password');
            const submitButton = document.querySelector('button[type="submit"]');

            const reqLength = document.getElementById('reqLength');
            const reqUpper = document.getElementById('reqUpper');
            const reqLower = document.getElementById('reqLower');
            const reqNumber = document.getElementById('reqNumber');
            const reqSpecial = document.getElementById('reqSpecial');

            function validatePasswordComplexity() {
                const password = passwordInput.value;
                let isValid = true;

                // Requisito: Mínimo de 8 caracteres
                if (password.length >= 8) {
                    reqLength.classList.remove('invalid');
                    reqLength.classList.add('valid');
                    reqLength.querySelector('.icon').textContent = '✔';
                } else {
                    reqLength.classList.remove('valid');
                    reqLength.classList.add('invalid');
                    reqLength.querySelector('.icon').textContent = '✖';
                    isValid = false;
                }

                // Requisito: Pelo menos uma letra maiúscula
                if (/[A-Z]/.test(password)) {
                    reqUpper.classList.remove('invalid');
                    reqUpper.classList.add('valid');
                    reqUpper.querySelector('.icon').textContent = '✔';
                } else {
                    reqUpper.classList.remove('valid');
                    reqUpper.classList.add('invalid');
                    reqUpper.querySelector('.icon').textContent = '✖';
                    isValid = false;
                }

                // Requisito: Pelo menos uma letra minúscula
                if (/[a-z]/.test(password)) {
                    reqLower.classList.remove('invalid');
                    reqLower.classList.add('valid');
                    reqLower.querySelector('.icon').textContent = '✔';
                } else {
                    reqLower.classList.remove('valid');
                    reqLower.classList.add('invalid');
                    reqLower.querySelector('.icon').textContent = '✖';
                    isValid = false;
                }

                // Requisito: Pelo menos um número
                if (/[0-9]/.test(password)) {
                    reqNumber.classList.remove('invalid');
                    reqNumber.classList.add('valid');
                    reqNumber.querySelector('.icon').textContent = '✔';
                } else {
                    reqNumber.classList.remove('valid');
                    reqNumber.classList.add('invalid');
                    reqNumber.querySelector('.icon').textContent = '✖';
                    isValid = false;
                }

                // Requisito: Pelo menos um caractere especial
                if (/[^A-Za-z0-9]/.test(password)) {
                    reqSpecial.classList.remove('invalid');
                    reqSpecial.classList.add('valid');
                    reqSpecial.querySelector('.icon').textContent = '✔';
                } else {
                    reqSpecial.classList.remove('valid');
                    reqSpecial.classList.add('invalid');
                    reqSpecial.querySelector('.icon').textContent = '✖';
                    isValid = false;
                }

                // Desabilitar o botão de submissão se a senha não atender aos requisitos ou não coincidir
                submitButton.disabled = !(isValid && (password === confirmPasswordInput.value));
            }

            passwordInput.addEventListener('input', validatePasswordComplexity);
            confirmPasswordInput.addEventListener('input', validatePasswordComplexity);

            // Inicializa a validação ao carregar a página (caso o navegador preencha campos automaticamente)
            validatePasswordComplexity();
        });