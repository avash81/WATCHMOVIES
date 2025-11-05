// movie-client/tailwind.config.js
/** @type {import('tailwindcss').Config} */
module.exports = {
  // IMPORTANT: The content array tells Tailwind which files to scan for class names
  content: [
    './index.html',
    './src/**/*.{vue,js,ts,jsx,tsx}',
    './src/pages/**/*.vue',
    './src/layouts/**/*.vue',
    './src/components/**/*.vue',
  ],
  theme: {
    extend: {
      colors: {
        primary: '#e50914',
        secondary: '#1a1a1a',
        accent: '#ff6b00',
        dark: {
          900: '#0f0f0f',
          800: '#1a1a1a',
          700: '#262626',
          600: '#333333',
        },
      },
      backgroundImage: {
        'gradient-primary': 'linear-gradient(135deg, #e50914 0%, #ff6b00 100%)',
      },
    },
  },
  plugins: [],
}
