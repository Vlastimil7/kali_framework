/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ["./src/**/*.{html,js,php}", "./public/**/*.{html,js,php}"],
  theme: {
    extend: {
      colors: {
        gold: "var(--color-gold)",
        black: "var(--color-black)",
        white: "var(--color-white)",
        indigo: "var(--color-indigo)",
        "gray-light": "var(--color-gray-light)",
        "gray-medium": "var(--color-gray-medium)",
      },
    },
  },
  plugins: [],
};
