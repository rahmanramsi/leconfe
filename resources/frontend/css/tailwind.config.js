const colors = require('tailwindcss/colors')

import defaultTheme from 'tailwindcss/defaultTheme'

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/views/frontend/**/*.blade.php',
        './resources/views/errors/**/*.blade.php',
        './stubs/plugins/**/*.blade.php',
        './plugins/**/*.blade.php',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['var(--font-family)', ...defaultTheme.fontFamily.sans],
            },
            fontSize : {
                '2xs' : '.65rem',
            },
            typography: (theme) => ({
                DEFAULT: {
                  css: {
                    a: {
                        'text-decoration': 'none',
                    },
                  },
                },
              }),
        },
    },
    darkMode: 'class',
    plugins: [
        require('daisyui'),
        require('@tailwindcss/typography'),
        require("tailwindcss-animate"),
    ],
    daisyui: {
        themes: [
            {
                light: {
                    ...require("daisyui/src/theming/themes")["winter"],
                    primary: '#1c3569',
                    'primary-content': '#ffffff',
                    warning: '#FACC15',
                    error: '#ff5861',
                    success: '#4ADE80',
                    'base-100': '#F1F6FA',
                    'base-content' : '#000000',
                    '--rounded-box': '0px',
                    '--rounded-btn': '0.25rem',
                    '--btn-text-case': 'none',
                },
            },
        ],
    },
}
