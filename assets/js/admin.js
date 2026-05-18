(function () {
    var root = document.querySelector('.def-wrap');
    if (!root) return;

    // ===== Switch d'onglets =====
    var tabs   = root.querySelectorAll('.def-tab');
    var panels = root.querySelectorAll('.def-tab-content');

    function activate(slug) {
        tabs.forEach(function (t) {
            var on = t.dataset.tab === slug;
            t.classList.toggle('active', on);
            t.setAttribute('aria-selected', on ? 'true' : 'false');
        });
        panels.forEach(function (p) {
            p.classList.toggle('active', p.dataset.panel === slug);
        });
    }

    tabs.forEach(function (t) {
        t.addEventListener('click', function (e) {
            e.preventDefault();
            activate(t.dataset.tab);
            if (history && history.replaceState) {
                history.replaceState(null, '', '#' + t.dataset.tab);
            }
        });
    });

    if (window.location.hash) {
        var slug = window.location.hash.replace('#', '');
        if (root.querySelector('.def-tab[data-tab="' + CSS.escape(slug) + '"]')) {
            activate(slug);
        }
    }

    // ===== Bouton "Remplacer la clé" =====
    var replaceBtn  = root.querySelector('[data-def-replace-toggle]');
    var replaceForm = root.querySelector('[data-def-replace-form]');
    if (replaceBtn && replaceForm) {
        replaceBtn.addEventListener('click', function () {
            replaceForm.classList.add('is-open');
            replaceBtn.style.display = 'none';
            var input = replaceForm.querySelector('input[name="api_key"]');
            if (input) input.focus();
        });
    }

    // ===== Confirmation suppression =====
    var deleteForm = root.querySelector('[data-def-delete-form]');
    if (deleteForm) {
        deleteForm.addEventListener('submit', function (e) {
            if (!window.confirm('Supprimer la clé API Google ? Les fonctionnalités associées (City Autocomplete) seront désactivées.')) {
                e.preventDefault();
            }
        });
    }

    // ===== Auto-scroll vers l'onglet en focus après POST =====
    var focused = root.querySelector('.def-tab-content.def-focus');
    if (focused) {
        setTimeout(function () {
            focused.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }, 100);
    }
})();
