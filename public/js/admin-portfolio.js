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

(() => {
    const MB = 1024 * 1024;
    const ALLOWED = {
        'image/jpeg': { resourceType: 'image', limit: 10 * MB },
        'image/png': { resourceType: 'image', limit: 10 * MB },
        'image/webp': { resourceType: 'image', limit: 10 * MB },
        'video/mp4': { resourceType: 'video', limit: 50 * MB },
        'video/webm': { resourceType: 'video', limit: 50 * MB },
        'video/quicktime': { resourceType: 'video', limit: 50 * MB },
    };

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const form = document.querySelector('[data-portfolio-form]');

    document.querySelectorAll('[data-media-input]').forEach((input) => {
        const list = form?.querySelector('[data-upload-list]');
        const summary = form?.querySelector('[data-upload-summary]');
        const signUrl = input.dataset.signUrl;
        const discardBase = input.dataset.discardUrlBase;
        if (!form || !list || !summary || !signUrl || !discardBase) return;

        const submitButton = form.querySelector('button[type="submit"].admin-button');
        const cards = new Map();
        let counter = 0;
        let pending = 0;

        const setPending = (delta) => {
            pending = Math.max(0, pending + delta);
            if (submitButton) submitButton.disabled = pending > 0;
            summary.textContent = pending > 0
                ? `${pending} berkas sedang diunggah ke penyimpanan...`
                : (cards.size > 0 ? `${cards.size} berkas siap disimpan.` : '');
        };

        const buildCard = (file, resourceType) => {
            const key = `u${++counter}`;
            const card = document.createElement('article');
            card.className = 'admin-upload-card';
            card.dataset.uploadKey = key;

            const thumb = document.createElement(resourceType === 'video' ? 'video' : 'img');
            thumb.className = 'admin-upload-card__thumb';
            thumb.alt = file.name;
            if (resourceType === 'video') thumb.muted = true;

            const body = document.createElement('div');
            body.className = 'admin-upload-card__body';

            const name = document.createElement('strong');
            name.textContent = file.name;

            const status = document.createElement('span');
            status.className = 'admin-upload-card__status';
            status.textContent = 'Menunggu...';

            const bar = document.createElement('progress');
            bar.className = 'admin-upload-card__bar';
            bar.max = 100;
            bar.value = 0;

            const actions = document.createElement('div');
            actions.className = 'admin-upload-card__actions';

            const remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'admin-button admin-button--danger-quiet';
            remove.textContent = 'Hapus';
            remove.addEventListener('click', () => discard(key, remove));

            actions.append(remove);
            body.append(name, status, bar, actions);
            card.append(thumb, body);
            list.append(card);

            const entry = { key, card, status, bar, inputs: [], mediaUploadId: null, objectUrl: null };
            cards.set(key, entry);
            return entry;
        };

        const fail = (entry, message) => {
            entry.status.textContent = message;
            entry.bar.remove();
            entry.card.classList.add('admin-upload-card--error');
        };

        const discard = async (key, removeButton) => {
            const entry = cards.get(key);
            if (!entry) return;

            const uploaded = Boolean(entry.mediaUploadId);
            removeButton.disabled = true;
            if (entry.objectUrl) URL.revokeObjectURL(entry.objectUrl);
            entry.card.remove();
            entry.inputs.forEach((node) => node.remove());
            cards.delete(key);
            setPending(-1);

            if (!uploaded || !entry.mediaUploadId) return;

            try {
                const response = await fetch(`${discardBase}/${entry.mediaUploadId}`, {
                    method: 'DELETE',
                    credentials: 'same-origin',
                    headers: { 'X-CSRF-TOKEN': csrfToken, Accept: 'application/json' },
                });
                if (!response.ok) {
                    summary.textContent = 'Berkas dihapus dari daftar, tetapi belum bisa dihapus dari penyimpanan. Jalankan php artisan media:prune-orphans.';
                }
            } catch {
                summary.textContent = 'Berkas dihapus dari daftar, tetapi belum bisa dihapus dari penyimpanan. Jalankan php artisan media:prune-orphans.';
            }
        };

        const addHidden = (entry, field, value) => {
            const node = document.createElement('input');
            node.type = 'hidden';
            node.name = `uploads[${entry.key}][${field}]`;
            node.value = String(value);
            form.append(node);
            entry.inputs.push(node);
        };

        const uploadToCloudinary = (payload, file, onProgress) => new Promise((resolve, reject) => {
            const xhr = new XMLHttpRequest();
            const body = new FormData();
            body.append('file', file);
            body.append('api_key', payload.api_key);
            body.append('timestamp', payload.params.timestamp);
            body.append('signature', payload.signature);
            body.append('public_id', payload.params.public_id);
            body.append('overwrite', payload.params.overwrite);

            xhr.open('POST', payload.upload_url);
            xhr.upload.addEventListener('progress', (event) => {
                if (event.lengthComputable) onProgress(Math.round((event.loaded / event.total) * 100));
            });
            xhr.addEventListener('load', () => {
                let parsed = {};
                try {
                    parsed = JSON.parse(xhr.responseText);
                } catch {
                    parsed = {};
                }
                if (xhr.status >= 200 && xhr.status < 300) {
                    resolve(parsed);
                    return;
                }
                reject(new Error(parsed.error?.message || `Cloudinary menolak unggahan (${xhr.status}).`));
            });
            xhr.addEventListener('error', () => reject(new Error('Koneksi terputus saat mengunggah.')));
            xhr.addEventListener('abort', () => reject(new Error('Unggahan dibatalkan.')));
            xhr.send(body);
        });

        const process = async (file) => {
            const meta = ALLOWED[file.type];
            if (!meta) {
                summary.textContent = `${file.name}: format tidak didukung. Gunakan JPG, PNG, WebP, MP4, WebM, atau MOV.`;
                return;
            }
            if (file.size > meta.limit) {
                summary.textContent = `${file.name}: ukuran melebihi ${meta.limit / MB} MB.`;
                return;
            }

            setPending(1);
            const entry = buildCard(file, meta.resourceType);
            entry.objectUrl = URL.createObjectURL(file);
            entry.card.querySelector(meta.resourceType === 'video' ? 'video' : 'img').src = entry.objectUrl;

            try {
                const signResponse = await fetch(signUrl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({
                        resource_type: meta.resourceType,
                        declared_size: file.size,
                        filename: file.name,
                    }),
                });

                if (!signResponse.ok) {
                    const body = await signResponse.json().catch(() => ({}));
                    throw new Error(body.message || `Server menolak permintaan tanda tangan (${signResponse.status}).`);
                }

                const payload = await signResponse.json();
                entry.status.textContent = 'Mengunggah...';
                const uploaded = await uploadToCloudinary(payload, file, (percent) => {
                    entry.bar.value = percent;
                    entry.status.textContent = `Mengunggah ${percent}%`;
                });

                entry.mediaUploadId = payload.media_upload_id;
                entry.bar.value = 100;
                entry.status.textContent = 'Siap disimpan';
                entry.card.classList.add('admin-upload-card--ready');

                addHidden(entry, 'media_upload_id', payload.media_upload_id);
                addHidden(entry, 'public_id', payload.params.public_id);
                addHidden(entry, 'secure_url', uploaded.secure_url);
                addHidden(entry, 'resource_type', payload.resource_type);
                addHidden(entry, 'token', payload.token);
            } catch (error) {
                fail(entry, error.message);
            }
        };

        input.addEventListener('change', () => {
            const files = Array.from(input.files);
            input.value = '';
            files.reduce((chain, file) => chain.then(() => process(file)), Promise.resolve());
        });
    });
})();
