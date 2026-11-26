<!-- 3-Mode Theme Engine (Light / Dark / System) -->
<script>
    (function () {
        const media = window.matchMedia('(prefers-color-scheme: dark)');
        window.SmartCoopTheme = {
            getMode() {
                const saved = localStorage.getItem('theme');
                return (saved === 'light' || saved === 'dark' || saved === 'system') ? saved : 'system';
            },
            resolveDark(mode) {
                return mode === 'dark' || (mode !== 'light' && media.matches);
            },
            apply(mode) {
                const normalized = (mode === 'light' || mode === 'dark' || mode === 'system') ? mode : 'system';
                if (normalized === 'system') {
                    localStorage.removeItem('theme');
                } else {
                    localStorage.setItem('theme', normalized);
                }
                const dark = this.resolveDark(normalized);
                document.documentElement.classList.toggle('dark', dark);
                document.documentElement.style.backgroundColor = dark ? '#020617' : '#f1f5f9';
                window.dispatchEvent(new CustomEvent('theme-changed', { detail: { dark, mode: normalized } }));
                return { mode: normalized, dark };
            }
        };
        window.SmartCoopTheme.apply(window.SmartCoopTheme.getMode());
        media.addEventListener('change', () => {
            if (window.SmartCoopTheme.getMode() === 'system') {
                window.SmartCoopTheme.apply('system');
            }
        });
    })();
</script>
