document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('form-aggiunta-sala');
    if (!form) {
        return;
    }

    form.addEventListener('submit', (evento) => {
        const inizio = form.elements.inizio_occupazione.value;
        const fine = form.elements.fine_occupazione.value;

        if (inizio !== '' && fine !== '' && inizio >= fine) {
            evento.preventDefault();
            alert('L’orario di fine occupazione deve essere successivo a quello di inizio.');
            form.elements.fine_occupazione.focus();
        }
    });
});
