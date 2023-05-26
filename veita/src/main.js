import './assets/main.css'
import axios from './plugins/axios'

import { createApp } from 'vue'
import App from './App.vue'

const app = createApp(App);

app.use(axios, {
  baseURL: './',
});
app.mount('#app');
