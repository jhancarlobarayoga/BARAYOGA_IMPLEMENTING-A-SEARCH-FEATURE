// Safe classroom phishing simulation.
// It deliberately does not read, store, submit, or transmit either input value.
document.addEventListener('DOMContentLoaded', function () {
    const button = document.getElementById('demo-signin');
    const result = document.getElementById('training-result');

    if (button && result) {
        button.addEventListener('click', function () {
            result.hidden = false;
            result.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        });
    }
});
