module.exports = {
  content: ["./src/templates/**/*.{html,twig}", "./src/web/assets/src/**/*.js"],
  theme: {
    colors: {
      transparent: 'transparent',
      current: 'currentColor',
      gray: '#999999',
      white: '#ffffff',
      black: '#000000'
    },
    extend: {},
  },
  plugins: [],
  corePlugins: {
    preflight: false,
  }
}
