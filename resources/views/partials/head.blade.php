<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>{{ $title ?? 'ScryCaster' }}</title>

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance

<script>
document.addEventListener('alpine:init', () => {
    Alpine.store('appTheme', {
        currentTheme: localStorage.getItem('colorTheme') || 'havelock-blue', // Default theme

        themes: [
            { name: 'Havelock Blue', value: 'havelock-blue', isDefault: true },
            { name: 'Earthen & Arcane', value: 'earthen-arcane' },
            { name: 'Heroic & Fiery', value: 'heroic-fiery' },
            { name: 'Mystic & Verdant', value: 'mystic-verdant' },
        ],

        init() {
            this.applyTheme(this.currentTheme);
            // Watch for changes from other tabs/windows to localStorage item 'colorTheme'
            window.addEventListener('storage', (e) => {
                if (e.key === 'colorTheme' && e.newValue && e.newValue !== this.currentTheme) {
                    this.currentTheme = e.newValue;
                    this.applyTheme(this.currentTheme);
                }
            });

            // Ensure theme is applied if $flux.appearance changes (e.g. system theme causes change)
            // This is a bit speculative as we don't know $flux internals, but it's a safeguard.
            if (window.Alpine && Alpine.store('flux') && typeof Alpine.store('flux').appearance !== 'undefined') {
                Alpine.effect(() => {
                    // Re-apply our theme if flux appearance changes,
                    // just in case flux's own theme logic interferes with data-theme.
                    // This primarily ensures that if flux refreshes the class list, our data-theme is reapplied.
                    const fluxAppearance = Alpine.store('flux').appearance;
                    console.log(`Flux appearance changed to: ${fluxAppearance}, re-applying color theme: ${this.currentTheme}`);
                    this.applyTheme(this.currentTheme);
                });
            }
        },

        setTheme(themeName) {
            if (this.themes.find(t => t.value === themeName)) {
                this.currentTheme = themeName;
                localStorage.setItem('colorTheme', themeName);
                this.applyTheme(themeName);
            } else {
                console.warn(`Attempted to set unknown theme: ${themeName}`);
            }
        },

        applyTheme(themeName) {
            const htmlElement = document.documentElement;
            // Remove all known theme attributes first to handle switching from a specific theme back to default
            this.themes.forEach(theme => {
                if (theme.value !== 'havelock-blue') { // Don't remove default if it's not set via data-theme
                    htmlElement.removeAttribute(`data-theme-${theme.value}`);
                }
            });

            if (themeName && themeName !== 'havelock-blue') {
                htmlElement.setAttribute('data-theme', themeName);
            } else {
                htmlElement.removeAttribute('data-theme'); // Default theme means no data-theme attribute
            }
            console.log(`Applied color theme: ${themeName || 'havelock-blue (default)'}`);
        }
    });

    // Initialize the theme when Alpine is ready if the store exists
    if (Alpine.store('appTheme')) {
        Alpine.store('appTheme').init();
    }
});
</script>
