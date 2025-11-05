/* eslint-env node */

/**
 * @type {import('@quasar/app-vite').QuasarConfig}
 */
module.exports = function (/* ctx */) {
  return {
    supportTS: false,
    boot: [],
    css: ['app.scss'],
    extras: ['roboto-font', 'material-icons'],
    build: {
      target: {
        browser: ['es2019', 'edge88', 'firefox78', 'chrome87', 'safari13.1'],
        node: 'node20',
      },
      vueRouterMode: 'hash',

      // Simple Vite plugin configuration
      vitePlugins: [
        [
          '@tailwindcss/vite',
          {
            config: './tailwind.config.js',
          },
        ],
      ],
    },
    devServer: {
      port: 3000,
      open: true,
    },
    framework: {
      config: {},
      plugins: [],
    },
    animations: [],
  }
}
