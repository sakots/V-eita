import axios from 'axios'

const axiosPlugin = {
  install(app, options) {
    axios.defaults.baseURL = options.baseURL;
    app.provide('axios', axios);
  },
};
export default axiosPlugin;
