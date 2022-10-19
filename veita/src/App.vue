<template>
  <div id="app">
    <h3>掲示板に投稿する</h3>
    <label for="nickname">お名前：</label>
    <input id="nickname" type="text">
    <label for="mail">mail：</label>
    <input id="mail" type="text">
    <br>
    <label for="comment">コメント</label>
    <textarea name="" id="comment" cols="48" rows="8"></textarea>
    <br>
    <button @click="createComment"> 送信 </button>
    <h2>掲示板</h2>
  </div>
</template>

<script>

export default {
  data() {
    return {
      nickname: "",
      mailaddress: "",
      comment: ""
    }
  }
}
</script>

<style>
#app {
  font-family: Avenir, Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  text-align: center;
  color: #2c3e50;
  margin-top: 60px;
}
</style>
