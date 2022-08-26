<!DOCTYPE html>
<?php
//設定の読み込み
require(__DIR__ . '/config.php');
require(__DIR__ . '/theme_conf.php');
?>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= TITLE ?></title>
  <script src="https://unpkg.com/vue@next"></script>
  <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
  <link rel="stylesheet" href="css/monored_index.min.css">
  <link rel="stylesheet" href="css/reita/mono.min.css">
  <link rel="stylesheet" href="css/red/mono.min.css" id="css1" disabled>
  <link rel="stylesheet" href="css/main/mono.min.css" id="css2" disabled>
  <link rel="stylesheet" href="css/dark/mono.min.css" id="css3" disabled>
  <link rel="stylesheet" href="css/deep/mono.min.css" id="css4" disabled>
  <link rel="stylesheet" href="css/mayo/mono.min.css" id="css5" disabled>
  <link rel="stylesheet" href="css/dev/mono.min.css" id="css6" disabled>
  <link rel="stylesheet" href="css/sql/mono.min.css" id="css7" disabled>
  <link rel="stylesheet" href="css/pop/mono.min.css" id="css8" disabled>
  <script src="js/switchcss.js"></script>
</head>

<body>
  <header id="header">
    <h1><a href="<?= PHP_SELF ?>"><?= TITLE ?></a></h1>
  </header>
  <main>
    <div id="app">
      <section class="thread" v-for="user in users">
        <h3 class="oyat">[{{ user.tid }}] {{ user.sub }}</h3>
        <section>
          <h4 class="oya">
            <span class="oyaname">
              <a href="<?= PHP_SELF ?>?mode=search&amp;bubun=kanzen&amp;search={{ user.a_name }}">
                {{ user.a_name }}</a>
            </span>
            <svg v-if="user.admins" viewBox="0 0 640 512">
              <use href="icons/user-check.svg#admin_badge">
            </svg>
            <span v-if="user.modified === user.created">
              {{ user.modified }}
            </span>
            <span v-else>
              {{ user.created }} <?= $updatemark ?> {{ user.modified }}
            </span>
            <span v-if="user.mail" class="mail">
              <a href="mailto:{{ user.mail }}">[mail]</a>
            </span>
            <span v-if="user.a_url" class="url">
              <a href=" user.a_url " target="_blank" rel="nofollow noopener noreferrer">[URL]</a>
            </span>
            <? if (DISP_ID) : ?>
              <span class="id">ID：{{ user.id }}</span>
            <? endif; ?>
            <span class="sodane">
              <a href="<?= PHP_SELF ?>?mode=sodane&amp;resto={{ user.tid }}"><?= SODANE ?>
                <span v-if="user.exid != 0">
                  x{{ user.exid }}
                </span>
                <span v-else>
                  +
                </span>
              </a>
            </span>
        </section>
      </section>
    </div>
    <script src="main.js"></script>
  </main>
  <footer>
    <div class="copy">
      <!-- 著作権表示 -->
      <p>
        <a href="https://oekakibbs.moe/" target="_blank">Veita v0.0.0</a>
      </p>
      <p>
        OekakiApplet -
        <a href="https://github.com/funige/neo/" target="_blank" rel="noopener noreferrer" title="by funige">PaintBBS NEO</a>
        <? if (USE_CHICKENPAINT) : ?>,<a href="https://github.com/thenickdude/chickenpaint" target="_blank" rel="nofollow noopener noreferrer" title="by Nicholas Sherlock">ChickenPaint</a> <? endif; ?>
      </p>
      <p>
        UseFunction -
        <!-- http://wondercatstudio.com/ -->DynamicPalette,
        <a href="https://huruihone.tumblr.com/" target="_blank" rel="noopener noreferrer" title="by Soto">AppletFit</a>,
        <a href="https://github.com/imgix/luminous" target="_blank" rel="noopener noreferrer" title="by imgix">Luminous</a>,
        <a href="https://v3.ja.vuejs.org/" target="_blank" rel="noopener noreferrer" title="by vuejs.org">Vue.js</a>
      </p>
    </div>
  </footer>
</body>

</html>