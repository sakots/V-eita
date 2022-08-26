Vue.createApp({
  el: '#app',
  data(){
    return {
      users: [],
    }
},

  mounted(){
    axios.get('veita.php')
    .then(response => this.users = response.data)
    .catch(error => console.log(error))
    console.log(response.data)
  }
}).mount('#app')

