(function () {
    function withModalQuery(url) {
        const parsed = new URL(url, window.location.origin);
        parsed.searchParams.set('fanous_modal', '1');

        return parsed.toString();
    }

    function ensureModal() {
        let modal = document.querySelector('[data-fanous-page-modal-shell]');

        if (modal) {
            return modal;
        }

        modal = document.createElement('div');
        modal.className = 'fanous-page-modal';
        modal.dataset.fanousPageModalShell = 'true';
        modal.innerHTML = [
            '<button class="fanous-page-modal__backdrop" type="button" data-fanous-page-modal-close aria-label="بستن"></button>',
            '<section class="fanous-page-modal__panel" role="dialog" aria-modal="true">',
            '<header class="fanous-page-modal__header">',
            '<strong data-fanous-page-modal-title>فورم</strong>',
            '<button type="button" data-fanous-page-modal-close aria-label="بستن">×</button>',
            '</header>',
            '<iframe class="fanous-page-modal__frame" title="فورم" loading="eager"></iframe>',
            '</section>',
        ].join('');
        document.body.appendChild(modal);

        modal.querySelectorAll('[data-fanous-page-modal-close]').forEach(function (button) {
            button.addEventListener('click', closeModal);
        });

        return modal;
    }

    function closeModal() {
        const modal = document.querySelector('[data-fanous-page-modal-shell]');

        if (!modal) {
            return;
        }

        modal.classList.remove('is-visible');
        document.body.classList.remove('fanous-page-modal-open');
        modal.querySelector('iframe').removeAttribute('src');
    }

    function openModal(link) {
        const modal = ensureModal();
        const iframe = modal.querySelector('iframe');
        const title = link.dataset.modalTitle || link.textContent.trim() || 'فورم';
        const initialPath = new URL(link.href, window.location.origin).pathname;

        modal.querySelector('[data-fanous-page-modal-title]').textContent = title;
        iframe.src = withModalQuery(link.href);
        iframe.dataset.initialPath = initialPath;

        modal.classList.add('is-visible');
        document.body.classList.add('fanous-page-modal-open');
        modal.querySelector('[data-fanous-page-modal-close]')?.focus();
    }

    document.addEventListener('click', function (event) {
        const link = event.target.closest('a[data-fanous-page-modal]');

        if (!link || event.defaultPrevented || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
            return;
        }

        event.preventDefault();
        openModal(link);
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && document.body.classList.contains('fanous-page-modal-open')) {
            closeModal();
        }
    });

    window.addEventListener('message', function (event) {
        if (event.origin !== window.location.origin || event.data?.type !== 'fanous:modal-saved') {
            return;
        }

        closeModal();
        window.location.reload();
    });

    document.addEventListener('DOMContentLoaded', function () {
        ensureModal().querySelector('iframe').addEventListener('load', function (event) {
            const iframe = event.currentTarget;

            try {
                const location = iframe.contentWindow.location;
                const initialPath = iframe.dataset.initialPath;

                if (initialPath && location.pathname !== initialPath && !location.search.includes('fanous_modal=1')) {
                    closeModal();
                    window.location.reload();
                }
            } catch (error) {
                // Cross-origin is not expected, but the modal should stay open if the browser blocks access.
            }
        });
    });
})();
