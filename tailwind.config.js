import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/laravel/jetstream/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    darkMode: 'class',

    theme: {
        extend: {
            fontFamily: {
                sans: ['Arial', ...defaultTheme.fontFamily.sans],

            },
            colors: {
                'mainWhite': '#FDFFFC',
                'darkBlue': '#1F2937',
                'turqoFocus': '#03DCCD',
            },
        },
    },

    plugins: [forms, typography],
};
