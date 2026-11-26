for (const pulsanteMostraPassword of document.querySelectorAll('[data-password-toggle]')) {
    const idCampi = pulsanteMostraPassword.getAttribute('aria-controls')?.trim().split(/\s+/) ?? [];
    const campiPassword = idCampi.map(id => document.getElementById(id)).filter(Boolean);

    if (campiPassword.length === 0) {
        continue;
    }

    pulsanteMostraPassword.hidden = false;

    pulsanteMostraPassword.addEventListener('click', () => {
        const mostra = campiPassword.some(campo => campo.type === 'password');
        const azione = mostra ? 'Nascondi' : 'Mostra';
        const etichetta = azione + ' password';

        for (const campo of campiPassword) {
            campo.type = mostra ? 'text' : 'password';
        }

        pulsanteMostraPassword.textContent = campiPassword.length > 1 ? etichetta : azione;
        pulsanteMostraPassword.setAttribute('aria-label', etichetta);
    });
}
