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
    extend: {},
  },
  plugins: [],
}
