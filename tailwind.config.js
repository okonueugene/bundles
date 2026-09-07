import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                ok: {
                    DEFAULT: '#16A34A',
                    dark: '#15803D',
                    light: '#F0FDF4',
                },
                okoa: {
                    charcoal: '#121826',
                    bg: '#F8FAFC',
                    border: '#E2E8F0',
                    muted: '#64748B',
                },
            },
        },
    },
    plugins: [],
};
