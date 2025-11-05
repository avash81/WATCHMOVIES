import { createApp } from 'vue'
import { Quasar } from 'quasar'
import quasarLang from 'quasar/lang/en-US'
import quasarIconSet from 'quasar/icon-set/material-icons'

// Import Quasar css
import 'quasar/src/css/index.sass'

// Import Tailwind CSS
import './css/app.scss'

// Import icon libraries
import '@quasar/extras/material-icons/material-icons.css'
import '@quasar/extras/fontawesome-v6/fontawesome-v6.css'

// Import App and routes
import App from './App.vue'
import router from './router'

const myApp = createApp(App)

myApp.use(Quasar, {
  plugins: {}, // import Quasar plugins and add here
  lang: quasarLang,
  iconSet: quasarIconSet,
})

// Import and use router
myApp.use(router)

// Assumes you have a <div id="app"></div> in your index.html
myApp.mount('#q-app')
