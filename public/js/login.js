const campoPassword = document.getElementById('password');

const pulsanteMostraPassword = document.querySelector(
    '[data-password-toggle]'
);

if (campoPassword && pulsanteMostraPassword) {

    pulsanteMostraPassword.addEventListener('click', function () {

        const passwordNascosta = campoPassword.type === 'password';

        if (passwordNascosta) {
            campoPassword.type = 'text';

            pulsanteMostraPassword.setAttribute(
                'aria-label',
                'Nascondi password'
            );

            pulsanteMostraPassword.setAttribute(
                'aria-pressed',
                'true'
            );
        } else {
            campoPassword.type = 'password';

            pulsanteMostraPassword.setAttribute(
                'aria-label',
                'Mostra password'
            );

            pulsanteMostraPassword.setAttribute(
                'aria-pressed',
                'false'
            );
        }

    });
}
