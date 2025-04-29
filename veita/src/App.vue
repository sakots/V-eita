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
        <itemSearch />
        <deleteForm />
      </section>
    </div>
  </main>
</template>

<script setup>

import headerItem from './components/headerItem.vue'
import pagingDefault from './components/pagingDefault.vue'
import viewDefault from './components/viewDefault.vue'
import itemSearch from './components/itemSearch.vue'
import deleteForm from './components/deleteForm.vue'

</script>
