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

        themes: [ // For the UI picker
            { name: 'Havelock Blue', value: 'havelock-blue', isDefault: true },
            { name: 'Earthen & Arcane', value: 'earthen-arcane' },
            { name: 'Heroic & Fiery', value: 'heroic-fiery' },
            { name: 'Mystic & Verdant', value: 'mystic-verdant' },
        ],

        // RGB string palettes for Filament theming
        // These are based on standard Tailwind colors.
        palettes: {
            havelockBlue: { // Custom "Havelock Blue" - approximating from existing vars or using a standard blue
                '50':  '239, 246, 255', // Sky-50 (example)
                '100': '219, 234, 254', // Sky-100
                '200': '191, 219, 254', // Sky-200
                '300': '147, 197, 253', // Sky-300
                '400': '96, 165, 250',  // Sky-400 - our app's --color-havelock-blue-400 is #6badef (107, 173, 239) - let's use actuals
                                        // Using general blue for now, will need to map Havelock specific shades
                '50':  '240, 247, 254', // --color-havelock-blue-50: #f0f7fe;
                '100': '222, 236, 251', // --color-havelock-blue-100: #deecfb;
                '200': '196, 223, 249', // --color-havelock-blue-200: #c4dff9;
                '300': '155, 202, 245', // --color-havelock-blue-300: #9bcaf5;
                '400': '107, 173, 239', // --color-havelock-blue-400: #6badef;
                '500': '85, 150, 234',  // --color-havelock-blue-500: #5596ea;
                '600': '51, 114, 221',  // --color-havelock-blue-600: #3372dd;
                '700': '43, 93, 202',   // --color-havelock-blue-700: #2b5dca;
                '800': '40, 77, 165',   // --color-havelock-blue-800: #284da5;
                '900': '38, 67, 130',   // --color-havelock-blue-900: #264382;
                '950': '27, 42, 80',    // --color-havelock-blue-950: #1b2a50;
            },
            emerald: { // Tailwind Emerald
                '50':  '236, 253, 245', '100': '209, 250, 229', '200': '167, 243, 208', '300': '110, 231, 183',
                '400': '52, 211, 153',  '500': '16, 185, 129',  '600': '5, 150, 105',   '700': '4, 120, 87',
                '800': '6, 95, 70',     '900': '6, 78, 59',     '950': '2, 44, 34'
            },
            azure: { // Tailwind Sky (used for "Azure" in CSS)
                '50':  '240, 249, 255', '100': '224, 242, 254', '200': '186, 230, 253', '300': '125, 211, 252',
                '400': '56, 189, 248',  '500': '14, 165, 233',  '600': '2, 132, 199',    '700': '3, 105, 161',
                '800': '7, 89, 133',    '900': '12, 74, 110',   '950': '8, 51, 68'
                // Our CSS azure (e.g. --color-azure-400: #60a5fa;) is closer to Tailwind Blue
                // Let's use Tailwind Blue for 'azure' to match our CSS
            },
            blue: { // Tailwind Blue (to match --color-azure-xxx which seems to be based on it)
                '50': '239, 246, 255', '100': '219, 234, 254', '200': '191, 219, 254', '300': '147, 197, 253',
                '400': '96, 165, 250',  '500': '59, 130, 246',  '600': '37, 99, 235',   '700': '29, 78, 216',
                '800': '30, 64, 175',   '900': '30, 58, 138',   '950': '23, 37, 84'
            },
            teal: { // Tailwind Teal
                '50':  '240, 253, 250', '100': '204, 251, 241', '200': '153, 246, 228', '300': '94, 234, 212',
                '400': '45, 212, 191',  '500': '20, 184, 166',  '600': '13, 148, 136',  '700': '15, 118, 110',
                '800': '17, 94, 89',    '900': '19, 78, 74',    '950': '4, 47, 46'
            },
            zinc: { // Tailwind Zinc
                '50':  '250, 250, 250', '100': '244, 244, 245', '200': '228, 228, 231', '300': '212, 212, 216',
                '400': '161, 161, 170', '500': '113, 113, 122', '600': '82, 82, 91',    '700': '63, 63, 70',
                '800': '39, 39, 42',    '900': '24, 24, 27',    '950': '9, 9, 11'
            }
        },

        get currentTheme() {
            return this._currentTheme;
        },

        set currentTheme(themeName) {
            if (this._currentTheme === themeName) return;
            const foundTheme = this.themes.find(t => t.value === themeName);
            if (foundTheme) {
                this._currentTheme = themeName;
                localStorage.setItem('colorTheme', themeName);
                this.applyAppTheme(themeName);
            } else {
                console.warn(`Attempted to set unknown theme via x-model: ${themeName}`);
            }
        },

        init() {
            this.applyAppTheme(this._currentTheme);
            window.addEventListener('storage', (e) => {
                if (e.key === 'colorTheme' && e.newValue && e.newValue !== this._currentTheme) {
                    this._currentTheme = e.newValue;
                    this.applyAppTheme(this._currentTheme);
                }
            });
        },

        applyAppTheme(themeName) {
            const htmlElement = document.documentElement;
            if (themeName && themeName !== 'havelock-blue') {
                htmlElement.setAttribute('data-theme', themeName);
            } else {
                htmlElement.removeAttribute('data-theme');
            }
            console.log(`Applied app color theme attribute for: ${themeName || 'havelock-blue (default)'}`);
            this.applyFilamentThemeStyles(themeName); // Apply Filament styles
        },

        applyFilamentThemeStyles(appThemeValue) {
            let primaryPaletteKey;
            switch (appThemeValue) {
                case 'earthen-arcane': primaryPaletteKey = 'emerald'; break;
                case 'heroic-fiery':   primaryPaletteKey = 'blue'; break; // Matched to Tailwind Blue for Azure
                case 'mystic-verdant': primaryPaletteKey = 'teal'; break;
                case 'havelock-blue':
                default: primaryPaletteKey = 'havelockBlue'; break;
            }

            const selectedPrimaryPalette = this.palettes[primaryPaletteKey];
            const grayPalette = this.palettes.zinc;
            let cssOverrides = ':root {\\n';

            if (selectedPrimaryPalette) {
                for (const shade in selectedPrimaryPalette) {
                    cssOverrides += `    --primary-${shade}-rgb: ${selectedPrimaryPalette[shade]};\\n`;
                }
            }
            if (grayPalette) {
                for (const shade in grayPalette) {
                    cssOverrides += `    --gray-${shade}-rgb: ${grayPalette[shade]};\\n`;
                }
            }
            cssOverrides += '}';

            // Handle Filament's dark mode by providing specific overrides if html.dark exists
            // Filament applies .dark to html tag.
            // We need to check if Filament uses different variable names for dark mode or if it just relies on its components adapting to the new --primary/--gray
            // For now, assuming Filament's components will adapt to the new --primary-xxx-rgb and --gray-xxx-rgb under .dark selector if needed.
            // If Filament uses vars like --primary-dark-500, we'd need more complex logic.
            // The current approach changes the base :root variables, which should affect both light and dark mode rendering in Filament if its components use these vars.

            let styleTag = document.getElementById('filament-dynamic-theme-styles');
            if (!styleTag) {
                styleTag = document.createElement('style');
                styleTag.id = 'filament-dynamic-theme-styles';
                document.head.appendChild(styleTag);
            }
            styleTag.textContent = cssOverrides;
            console.log('Applied Filament dynamic styles for theme:', appThemeValue);
        }
    });

    if (Alpine.store('appTheme')) {
        Alpine.store('appTheme').init();
    }
});
</script>
