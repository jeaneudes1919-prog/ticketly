import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './app/**/*.php',
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['"Inter Variable"', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                display: ['"Space Grotesk Variable"', 'ui-sans-serif', 'sans-serif'],
            },
        },
    },
    plugins: [forms({ strategy: 'class' })],
};
