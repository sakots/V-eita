<template>
  <header id="header">
    <headerItem />
    <hr />
    <pagingDefault />
  </header>
  <main>
    <viewDefault />
    <div>
      <section class="thread">
        <pagingDefault />
        <hr />
        <p>作者名/本文(ハッシュタグ)検索</p>
        <form class="search" method="GET" action="./">
          <input type="hidden" name="mode" value="search" />
          <label><input type="radio" name="division" value="division" />部分一致</label>
          <label><input type="radio" name="division" value="perfect" />完全一致</label>
          <label><input type="radio" name="tag" value="tag" />本文(ハッシュタグ)</label>
          <br />
          <input type="text" name="search" placeholder="検索" size="20" />
          <input type="submit" value=" 検索 " />
        </form>
        <form class="del_f" action="./" method="post">
          <p>
            No
            <input class="form" type="number" min="1" name="del_no" value="" autocomplete="off" required />
            Pass
            <input class="form" type="password" name="pwd" value="" autocomplete="current-password" />
            <select class="form" name="mode">
              <option value="edit">編集</option>
              <option value="del">削除</option>
            </select>
            <input class="button" type="submit" value=" OK " />
          </p>
        </form>
      </section>
    </div>
  </main>
</template>

<script setup>

import headerItem from './components/headerItem.vue'
import pagingDefault from './components/pagingDefault.vue'
import viewDefault from './components/viewDefault.vue'

</script>
