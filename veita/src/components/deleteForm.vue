<template>
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
</template>
