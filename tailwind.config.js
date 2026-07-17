import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            /*
            |------------------------------------------------------------------
            | Paleta de marca — derivada de public/img/logo.png (Balcoa's Café)
            |------------------------------------------------------------------
            | cocoa   → marrón chocolate del listón/borde del logo (~#6B3D2E)
            | caramel → naranja caramelo del pastel/aro (~#E39F4E)
            | cream   → fondo crema del logo (~#F7E7C3)
            | Uso semántico documentado en GuiaEstilo.md (raíz del repo).
            */
            colors: {
                cocoa: {
                    50:  '#f9f5f1',
                    100: '#f0e6dc',
                    200: '#e0cbb9',
                    300: '#cdaa8f',
                    400: '#b98865',
                    500: '#aa7048',
                    600: '#9c5e3d',
                    700: '#824a34',
                    800: '#6b3d2e',
                    900: '#573328',
                    950: '#2f1a14',
                },
                caramel: {
                    50:  '#fcf6ed',
                    100: '#f8ead5',
                    200: '#f1d5ab',
                    300: '#e9bc7c',
                    400: '#e39f4e',
                    500: '#dc8630',
                    600: '#ce6d25',
                    700: '#ab5420',
                    800: '#894421',
                    900: '#6f391e',
                    950: '#3c1d0e',
                },
                cream: {
                    50:  '#fdfbf6',
                    100: '#faf3e4',
                    200: '#f5e7cb',
                    300: '#efd9ac',
                },
            },
            fontFamily: {
                // Cuerpo: Figtree (limpia, legible). Titulares: Fraunces (serif
                // cálida, estética de café/pastelería). Ambas self-hosted vía
                // @fontsource (sin CDN externo — decisión SRI del informe Sonar).
                sans: ['Figtree Variable', ...defaultTheme.fontFamily.sans],
                display: ['Fraunces Variable', 'Georgia', ...defaultTheme.fontFamily.serif],
            },
        },
    },

    plugins: [forms],
};
