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

        palettes: {
            havelockBlue: {
                '50':  { rgb: '240, 247, 254', hex: '#f0f7fe' }, '100': { rgb: '222, 236, 251', hex: '#deecfb' },
                '200': { rgb: '196, 223, 249', hex: '#c4dff9' }, '300': { rgb: '155, 202, 245', hex: '#9bcaf5' },
                '400': { rgb: '107, 173, 239', hex: '#6badef' }, '500': { rgb: '85, 150, 234',  hex: '#5596ea' },
                '600': { rgb: '51, 114, 221',  hex: '#3372dd' }, '700': { rgb: '43, 93, 202',   hex: '#2b5dca' },
                '800': { rgb: '40, 77, 165',   hex: '#284da5' }, '900': { rgb: '38, 67, 130',   hex: '#264382' },
                '950': { rgb: '27, 42, 80',    hex: '#1b2a50' }
            },
            emerald: { // Tailwind Emerald
                '50':  { rgb: '236, 253, 245', hex: '#ecfdf5' }, '100': { rgb: '209, 250, 229', hex: '#d1fae5' },
                '200': { rgb: '167, 243, 208', hex: '#a7f3d0' }, '300': { rgb: '110, 231, 183', hex: '#6ee7b7' },
                '400': { rgb: '52, 211, 153',  hex: '#34d399' }, '500': { rgb: '16, 185, 129',  hex: '#10b981' },
                '600': { rgb: '5, 150, 105',   hex: '#059669' }, '700': { rgb: '4, 120, 87',    hex: '#047857' },
                '800': { rgb: '6, 95, 70',     hex: '#065f46' }, '900': { rgb: '6, 78, 59',     hex: '#064e3b' },
                '950': { rgb: '2, 44, 34',     hex: '#022c22' }
            },
            blue: { // Tailwind Blue (for "heroic-fiery" theme's azure)
                '50':  { rgb: '239, 246, 255', hex: '#eff6ff' }, '100': { rgb: '219, 234, 254', hex: '#dbeafe' },
                '200': { rgb: '191, 219, 254', hex: '#bfdbfe' }, '300': { rgb: '147, 197, 253', hex: '#93c5fd' },
                '400': { rgb: '96, 165, 250',  hex: '#60a5fa' }, '500': { rgb: '59, 130, 246',  hex: '#3b82f6' },
                '600': { rgb: '37, 99, 235',   hex: '#2563eb' }, '700': { rgb: '29, 78, 216',   hex: '#1d4ed8' },
                '800': { rgb: '30, 64, 175',   hex: '#1e40af' }, '900': { rgb: '30, 58, 138',   hex: '#1e3a8a' },
                '950': { rgb: '23, 37, 84',    hex: '#172554' }
            },
            teal: { // Tailwind Teal
                '50':  { rgb: '240, 253, 250', hex: '#f0fdfa' }, '100': { rgb: '204, 251, 241', hex: '#ccfbf1' },
                '200': { rgb: '153, 246, 228', hex: '#99f6e4' }, '300': { rgb: '94, 234, 212',  hex: '#5eead4' },
                '400': { rgb: '45, 212, 191',  hex: '#2dd4bf' }, '500': { rgb: '20, 184, 166',  hex: '#14b8a6' },
                '600': { rgb: '13, 148, 136',  hex: '#0d9488' }, '700': { rgb: '15, 118, 110',  hex: '#0f766e' },
                '800': { rgb: '17, 94, 89',    hex: '#115e59' }, '900': { rgb: '19, 78, 74',    hex: '#134e4a' },
                '950': { rgb: '4, 47, 46',     hex: '#042f2e' }
            },
            zinc: { // Tailwind Zinc
                '50':  { rgb: '250, 250, 250', hex: '#fafafa' }, '100': { rgb: '244, 244, 245', hex: '#f4f4f5' },
                '200': { rgb: '228, 228, 231', hex: '#e4e4e7' }, '300': { rgb: '212, 212, 216', hex: '#d4d4d8' },
                '400': { rgb: '161, 161, 170', hex: '#a1a1aa' }, '500': { rgb: '113, 113, 122', hex: '#71717a' },
                '600': { rgb: '82, 82, 91',    hex: '#52525b' }, '700': { rgb: '63, 63, 70',    hex: '#3f3f46' },
                '800': { rgb: '39, 39, 42',    hex: '#27272a' }, '900': { rgb: '24, 24, 27',    hex: '#18181b' },
                '950': { rgb: '9, 9, 11',      hex: '#09090b' }
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
                case 'heroic-fiery':   primaryPaletteKey = 'blue'; break;
                case 'mystic-verdant': primaryPaletteKey = 'teal'; break;
                case 'havelock-blue':
                default: primaryPaletteKey = 'havelockBlue'; break;
            }

            const selectedPrimaryPalette = this.palettes[primaryPaletteKey];
            const grayPalette = this.palettes.zinc; // Always use zinc for Filament's gray
            let cssOverrides = ':root {\\n';

            if (selectedPrimaryPalette) {
                for (const shade in selectedPrimaryPalette) {
                    cssOverrides += `    --primary-${shade}-rgb: ${selectedPrimaryPalette[shade].rgb};\\n`;
                    cssOverrides += `    --primary-${shade}: ${selectedPrimaryPalette[shade].hex};\\n`;
                }
            }
            if (grayPalette) {
                for (const shade in grayPalette) {
                    cssOverrides += `    --gray-${shade}-rgb: ${grayPalette[shade].rgb};\\n`;
                    cssOverrides += `    --gray-${shade}: ${grayPalette[shade].hex};\\n`;
                }
            }
            // Override danger, warning, success, info to be based on primary but with fixed hues
            // This is a simplification; ideally, these would be distinct palettes.
            // For now, let's use a few distinct Tailwind colors for these semantic meanings.
            // Example: Danger = Red, Success = Green, Warning = Amber, Info = Sky
            const dangerPalette = { // Tailwind Red
                '50': { rgb: '254, 242, 242', hex: '#fef2f2'}, '100': { rgb: '254, 226, 226', hex: '#fee2e2'},
                '200': { rgb: '254, 202, 202', hex: '#fecaca'}, '300': { rgb: '252, 165, 165', hex: '#fca5a5'},
                '400': { rgb: '248, 113, 113', hex: '#f87171'}, '500': { rgb: '239, 68, 68', hex: '#ef4444'},
                '600': { rgb: '220, 38, 38', hex: '#dc2626'}, '700': { rgb: '185, 28, 28', hex: '#b91c1c'},
                '800': { rgb: '153, 27, 27', hex: '#991b1b'}, '900': { rgb: '127, 29, 29', hex: '#7f1d1d'},
                '950': { rgb: '69, 10, 10', hex: '#450a0a'}
            };
            const warningPalette = { // Tailwind Amber
                '50': { rgb: '255, 251, 235', hex: '#fffbeb'}, '100': { rgb: '254, 243, 199', hex: '#fef3c7'},
                '200': { rgb: '253, 230, 138', hex: '#fde68a'}, '300': { rgb: '252, 211, 77', hex: '#fcd34d'},
                '400': { rgb: '251, 191, 36', hex: '#fbbd24'}, '500': { rgb: '245, 158, 11', hex: '#f59e0b'},
                '600': { rgb: '217, 119, 6', hex: '#d97706'}, '700': { rgb: '180, 83, 9', hex: '#b45309'},
                '800': { rgb: '146, 64, 14', hex: '#92400e'}, '900': { rgb: '120, 53, 15', hex: '#78350f'},
                '950': { rgb: '69, 26, 3', hex: '#451a03'}
            };
            const successPalette = { // Tailwind Green (distinct from Emerald)
                '50': { rgb: '240, 253, 244', hex: '#f0fdf4'}, '100': { rgb: '220, 252, 231', hex: '#dcfce7'},
                '200': { rgb: '187, 247, 208', hex: '#bbf7d0'}, '300': { rgb: '134, 239, 172', hex: '#86efac'},
                '400': { rgb: '74, 222, 128', hex: '#4ade80'}, '500': { rgb: '34, 197, 94', hex: '#22c55e'},
                '600': { rgb: '22, 163, 74', hex: '#16a34a'}, '700': { rgb: '21, 128, 61', hex: '#15803d'},
                '800': { rgb: '22, 101, 52', hex: '#166534'}, '900': { rgb: '20, 83, 45', hex: '#14532d'},
                '950': { rgb: '5, 46, 22', hex: '#052e16'}
            };
             const infoPalette = { // Tailwind Sky
                '50': { rgb: '240, 249, 255', hex: '#f0f9ff' }, '100': { rgb: '224, 242, 254', hex: '#e0f2fe' },
                '200': { rgb: '186, 230, 253', hex: '#bae6fd' }, '300': { rgb: '125, 211, 252', hex: '#7dd3fc' },
                '400': { rgb: '56, 189, 248', hex: '#38bdf8' }, '500': { rgb: '14, 165, 233', hex: '#0ea5e9' },
                '600': { rgb: '2, 132, 199', hex: '#0284c7' }, '700': { rgb: '3, 105, 161', hex: '#0369a1' },
                '800': { rgb: '7, 89, 133', hex: '#075985' }, '900': { rgb: '12, 74, 110', hex: '#0c4a6e' },
                '950': { rgb: '8, 51, 68', hex: '#083344' }
            };

            ['danger', 'warning', 'success', 'info'].forEach(type => {
                const palette = { danger: dangerPalette, warning: warningPalette, success: successPalette, info: infoPalette }[type];
                if (palette) {
                    for (const shade in palette) {
                        cssOverrides += `    --${type}-${shade}-rgb: ${palette[shade].rgb};\\n`;
                        cssOverrides += `    --${type}-${shade}: ${palette[shade].hex};\\n`;
                    }
                }
            });

            cssOverrides += '}';

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
