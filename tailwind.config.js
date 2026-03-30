import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';
import preline from 'preline/plugin';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/laravel/jetstream/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './node_modules/preline/dist/*.js',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['"Plus Jakarta Sans"', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand: {
                    50: '#f0f7ff',
                    100: '#e0effe',
                    200: '#bae2fd',
                    300: '#7cc8fb',
                    400: '#38a9f8',
                    500: '#0e8ceb',
                    600: '#026ec7',
                    700: '#0358a1',
                    800: '#074b85',
                    900: '#0c3f6e',
                    950: '#082849',
                },
            },
            boxShadow: {
                'glow': '0 0 20px -5px rgba(14, 140, 235, 0.5)',
                'glow-lg': '0 0 30px -5px rgba(14, 140, 235, 0.6)',
                'glass': 'inset 0 0 0 1px rgba(255, 255, 255, 0.1)',
            },
            borderRadius: {
                '2xl': '1rem',
                '3xl': '1.5rem',
            }
        },
    },

    plugins: [forms, typography, preline],
};
