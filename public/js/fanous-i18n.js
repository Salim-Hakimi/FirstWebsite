(function () {
    const LANG_KEY = 'fanous.lang';
    const THEME_KEY = 'fanous.theme';
    const SUPPORTED_LANGS = ['en', 'fa'];
    const SUPPORTED_THEMES = ['dark', 'light'];
    const textStore = new WeakMap();
    const attrNames = ['placeholder', 'title', 'aria-label', 'value'];
    let dictionaries = {};
    let currentLang = readStored(LANG_KEY, SUPPORTED_LANGS, document.documentElement.lang || 'en');
    let currentTheme = readStored(THEME_KEY, SUPPORTED_THEMES, document.documentElement.dataset.theme || 'dark');

    function readStored(key, allowed, fallback) {
        try {
            const value = localStorage.getItem(key);
            return allowed.includes(value) ? value : fallback;
        } catch (error) {
            return fallback;
        }
    }

    function saveStored(key, value) {
        try {
            localStorage.setItem(key, value);
        } catch (error) {
            // Storage can be disabled in private browsing; the UI still works for the current page.
        }
    }

    function normalize(text) {
        return text.replace(/\s+/g, ' ').trim();
    }

    function applyShellState() {
        const dir = currentLang === 'fa' ? 'rtl' : 'ltr';
        document.documentElement.lang = currentLang;
        document.documentElement.dir = dir;
        document.documentElement.dataset.theme = currentTheme;

        document.body.classList.toggle('rtl', currentLang === 'fa');
        document.body.classList.toggle('ltr', currentLang !== 'fa');
        document.body.classList.toggle('theme-dark', currentTheme === 'dark');
        document.body.classList.toggle('theme-light', currentTheme === 'light');
        document.body.classList.toggle('locale-fa', currentLang === 'fa');
        document.body.classList.toggle('locale-en', currentLang === 'en');
    }

    async function loadDictionary(lang) {
        if (dictionaries[lang]) {
            return dictionaries[lang];
        }

        try {
            const response = await fetch(`/i18n/${lang}.json`, { cache: 'no-cache' });
            dictionaries[lang] = response.ok ? await response.json() : {};
        } catch (error) {
            dictionaries[lang] = {};
        }

        return dictionaries[lang];
    }

    function translateValue(value, dictionary) {
        const key = normalize(value);
        return dictionary[key] || value;
    }

    function translateKey(key, dictionary) {
        return dictionary[key] || key;
    }

    function translateKeyedElements(root, dictionary) {
        root.querySelectorAll('[data-i18n]').forEach((element) => {
            element.textContent = translateKey(element.getAttribute('data-i18n'), dictionary);
        });

        root.querySelectorAll('[data-i18n-placeholder]').forEach((element) => {
            element.setAttribute('placeholder', translateKey(element.getAttribute('data-i18n-placeholder'), dictionary));
        });

        root.querySelectorAll('[data-i18n-title]').forEach((element) => {
            element.setAttribute('title', translateKey(element.getAttribute('data-i18n-title'), dictionary));
        });

        root.querySelectorAll('[data-i18n-aria-label]').forEach((element) => {
            element.setAttribute('aria-label', translateKey(element.getAttribute('data-i18n-aria-label'), dictionary));
        });
    }

    function translateTextNode(node, dictionary) {
        if (!node.nodeValue || normalize(node.nodeValue) === '') {
            return;
        }

        const parent = node.parentElement;
        if (!parent || parent.closest('[data-i18n], [data-i18n-skip], script, style, code, pre')) {
            return;
        }

        if (!textStore.has(node)) {
            textStore.set(node, node.nodeValue);
        }

        const original = textStore.get(node);
        const leading = original.match(/^\s*/)?.[0] || '';
        const trailing = original.match(/\s*$/)?.[0] || '';
        const translated = translateValue(original, dictionary);
        node.nodeValue = leading + normalize(translated) + trailing;
    }

    function translateAttributes(root, dictionary) {
        root.querySelectorAll('*').forEach((element) => {
            if (element.closest('[data-i18n-skip], script, style')) {
                return;
            }

            attrNames.forEach((attr) => {
                if (!element.hasAttribute(attr)) {
                    return;
                }

                const keyedAttr = attr === 'aria-label' ? 'data-i18n-aria-label' : `data-i18n-${attr}`;
                if (element.hasAttribute(keyedAttr)) {
                    return;
                }

                if (attr === 'value') {
                    const type = (element.getAttribute('type') || '').toLowerCase();
                    const translatableValue = element.tagName === 'BUTTON' || ['button', 'submit', 'reset'].includes(type);
                    if (!translatableValue) {
                        return;
                    }
                }

                const storeAttr = `data-i18n-original-${attr}`;
                if (!element.hasAttribute(storeAttr)) {
                    element.setAttribute(storeAttr, element.getAttribute(attr));
                }

                element.setAttribute(attr, translateValue(element.getAttribute(storeAttr), dictionary));
            });
        });
    }

    function translateDom(root, dictionary) {
        translateKeyedElements(root, dictionary);

        const walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT);
        const nodes = [];

        while (walker.nextNode()) {
            nodes.push(walker.currentNode);
        }

        nodes.forEach((node) => translateTextNode(node, dictionary));
        translateAttributes(root, dictionary);
    }

    function updateControls() {
        document.querySelectorAll('[data-lang-toggle]').forEach((button) => {
            const label = button.querySelector('[data-lang-label]');
            if (label) {
                label.textContent = currentLang === 'fa' ? 'English' : 'فارسی';
            }
            button.setAttribute('aria-label', currentLang === 'fa' ? 'Switch to English' : 'تغییر به فارسی');
        });

        document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
            const label = button.querySelector('[data-theme-label]');
            if (label) {
                label.textContent = currentTheme === 'dark'
                    ? (currentLang === 'fa' ? 'حالت روشن' : 'Light mode')
                    : (currentLang === 'fa' ? 'حالت تاریک' : 'Dark mode');
            }
            button.setAttribute('aria-label', currentTheme === 'dark' ? 'Switch to light mode' : 'Switch to dark mode');
        });
    }

    async function applyI18n() {
        applyShellState();
        const dictionary = await loadDictionary(currentLang);
        translateDom(document.body, dictionary);
        updateControls();
    }

    function bindControls() {
        document.addEventListener('click', (event) => {
            const langButton = event.target.closest('[data-lang-toggle]');
            const themeButton = event.target.closest('[data-theme-toggle]');

            if (langButton) {
                event.preventDefault();
                currentLang = currentLang === 'fa' ? 'en' : 'fa';
                saveStored(LANG_KEY, currentLang);
                applyI18n();
            }

            if (themeButton) {
                event.preventDefault();
                currentTheme = currentTheme === 'dark' ? 'light' : 'dark';
                saveStored(THEME_KEY, currentTheme);
                applyI18n();
            }
        });
    }

    applyShellState();

    document.addEventListener('DOMContentLoaded', () => {
        bindControls();
        applyI18n();
    });
})();
