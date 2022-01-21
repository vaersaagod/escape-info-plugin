module.exports = {
  content: ["./src/templates/_components/playground/**/*.{html,twig}", "./src/web/assets/src/playground/**/*.js"],
  prefix: 'tw-',
  theme: {
    colors: {
      transparent: 'transparent',
      current: 'currentColor',
      gray: {
        default: '#999999',
        light: '#F0F4F6'
      },
      white: '#ffffff',
      black: '#000000',
      primary: 'var(--playground-color-primary, black)',
      secondary: 'var(--playground-color-secondary, black)',
      accent: 'var(--playground-color-accent, black)'
    },
    extend: {
      height: {
        screen: '100vh'
      }
    },
  },
  plugins: [],
  corePlugins: {
    preflight: false,
  }
}
