<template>
  <div v-if="threads">
    <section class="thread" v-for="(thread, index) in threads.oya" v-bind:key="index">
      <h3 class="oya_t">[{{ thread['tid'] }}] {{ thread['sub'] }}</h3>
      <section>
        <h4 class="oya">
          <span class="oya_name"
            ><a
              v-bind:href="
                baseUrl + './?mode=search&amp;division=perf&amp;search=' + thread['a_name']
              "
              >{{ thread['a_name'] }}</a
            ></span
          >
          <svg v-if="thread['admins'] === 1" viewBox="0 0 640 512">
            <use href="./icons/user-check.svg#admin_badge" />
          </svg>
          <span v-if="thread['modified'] === thread['created']">{{ thread['modified'] }}</span>
          <span v-else
            >{{ thread['created'] }} {{ thread.update_mark }} {{ thread['modified'] }}</span
          >
          <span v-if="thread['mail']" class="mail">
            <a v-bind:href="'mailto:' + thread['mail']">[mail]</a></span
          >
          <span v-if="thread['a_url']" class="url">
            <a v-bind:href="thread['a_url']" target="_blank" rel="nofollow noopener noreferrer"
              >[URL]</a
            ></span
          >
          <span v-if="threads.display_id" class="id"> ID：{{ thread['id'] }} </span>
          <span class="sodane">
            <a v-bind:href="'./?mode=sodane&amp;res_to=' + thread['tid']"
              >{{ threads.sodane }}
              <span v-if="thread['exid'] != 0">x{{ thread['exid'] }}</span>
              <span v-else>+</span>
            </a></span
          >
        </h4>
        <div v-if="thread['picfile']">
          <h5 v-if="threads.dp_time">
            {{ thread['tool'] }} ({{ thread['img_w'] }}x{{ thread['img_h'] }})
            <span v-if="thread['utime'] !== null">描画時間：{{ thread['utime'] }}</span>
            <span v-if="thread['ext01'] === 1">★NSFW</span>
          </h5>
          <h5>
            <a target="_blank" v-bind:href="baseUrl + threads.path + thread['picfile']">{{
              thread['picfile']
            }}</a>
            <a
              v-if="thread['pchfile'] && thread['tool'] !== 'Chicken Paint'"
              v-bind:href="baseUrl + './?mode=anime&amp;pch=' + thread['pchfile']"
              target="_blank"
            >
              ●動画</a
            >
            <a
              v-if="threads.use_continue"
              v-bind:href="'./?mode=continue&amp;no=' + thread['picfile']"
            >
              ●続きを描く</a
            >
          </h5>
          <a
            v-if="thread['ext01'] === 1"
            class="luminous"
            v-bind:href="baseUrl + threads.path + thread['picfile']"
            ><span class="nsfw"
              ><img
                v-bind:src="baseUrl + threads.path + thread['picfile']"
                v-bind:alt="thread['picfile']"
                loading="lazy"
                class="image" /></span
          ></a>
          <a
            v-else
            class="luminous"
            v-bind:href="baseUrl + threads.path + thread['picfile']"
            target="_blank"
            ><img
              v-bind:src="baseUrl + threads.path + thread['picfile']"
              v-bind:alt="thread['picfile']"
              loading="lazy"
              class="image"
          /></a>
          <p class="comment oya" v-html="thread['com']"></p>
          <div v-if="thread['r_flag']" class="res">
            <p class="limit">
              レス{{ thread['res_d_su'] }}件省略。すべて見るには
              <a v-bind:href="'./?mode=res&amp;res=' + thread['tid']">
                <span
                  v-if="
                    threads.elapsed_time === 0 ||
                    threads.now_time - thread['past'] < threads.elapsed_time
                  "
                  >返信</span
                >
                <span v-else>すべて見る</span></a
              >
              を押してください。
            </p>
          </div>
          <div v-if="thread.res">
            <div v-for="(re, index) in thread.res" v-bind:key="index">
              <section class="res">
                <section>
                  <h3>[{{ re['tid'] }}] {{ re['sub'] }}</h3>
                  <h4>
                    名前：<span class="res_name"
                      >{{ re['a_name'] }}
                      <svg v-if="re['admin'] === 1" viewBox="0 0 640 512">
                        <use href="./icons/user-check.svg#admin_badge" />
                      </svg>
                    </span>
                    <span v-if="re['modified'] === re['created']">{{ re['modified'] }}</span>
                    <span v-else
                      >{{ re['created'] }} {{ threads.update_mark }} {{ re['modified'] }}</span
                    >
                    <span v-if="re['mail']" class="mail"
                      ><a v-bind:href="'mailto:' + re['mail']"> [mail]</a></span
                    >
                    <span v-if="re['a_url']" class="url"
                      ><a
                        v-bind:href="re['a_url']"
                        target="_blank"
                        rel="nofollow noopener noreferrer"
                      >
                        [URL]</a
                      ></span
                    >
                    <span v-if="threads.display_id" class="id"> ID：{{ re['id'] }}</span>
                    <span class="sodane">
                      <a v-bind:href="'./?mode=sodane&amp;res_to=' + re['tid']"
                        >{{ re.sodane }}
                        <span v-if="re['exid'] != 0">x{{ re['exid'] }}</span>
                        <span v-else>+</span>
                      </a></span
                    >
                  </h4>
                  <p class="comment" v-html="re['com']"></p>
                </section>
              </section>
            </div>
          </div>
          <div class="th_foot">
            <span v-if="threads.share_button" class="button"
              ><a
                v-bind:href="
                  'https://x.com/intent/tweet?&amp;text=%5B' +
                  thread['tid'] +
                  '%5D%20' +
                  thread['sub'] +
                  '%20by%20' +
                  thread['a_name'] +
                  '%20-%20' +
                  threads.b_title +
                  '&amp;url=' +
                  threads.base +
                  './?mode=res%26res=' +
                  thread['tid']
                "
                target="_blank"
                ><svg viewBox="0 0 512 512"><use href="./icons/twitter.svg#twitter" /></svg>
                tweet</a
              ></span
            >
            <span v-if="threads.share_button" class="button"
              ><a
                v-bind:href="
                  'http://www.facebook.com/share.php?u=' +
                  threads.base +
                  './?mode=res%26res=' +
                  thread['tid']
                "
                class="fb btn"
                target="_blank"
                ><svg viewBox="0 0 512 512"><use href="./icons/facebook.svg#facebook" /></svg>
                share</a
              ></span
            >

            <span
              v-if="
                threads.elapsed_time === 0 ||
                threads.now_time - thread['past'] < threads.elapsed_time
              "
              ><span class="button"
                ><a v-bind:href="baseUrl + './?mode=res&amp;res=' + thread['tid']"
                  ><svg viewBox="0 0 512 512"><use href="../icons/rep.svg#rep" /></svg> 返信</a
                ></span
              ></span
            >
            <span v-else>このスレは古いので返信できません…</span>
            <a href="#header">[↑]</a>
            <hr />
          </div>
        </div>
      </section>
    </section>
  </div>
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
