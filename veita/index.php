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
		<h1><a href="./"><?= TITLE ?></a></h1>
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
              <?php if(USE_NISE_SHIPE_NEO): ?><option value="sneo">偽しぃペNEO</option><?php endif; ?>
              <?php if(USE_CHICKENPAINT): ?><option value="chicken">ChickenPaint</option><?php endif; ?>
						</select>
						<label for="palettes">パレット</label>
            <?php if(USE_SELECT_PALETTES): ?>
						<select name="palettes" id="palettes">
              <?php var_dump($pallets_dat); ?>
              <?php foreach($pallets_dat as &$palette): ?>
							<option value="<?= $palette[1] ?>" id="<?= $palette ?>"><?= $palette[0] ?></option>
							<?php endforeach; ?>
						</select>
						<?php else: ?>
						<select name="palettes" id="palettes">
							<option value="neo" id="0">標準</option>
						</select>
						<?php endif; ?>
            <?php if(USE_ANIME): ?>
						<label><input type="checkbox" value="true" name="anime" title="動画記録" <?php if(DEF_ANIME): ?> checked <?php endif; ?>>アニメーション記録</label>
            <?php endif; ?>
						<input class="button" type="submit" value="お絵かき">
					</p>
				</form>
				<ul>
					<li>iPadやスマートフォンでも描けるお絵かき掲示板です。</li>
					<li>お絵かきできるサイズは幅300～<?= PMAX_W ?>px、高さ300～<?= PMAX_H ?>pxです。</li>
          <?php foreach($addinfo as &$info): ?><?php if(!empty($info)): ?>
          <li><?= $info ?></li>
          <?php endif; ?><?php endforeach; ?>
				</ul>
			</section>
			<hr>
			<section class="paging">
				<p>
          <?php if($dat['back'] === 0): ?>
					<span class="se">[START]</span>
					<?php else: ?>
					<span class="se">&lt;&lt;<a href="./?page=<?= $dat['back'] ?>">[BACK]</a></span>
					<?php endif; ?>
          <?php foreach($dat['paging'] as $pp): ?>
          <?php if($pp['p'] == $dat['nowpage']): ?>
					<em class="thispage">[<?= $pp['p'] ?>]</em>
					<?php else: ?>
					<a href="./?page=<?= $pp['p'] ?>">[<?= $pp['p'] ?>]</a>
					<?php endif; ?>
					<?php endforeach; ?>
          <?php if($dat['next'] === ($dat['max_page'] + 1)): ?>
					<span class="se">[END]</span>
					<?php else: ?>
					<span class="se"><a href="./?page=<?= $dat['next'] ?>">[NEXT]</a>&gt;&gt;</span>
					<?php endif; ?>
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