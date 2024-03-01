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
                sans: ['Inter var', ...defaultTheme.fontFamily.sans],
                arial: ['arial', 'sans'],


            },
            colors: {
                'mainWhite': '#FDFFFC',
                'darkBlue': '#1F2937',
                'bluee': '#113946',
                'turqoFocus': '#03DCCD',
                'turqo': '#04F1E1',

            },
            letterSpacing: {
                "widest-xl": '.15em',
            },
            lineHeight: {
                '12': '68px',
            }
        },
    },

    plugins: [forms, typography],
};
