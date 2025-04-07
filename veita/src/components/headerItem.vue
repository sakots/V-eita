<template>
  <h1 v-if="headers"><a href="./">{{ headers.b_title }}</a></h1>
  <div>
		<a v-if="headers" v-bind:href="headers.home" target="_top">[ホーム]</a>
		<a href="?mode=admin_in">[管理モード]</a>
	</div>
  <hr>
  <div>
    <section>
      <p class="top menu">
        <a href="?mode=catalog">[カタログ]</a>
        <a href="?mode=pic_tmp">[投稿途中の絵]</a>
        <a href="#footer">[↓]</a>
      </p>
    </section>
    <section>
      <p v-if="headers" class="system_msg">{{ headers.message }}</p>
    </section>
  </div>
	<hr>
  <div>
    <section class="e_post">
      <form v-if="headers" action="./" method="post" enctype="multipart/form-data">
        <p>
          <label v-if="headers">幅：<input class="form" type="number" min="300" v-bind:max="headers.p_max_w" name="pic_w" v-bind:value="headers.p_def_w" required></label>
          <label v-if="headers">高さ：<input class="form" type="number" min="300" v-bind:max="headers.p_max_h" name="pic_h" v-bind:value="headers.p_def_h" required></label>
          <input type="hidden" name="mode" value="paint">
          <label for="tools">ツール</label>
          <select name="tools" id="tools">
            <option value="neo">PaintBBS NEO</option>
            <option v-if="headers.use_chicken" value="chicken">ChickenPaint</option>
          </select>
          <label for="palettes">パレット</label>
          <select v-if="headers.select_palettes" name="palettes" id="palettes">
            <option v-for="(index) in headers.pallets_dat" v-bind:value="index[1]" v-bind:id="index" v-bind:key="index">{{ index[0] }}</option>
          </select>
          <select v-else name="palettes" id="palettes">
            <option value="neo" id="0">標準</option>
          </select>
          <label v-if="headers.use_anime">
            <input v-if="headers.def_anime" type="checkbox" value="true" name="anime" title="動画記録" checked>
            <input v-else type="checkbox" value="true" name="anime" title="動画記録" checked>
            アニメーション記録
          </label>
          <input class="button" type="submit" value="お絵かき">
        </p>
      </form>
      <ul v-if="headers">
        <li>iPadやスマートフォンでも描けるお絵かき掲示板です。</li>
        <li>お絵かきできるサイズは幅300～{{ headers.p_max_w }}px、高さ300～{{ headers.p_max_h }}pxです。</li>
        <li v-for="(index) in headers.add_info" v-bind:key="index" v-html="index"></li>
      </ul>
    </section>
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
