<template>
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
</template>
