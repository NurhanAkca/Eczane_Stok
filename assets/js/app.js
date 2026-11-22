// Basit istemci tarafı geliştirmeler için ayrılmıştır.
document.querySelectorAll('[data-toggle-password]').forEach((button) => {
    const targetSelector = button.getAttribute('data-target');
    const input = targetSelector ? document.querySelector(targetSelector) : null;

    if (!(input instanceof HTMLInputElement)) {
        return;
    }

    const labelShow = button.getAttribute('data-label-show') || 'Show';
    const labelHide = button.getAttribute('data-label-hide') || 'Hide';
    const ariaShow = button.getAttribute('data-aria-show') || 'Show password';
    const ariaHide = button.getAttribute('data-aria-hide') || 'Hide password';

    button.addEventListener('click', () => {
        const showPassword = input.type === 'password';
        input.type = showPassword ? 'text' : 'password';
        button.textContent = showPassword ? labelHide : labelShow;
        button.setAttribute('aria-label', showPassword ? ariaHide : ariaShow);
        button.setAttribute('aria-pressed', showPassword ? 'true' : 'false');
    });
});
