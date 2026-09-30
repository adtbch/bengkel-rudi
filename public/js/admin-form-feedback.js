document.addEventListener('submit', (event) => {
    const form = event.target;

    if (!(form instanceof HTMLFormElement) || form.dataset.submitting === 'true') {
        event.preventDefault();
        return;
    }

    form.dataset.submitting = 'true';
    form.querySelectorAll('button[type="submit"], input[type="submit"]').forEach((button) => {
        button.disabled = true;
        if (button.tagName === 'BUTTON' && !button.dataset.originalLabel) {
            button.dataset.originalLabel = button.textContent;
            button.textContent = 'Menyimpan...';
        }
    });
});

window.addEventListener('pageshow', () => {
    document.querySelectorAll('form[data-submitting="true"]').forEach((form) => {
        delete form.dataset.submitting;
        form.querySelectorAll('button[type="submit"], input[type="submit"]').forEach((button) => {
            button.disabled = false;
            if (button.dataset.originalLabel) button.textContent = button.dataset.originalLabel;
        });
    });
});
