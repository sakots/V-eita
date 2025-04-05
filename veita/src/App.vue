<template>
  <header id="header">
    <headerItem />
  </header>
  <main>
    <div class="thread" v-for="thread in threads" :key="thread.id">
      <h2>{{ thread.title }}</h2>
      <p>{{ thread.content }}</p>
      <p>投稿日: {{ thread.created_at }}</p>
      <p>スレッドID: {{ thread.id }}</p>
    </div>
  </main>
</template>

<script setup>
import axios from 'axios'
import { ref, onMounted } from 'vue'

import headerItem from './components/headerItem.vue'

const threads = ref()
const getThreads = () => {
  axios.get(import.meta.env.VITE_BASE_URL).then((res) => {
    threads.value = res.data
  })
}
//DOM読み込み後に展開する
onMounted(async () => {
  getThreads()
})
</script>
