<template>
  <section class="paging">
    <p v-if="threads">
      <span v-if="threads.back === 0" class="se">[START]</span>
      <span v-else class="se">&lt;&lt;<a v-bind:href="'./?page=' + threads.back">[BACK]</a></span>
      <span v-for="(paging, index) in threads.paging" v-bind:key="index">
        <span v-if="paging['p'] === threads.now_page"
          ><em class="this_page">[{{ paging['p'] }}]</em></span
        >
        <span v-else
          ><a v-bind:href="'./?page=' + paging['p']">[{{ paging['p'] }}]</a></span
        >
      </span>
      <span v-if="threads.next === threads.max_page + 1" class="se">[END]</span>
      <span v-else class="se"><a v-bind:href="'./?page=' + threads.next">[NEXT]</a>&gt;&gt;</span>
    </p>
  </section>
</template>

<script setup>
import axios from 'axios'
import { ref, onMounted } from 'vue'

const threads = ref()
const baseUrl = import.meta.env.VITE_BASE_URL
const getThreads = () => {
  const param = location.search
  axios.get(baseUrl + param).then((res) => {
    threads.value = res.data
  })
}
//DOM読み込み後に展開する
onMounted(async () => {
  getThreads()
})
</script>
