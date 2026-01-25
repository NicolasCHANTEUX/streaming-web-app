/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./src/Views/**/*.php",
    "./public/**/*.html",
    "./public/assets/js/**/*.js",
  ],
  theme: {
    extend: {
      colors: {
        'bg-main': '#121212',
        'bg-surface': '#1E1E1E',
        'bg-hover': '#282828',
        'primary': '#1DB954',
        'primary-hover': '#1ed760',
        'text-main': '#FFFFFF',
        'text-sub': '#B3B3B3',
        'border-main': '#333333',
      },
      spacing: {
        'player': '90px',
        'nav': '60px',
        'header': '60px',
      }
    },
  },
  plugins: [],
}
