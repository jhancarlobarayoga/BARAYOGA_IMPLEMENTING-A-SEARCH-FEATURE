// External JavaScript is allowed by the Content Security Policy (script-src 'self').
// The form action is cancelled if the user does not confirm the deletion.
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            const message = form.dataset.confirm || 'Are you sure you want to continue?';
            if (!window.confirm(message)) {
                event.preventDefault();
            }
        });
    });
});
