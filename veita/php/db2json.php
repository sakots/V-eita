<?php

require_once(__DIR__ . '/functions.php');

//タイムゾーン設定
date_default_timezone_set(DEFAULT_TIMEZONE);

//データベース接続PDO
define('DB_PDO', 'sqlite:' . DB_NAME . '.db');

$dsp_res = DSP_RES;
$page_def = PAGE_DEF;

//スレ数カウント
try {
  $db = new PDO(DB_PDO);
  $sqlth = "SELECT SUM(thread) as cnt FROM tlog";
  $th_cnt_sql = $db->query("$sqlth");
  $th_cnt_sql = $th_cnt_sql->fetch();
  $th_cnt = $th_cnt_sql["cnt"];
} catch (PDOException $e) {
  echo "DB接続エラー:" . $e->getMessage();
}
if ($th_cnt > LOG_MAX_T) {
  logdel();
}

//古いスレのレスボタンを表示しない
$elapsed_time = ELAPSED_DAYS * 86400; //デフォルトの1年だと31536000
$nowtime = time(); //いまのunixタイムスタンプを取得
//あとはテーマ側で計算する
$dat['nowtime'] = $nowtime;
$dat['elapsed_time'] = $elapsed_time;

//ページング
try {
  $db = new PDO(DB_PDO);
  $sqlcnt = "SELECT SUM(thread) as cnt FROM tlog WHERE invz=0";
  $th_cnt_sql = $db->query("$sqlcnt");
  $th_cnt_sql = $th_cnt_sql->fetch();
  $count = $th_cnt_sql["cnt"];
  if (isset($_GET['page']) && is_numeric($_GET['page'])) {
    $page = $_GET['page'];
    $page = max($page, 1);
  } else {
    $page = 1;
  }
  $start = $page_def * ($page - 1);

  //最大何ページあるのか
  $max_page = floor($count / $page_def) + 1;
  //最後にスレ数0のページができたら表示しない処理
  if (($count % $page_def) == 0) {
    $max_page = $max_page - 1;
    //ただしそれが1ページ目なら困るから表示
    $max_page = max($max_page, 1);
  }
  $dat['max_page'] = $max_page;

  //リンク作成用
  $dat['nowpage'] = $page;
  $p = 1;
  $pp = array();
  $paging = array();
  while ($p <= $max_page) {
    $paging[($p)] = compact('p');
    $pp[] = $paging;
    $p++;
  }
  $dat['paging'] = $paging;
  $dat['pp'] = $pp;

  $dat['back'] = ($page - 1);
  $dat['next'] = ($page + 1);

  $db = null; //db切断
} catch (PDOException $e) {
  echo "DB接続エラー:" . $e->getMessage();
}
//読み込み

try {
  $db = new PDO(DB_PDO);
  //1ページの全スレッド取得
  $sql = "SELECT * FROM tlog WHERE invz=0 AND thread=1 ORDER BY tree DESC LIMIT ?, ?";
  $posts = $db->prepare($sql);
  $posts->bindValue(1, $start, PDO::PARAM_INT);
  $posts->bindValue(2, $page_def, PDO::PARAM_INT);
  $posts->execute();

  $ko = array();
  $oya = array();

  $i = 0;
  $j = 0;
  while ($i < PAGE_DEF) {
    $bbsline = $posts->fetch();
    if (empty($bbsline)) {
      break;
    } //スレがなくなったら抜ける
    $oid = $bbsline["tid"]; //スレのtid(親番号)を取得
    $sqli = "SELECT * FROM tlog WHERE parent = $oid AND invz=0 AND thread=0 ORDER BY comid ASC";
    //レス取得
    $postsi = $db->query($sqli);
    $j = 0;
    $flag = true;
    while ($flag == true) {
      $_pchext = pathinfo($bbsline['pchfile'], PATHINFO_EXTENSION);
      if ($_pchext === 'chi') {
        $bbsline['pchfile'] = ''; //ChickenPaintは動画リンクを出さない
      }
      $rep = $postsi->fetch();
      if (empty($rep)) { //レスがなくなったら
        $bbsline['ressu'] = $j; //スレのレス数
        $bbsline['res_d_su'] = $j - DSP_RES; //スレのレス省略数
        if ($j > DSP_RES) { //スレのレス数が規定より多いと
          $bbsline['rflag'] = true; //省略フラグtrue
        } else {
          $bbsline['rflag'] = false; //省略フラグfalse
        }
        $flag = false;
        break;
      } //抜ける
      $rep['resno'] = ($j + 1); //レス番号
      // http、https以外のURLの場合表示しない
      if (!filter_var($rep['a_url'], FILTER_VALIDATE_URL) || !preg_match('|^https?://.*$|', $rep['a_url'])) {
        $rep['a_url'] = "";
      }
      $rep['com'] = htmlspecialchars($rep['com'], ENT_QUOTES | ENT_HTML5);

      //オートリンク
      if (AUTOLINK) {
        $rep['com'] = auto_link($rep['com']);
      }
      //ハッシュタグ
      if (USE_HASHTAG) {
        $rep['com'] = hashtag_link($rep['com']);
      }
      //空行を縮める
      $rep['com'] = preg_replace('/(\n|\r|\r\n|\n\r){3,}/us', "\n\n", $rep['com']);
      //<br>に
      $rep['com'] = tobr($rep['com']);
      //引用の色
      $rep['com'] = quote($rep['com']);
      //日付をUNIX時間に変換して設定どおりにフォーマット
      $rep['created'] = date(DATE_FORMAT, strtotime($rep['created']));
      $rep['modified'] = date(DATE_FORMAT, strtotime($rep['modified']));
      $ko[] = $rep;
      //echo json_encode($ko[$j], JSON_UNESCAPED_UNICODE);
      $j++;
    }
    // http、https以外のURLの場合表示しない
    if (!filter_var($bbsline['a_url'], FILTER_VALIDATE_URL) || !preg_match('|^https?://.*$|', $bbsline['a_url'])) {
      $bbsline['a_url'] = "";
    }
    $bbsline['com'] = htmlspecialchars($bbsline['com'], ENT_QUOTES | ENT_HTML5);

    //オートリンク
    if (AUTOLINK) {
      $bbsline['com'] = auto_link($bbsline['com']);
    }
    //ハッシュタグ
    if (USE_HASHTAG) {
      $bbsline['com'] = hashtag_link($bbsline['com']);
    }
    //空行を縮める
    $bbsline['com'] = preg_replace('/(\n|\r|\r\n){3,}/us', "\n\n", $bbsline['com']);
    //<br>に
    $bbsline['com'] = tobr($bbsline['com']);
    //引用の色
    $bbsline['com'] = quote($bbsline['com']);
    //日付をUNIX時間にしたあと整形
    $bbsline['past'] = strtotime($bbsline['created']); // このスレは古いので用
    $bbsline['created'] = date(DATE_FORMAT, strtotime($bbsline['created']));
    $bbsline['modified'] = date(DATE_FORMAT, strtotime($bbsline['modified']));
    $oya[] = $bbsline;
    $i++;
  }

  $dat['ko'] = $ko;
  $dat['oya'] = $oya;
  $dat['dsp_res'] = DSP_RES;
  $dat['path'] = IMG_DIR;

  // jsonに変換
  $oya_json = json_encode($oya, JSON_UNESCAPED_UNICODE);

  // jsonがなければテキスト書き込み
  if (!file_exists('oya.json')) {
    file_put_contents('oya.json', $oya_json, FILE_APPEND | LOCK_EX);
  }

  $db = null; //db切断
} catch (PDOException $e) {
  echo "DB接続エラー:" . $e->getMessage();
}


