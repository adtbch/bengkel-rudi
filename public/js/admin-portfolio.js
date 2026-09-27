document.querySelectorAll('[data-image-input]').forEach((input) => {
    const preview = input.closest('form')?.querySelector('[data-image-preview]');
    if (!preview) return;

    let objectUrls = [];

    const clearUrls = () => {
        objectUrls.forEach((url) => URL.revokeObjectURL(url));
        objectUrls = [];
    };

    const render = () => {
        clearUrls();
        preview.replaceChildren();

        Array.from(input.files).forEach((file, index) => {
            const url = URL.createObjectURL(file);
            objectUrls.push(url);

            const item = document.createElement('article');
            item.className = 'admin-image-preview__item';

            const image = document.createElement('img');
            image.src = url;
            image.alt = `Preview ${file.name}`;

            const details = document.createElement('div');
            const name = document.createElement('strong');
            name.textContent = file.name;
            const size = document.createElement('span');
            size.textContent = `${(file.size / 1024 / 1024).toFixed(2)} MB`;
            details.append(name, size);

            const remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'admin-button admin-button--danger';
            remove.textContent = 'Hapus pilihan';
            remove.addEventListener('click', () => {
                const files = new DataTransfer();
                Array.from(input.files).forEach((candidate, candidateIndex) => {
                    if (candidateIndex !== index) files.items.add(candidate);
                });
                input.files = files.files;
                render();
            });

            item.append(image, details, remove);
            preview.append(item);
        });
    };

    input.addEventListener('change', render);
    input.form?.addEventListener('reset', () => {
        clearUrls();
        preview.replaceChildren();
    });
    window.addEventListener('pagehide', clearUrls, { once: true });
});
