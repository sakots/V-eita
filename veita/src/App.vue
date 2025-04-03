<template>
  <header>
    <h1>{{ boardName }}</h1>
    <hr />
  </header>
  <main></main>
</template>

<script setup>
import axios from 'axios'
import { ref, onMounted } from 'vue'

const boardName = ref(import.meta.env.VITE_BOARD_NAME)

const threads = ref()
const getThreads = () => {
  axios.get(import.meta.env.VITE_BASE_URL).then((res) => {
    threads.value = res.data
  })
}
//DOM読み込み後に展開する
onMounted(async () => {
  const logs = await getThreads()
  console.log(logs) //値が展開されている
})
</script>
