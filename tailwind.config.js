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
                'reddish': '#CA7471',

            },
            backgroundImage: {
                'gradient-radial': 'radial-gradient(var(--tw-gradient-stops))',
            },
            letterSpacing: {
                "widest-xl": '.15em',
            },
            lineHeight: {
                '12': '68px',
            },
            spacing: {
                '128' : '32rem',
                '144': '36rem',
            },
            maxWidth: {
                '8xl': '88rem',
                '9xl': '96rem',
                '10xl': '104rem',
            }
        },
    },

    plugins: [forms, typography],
};
