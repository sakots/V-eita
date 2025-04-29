<template>
  <div v-if="headers" class="copy">
    <!-- 著作権表示 -->
    <p>
      <a href="https://dev.oekakibbs.net/" target="_blank">V-eita {{ headers.ver }}</a>
    </p>
    <p>
      OekakiApplet -
      <a href="https://github.com/funige/neo/" target="_blank" rel="noopener noreferrer" title="by funige">PaintBBS NEO</a>
      <span v-if="headers.use_chicken === 1"> ,<a href="https://github.com/satopian/ChickenPaint_Be" target="_blank" rel="nofollow noopener noreferrer" title="by Nicholas Sherlock">ChickenPaint Be</a></span>
    </p>
    <p>
      UseFunction -
      <!-- http://wondercatstudio.com/ -->DynamicPalette,
      <a href="https://huruihone.tumblr.com/" target="_blank" rel="noopener noreferrer" title="by Soto">AppletFit</a>
    </p>
  </div>
</template>

<script setup>
import axios from 'axios'
import { ref, onMounted } from 'vue';

const headers = ref()
const targetUrl = import.meta.env.VITE_BASE_URL + '?mode=get_header'
const getHeaders = () => {
  axios.get(targetUrl).then((res) => {
    headers.value = res.data
  })
}
//DOM読み込み後に展開する
onMounted(async () => {
  getHeaders()
})
</script>
