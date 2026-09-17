document.querySelectorAll('[data-toast-demo]').forEach((button) => {
    button.addEventListener('click', () => {
        window.toast?.show(button.dataset.toastMessage || '', {
            type: button.dataset.toastType || 'info',
            title: button.dataset.toastTitle || '',
        });
    });
});
