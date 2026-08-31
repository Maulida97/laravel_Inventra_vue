import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['"Plus Jakarta Sans"', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                bg: '#F5F6F8',
                surface: {
                    DEFAULT: '#FFFFFF',
                    alt: '#F0F2F5',
                },
                border: {
                    DEFAULT: '#E2E5EA',
                    strong: '#CBD1DA',
                },
                secondary: {
                    DEFAULT: '#14324F',
                    600: '#1B4066',
                    500: '#24537F',
                    100: '#E7EDF3',
                },
                accent: {
                    DEFAULT: '#2563EB',
                    hover: '#1D4ED8',
                    100: '#E8EFFE',
                },
                text: {
                    DEFAULT: '#16202B',
                    secondary: '#4A5567',
                    muted: '#7C8896',
                },
            },
        },
    },

    plugins: [forms],
};
