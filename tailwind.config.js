const defaultTheme = require('tailwindcss/defaultTheme')

module.exports = {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/laravel/jetstream/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },

            colors: {
                'status-active-bg': '#DCFCE7',
                'status-active-text': '#166534',

                'status-expired-bg': '#FEE2E2',
                'status-expired-text': '#991B1B',

                'status-warning-bg': '#FEF3C7',
                'status-warning-text': '#92400E',

                'logo-blue': '#1C214D',
                'bg-app': '#BDC1C8',
                'red-hki': '#CF1515',
            },
        },
    },

    plugins: [
        require('@tailwindcss/forms'),
        require('@tailwindcss/typography'),
    ],
};
