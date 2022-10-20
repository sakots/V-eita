<?php
  require(__DIR__ . '/veita.php');
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= TITLE ?></title>
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
		<div>
			<a href="<?= HOME ?>" target="_top">[ホーム]</a>
			<a href="<?= PHP_SELF ?>?mode=admin_in">[管理モード]</a>
		</div>
		<hr>
		<div>
			<section>
				<p class="top menu">
					<a href="<?= PHP_SELF ?>?mode=catalog">[カタログ]</a>
					<a href="<?= PHP_SELF ?>?mode=pictmp">[投稿途中の絵]</a>
					<a href="#footer">[↓]</a>
				</p>
			</section>
		</div>
		<hr>
		<div>
			<section class="epost">
				<form action="<?= PHP_SELF ?>" method="post" enctype="multipart/form-data">
					<p>
						<label>幅：<input class="form" type="number" min="300" max="<?= PMAX_W ?>" name="picw" value="<?= PDEF_W ?>" required></label>
						<label>高さ：<input class="form" type="number" min="300" max="<?= PMAX_H ?>" name="pich" value="<?= PDEF_H ?>" required></label>
						<input type="hidden" name="mode" value="paint">
						<label for="tools">ツール</label>
						<select name="tools" id="tools">
							<option value="neo">PaintBBS NEO</option>
              <? if($use_nise_shipe_neo): ?><option value="sneo">偽しぃペNEO</option><? endif; ?>
              <? if($use_shi_p): ?><option value="shi">しぃペインター</option><? endif; ?>
              <? if($use_chicken): ?><option value="chicken">ChickenPaint</option><? endif; ?>
						</select>
						<label for="palettes">パレット</label>
            <? if($select_palettes): ?>
						<select name="palettes" id="palettes">
              <? foreach($pallets_dat as $palette): ?>
							<option value="<?= $palette[1] ?>" id="<?= $palette ?>"><?= $palette[0] ?></option>
							<? endforeach; ?>
						</select>
						<? else: ?>
						<select name="palettes" id="palettes">
							<option value="neo" id="0">標準</option>
						</select>
						<? endif; ?>
            <? if($useanime): ?>
						<label><input type="checkbox" value="true" name="anime" title="動画記録" <? if($defanime): ?> checked <? endif; ?>>アニメーション記録</label>
            <? endif; ?>
						<input class="button" type="submit" value="お絵かき">
					</p>
				</form>
				<ul>
					<li>iPadやスマートフォンでも描けるお絵かき掲示板です。</li>
					<li>お絵かきできるサイズは幅300～<?= PMAX_W ?>px、高さ300～<?= PMAX_H ?>pxです。</li>
          <? foreach($addinfo as $info): ?><? if(!empty($info)): ?>
          <li><?= $info ?></li>
          <? endif; ?><? endforeach; ?>
				</ul>
			</section>
			<hr>
			<section class="paging">
				<p>
          <? if($back === 0): ?>
					<span class="se">[START]</span>
					<? else: ?>
					<span class="se">&lt;&lt;<a href="<?= PHP_SELF ?>?page=<? $back ?>">[BACK]</a></span>
					<? endif; ?>
          <? foreach($paging as $pp): ?>
          <? if($pp['p'] == $nowpage): ?>
					<em class="thispage">[<? $pp['p'] ?>]</em>
					<? else: ?>
					<a href="<?= PHP_SELF ?>?page=<? $pp['p'] ?>">[<? $pp['p'] ?>]</a>
					<? endif; ?>
					<? endforeach; ?>
          <? if($next === ($max_page + 1)): ?>
					<span class="se">[END]</span>
					<? else: ?>
					<span class="se"><a href="<?= PHP_SELF ?>?page=<? $next ?>">[NEXT]</a>&gt;&gt;</span>
					<? endif; ?>
				</p>
			</section>
		</div>
	</header>

  <script src="https://unpkg.com/vue@next"></script>
  <script src="https://unpkg.com/axios/dist/axios.min.js"></script>
  <script>
    const { createApp, ref, onMounted } = Vue;
    createApp({
      setup() {
      const message = ref('Hello Axios');
      onMounted(() => {
        axios
          .get('https://jsonplaceholder.typicode.com/users')
          .then((response) => console.log(response))
          .catch((error) => console.log(error));
        });
        return {
          message,
        };
      },
    }).mount('#app');
  </script>
</body>
</html>