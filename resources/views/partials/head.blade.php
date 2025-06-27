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
        _currentTheme: localStorage.getItem('colorTheme') || 'havelock-blue',

        themes: [
            { name: 'Havelock Blue', value: 'havelock-blue', isDefault: true },
            { name: 'Earthen & Arcane', value: 'earthen-arcane' },
            { name: 'Heroic & Fiery', value: 'heroic-fiery' },
            { name: 'Mystic & Verdant', value: 'mystic-verdant' },
        ],

        get currentTheme() {
            return this._currentTheme;
        },

        set currentTheme(themeName) {
            // Avoid feedback loop if the value is already set (e.g. from init or another tab)
            if (this._currentTheme === themeName) {
                // Ensure theme is applied even if value is same, e.g. on init or if localStorage was manually changed
                // this.applyTheme(themeName); // This might be too aggressive if called from init
                return;
            }

            const foundTheme = this.themes.find(t => t.value === themeName);
            if (foundTheme) {
                console.log(`Setting currentTheme from '${this._currentTheme}' to '${themeName}'`);
                this._currentTheme = themeName;
                localStorage.setItem('colorTheme', themeName);
                this.applyTheme(themeName); // Apply the theme when currentTheme is changed by x-model
            } else {
                console.warn(`Attempted to set unknown theme via x-model: ${themeName}`);
            }
        },

        init() {
            console.log(`Initializing appTheme with _currentTheme: ${this._currentTheme}`);
            this.applyTheme(this._currentTheme); // Apply initial theme based on localStorage or default

            window.addEventListener('storage', (e) => {
                if (e.key === 'colorTheme' && e.newValue && e.newValue !== this._currentTheme) {
                    console.log(`Storage event: colorTheme changed from '${this._currentTheme}' to '${e.newValue}'`);
                    this._currentTheme = e.newValue; // Update internal state
                    this.applyTheme(this._currentTheme); // Apply theme
                }
            });

            // This effect for $flux.appearance can be kept if needed.
            // For now, let's see if the direct approach works without it, to minimize complexity.
            // if (window.Alpine && Alpine.store('flux') && typeof Alpine.store('flux').appearance !== 'undefined') {
            //     Alpine.effect(() => {
            //         const fluxAppearance = Alpine.store('flux').appearance;
            //         console.log(`Flux appearance changed to: ${fluxAppearance}, re-applying color theme: ${this._currentTheme}`);
            //         this.applyTheme(this._currentTheme);
            //     });
            // }
        },

        applyTheme(themeName) {
            const htmlElement = document.documentElement;
            // Clear previous theme attribute to ensure clean switching
            // This assumes only one data-theme attribute is used.
            // htmlElement.removeAttribute('data-theme'); // Simpler removal

            if (themeName && themeName !== 'havelock-blue') {
                htmlElement.setAttribute('data-theme', themeName);
            } else {
                htmlElement.removeAttribute('data-theme'); // Default theme means no data-theme attribute
            }
            console.log(`Applied actual color theme attribute for: ${themeName || 'havelock-blue (default)'}`);
        }
    });

    if (Alpine.store('appTheme')) {
        Alpine.store('appTheme').init();
    }
});
</script>
