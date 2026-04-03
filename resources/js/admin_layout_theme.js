(function () {
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    if (localStorage.theme === 'dark' || (!('theme' in localStorage) && prefersDark)) {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }
})();

window.toggleDarkMode = function () {
    if (document.documentElement.classList.contains('dark')) {
        document.documentElement.classList.remove('dark');
        localStorage.theme = 'light';
    } else {
        document.documentElement.classList.add('dark');
        localStorage.theme = 'dark';
    }
};

document.addEventListener('click', function (event) {
    var toggleTrigger = event.target.closest('[data-dark-mode-toggle]');
    if (!toggleTrigger) {
        return;
    }

    event.preventDefault();

    if (typeof window.toggleDarkMode === 'function') {
        window.toggleDarkMode();
    }
});

function revealBody() {
    if (document.body) {
        document.body.style.visibility = 'visible';
    }
}

document.addEventListener('DOMContentLoaded', revealBody);
document.addEventListener('turbo:load', revealBody);
document.addEventListener('turbo:render', revealBody);
