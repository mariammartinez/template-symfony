import { createApp } from 'vue'
import App from './App.vue'
import { library } from '@fortawesome/fontawesome-svg-core'
import { faHandSparkles, faMagic, faUserCheck, faUserTimes } from '@fortawesome/free-solid-svg-icons'
import { faEdit, faTrashAlt } from '@fortawesome/free-regular-svg-icons'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import './registerServiceWorker'
import router from './router'
import store from './store'

import apiRegistry from './helpers/apiRegistry'

import './assets/css/app.scss'
// import { Tooltip, Toast, Popover } from 'bootstrap'

// todo variable d'environnement
const BASE_URL = 'http://localhost:5000'
apiRegistry.set('default', BASE_URL)

library.add(faHandSparkles, faMagic, faUserCheck, faUserTimes, faTrashAlt, faEdit)

createApp(App)
  .component('font-aw', FontAwesomeIcon)
  .use(store)
  .use(router)
  .mount('#app')
