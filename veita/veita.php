<?php
//--------------------------------------------------
//  おえかきけいじばん「Veita」
//  by sakots & OekakiBBS reDev.Team  https://oekakibbs.moe/
//--------------------------------------------------

//スクリプトのバージョン
define('VEITA_VER', 'v0.0.0'); //lot.220826.0

//設定の読み込み
require(__DIR__ . '/config.php');
require(__DIR__ . '/theme_conf.php');

//タイムゾーン設定
date_default_timezone_set(DEFAULT_TIMEZONE);

//データベース接続PDO
define('DB_PDO', 'sqlite:' . DB_NAME . '.db');

//ペイント画面の$pwdの暗号化
define('CRYPT_METHOD', 'aes-128-cbc');
define('CRYPT_IV', 'T3pkYxNyjN7Wz3pu'); //半角英数16文字

//初期設定(初期設定後は不要なので削除可)
//init();

//deltemp();

$message = "";

/*-----------mode-------------*/

$mode = filter_input(INPUT_POST, 'mode');
if (filter_input(INPUT_GET, 'mode') === "anime") {
  $pch = filter_input(INPUT_GET, 'pch');
  $mode = "anime";
}
if (filter_input(INPUT_GET, 'mode') === "continue") {
  $no = filter_input(INPUT_GET, 'no', FILTER_VALIDATE_INT);
  $mode = "continue";
}
if (filter_input(INPUT_GET, 'mode') === "admin") {
  $mode = "admin";
}
if (filter_input(INPUT_GET, 'mode') === "admin_in") {
  $mode = "admin_in";
}
if (filter_input(INPUT_GET, 'mode') === "piccom") {
  $stime = filter_input(INPUT_GET, 'stime', FILTER_VALIDATE_INT);
  $resto = filter_input(INPUT_GET, 'resto', FILTER_VALIDATE_INT);
  $mode = "piccom";
}
if (filter_input(INPUT_GET, 'mode') === "pictmp") {
  $mode = "pictmp";
}
if (filter_input(INPUT_GET, 'mode') === "picrep") {
  $no = filter_input(INPUT_GET, 'no');
  $pwd = (string)trim(filter_input(INPUT_GET, 'pwd'));
  $repcode = filter_input(INPUT_GET, 'repcode');
  $stime = filter_input(INPUT_GET, 'stime', FILTER_VALIDATE_INT);
  $mode = "picrep";
}
if (filter_input(INPUT_GET, 'mode') === "regist") {
  $mode = "regist";
}
if (filter_input(INPUT_GET, 'mode') === "reply") {
  $mode = "reply";
}
if (filter_input(INPUT_GET, 'mode') === "res") {
  $mode = "res";
}
if (filter_input(INPUT_GET, 'mode') === "sodane") {
  $mode = "sodane";
  $resto = filter_input(INPUT_GET, 'resto', FILTER_VALIDATE_INT);
}
if (filter_input(INPUT_GET, 'mode') === "continue") {
  $no = filter_input(INPUT_GET, 'no');
  $mode = "continue";
}
if (filter_input(INPUT_GET, 'mode') === "del") {
  $mode = "del";
}
if (filter_input(INPUT_GET, 'mode') === "edit") {
  $mode = "edit";
}
if (filter_input(INPUT_GET, 'mode') === "editexec") {
  $mode = "editexec";
}
if (filter_input(INPUT_GET, 'mode') === "catalog") {
  $mode = "catalog";
}
if (filter_input(INPUT_GET, 'mode') === "search") {
  $mode = "search";
}

switch ($mode) {
  default:
    return def();
}
exit;

//通常表示モード
function def()
{
  try {
    $db = new PDO(DB_PDO);
    //全スレッド取得
    $sql = "SELECT * FROM tlog WHERE invz=0 AND thread=1 ORDER BY tree DESC";
    $posts = $db->query($sql);

    $ko = array();
    $oya = array();

    $i = 0;
    $j = 0;
    while ($i < PAGE_DEF) {
      $bbsline = $posts->fetch();
      if (empty($bbsline)) {
        break;
      } //スレがなくなったら抜ける
      $oya_id = $bbsline["tid"]; //スレのtid(親番号)を取得
      $sql_i = "SELECT * FROM tlog WHERE parent = $oya_id AND invz=0 AND thread=0 ORDER BY comid ASC";
      //レス取得
      $posts_i = $db->query($sql_i);
      $j = 0;
      $flag = true;
      while ($flag == true) {
        $_pchext = pathinfo($bbsline['pchfile'], PATHINFO_EXTENSION);
        if ($_pchext === 'chi') {
          $bbsline['pchfile'] = ''; //ChickenPaintは動画リンクを出さない
        }
        $res = $posts_i->fetch();
        if (empty($res)) { //レスがなくなったら
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
        $res['resno'] = ($j + 1); //レス番号
        // http、https以外のURLの場合表示しない
        if (!filter_var($res['a_url'], FILTER_VALIDATE_URL) || !preg_match('|^https?://.*$|', $res['a_url'])) {
          $res['a_url'] = "";
        }
        $res['com'] = htmlspecialchars($res['com'], ENT_QUOTES | ENT_HTML5);

        //オートリンク
        if (AUTOLINK) {
          $res['com'] = auto_link($res['com']);
        }
        //ハッシュタグ
        if (USE_HASHTAG) {
          $res['com'] = hashtag_link($res['com']);
        }
        //空行を縮める
        $res['com'] = preg_replace('/(\n|\r|\r\n|\n\r){3,}/us', "\n\n", $res['com']);
        //引用の色
        $res['com'] = quote($res['com']);
        //日付をUNIX時間に変換して設定どおりにフォーマット
        $res['created'] = date(DATE_FORMAT, strtotime($res['created']));
        $res['modified'] = date(DATE_FORMAT, strtotime($res['modified']));
        $ko[] = $res;
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
      //引用の色
      $bbsline['com'] = quote($bbsline['com']);
      //日付をUNIX時間にしたあと整形
      $bbsline['past'] = strtotime($bbsline['created']); // このスレは古いので用
      $bbsline['created'] = date(DATE_FORMAT, strtotime($bbsline['created']));
      $bbsline['modified'] = date(DATE_FORMAT, strtotime($bbsline['modified']));
      $oya[] = $bbsline;
      $i++;
    }

    echo json_encode($oya, JSON_UNESCAPED_UNICODE);
    //echo json_encode($ko, JSON_UNESCAPED_UNICODE);

    $db = null; //db切断
  } catch (PDOException $e) {
    echo "DB接続エラー:" . $e->getMessage();
  }
}

/* オートリンク */
function auto_link($proto)
{
  if (!(stripos($proto, "script") !== false)) { //scriptがなければ続行
    $pattern = "{(https?|ftp)(://[[:alnum:]\+\$\;\?\.%,!#~*/:@&=_-]+)}";
    $replace = "<a href=\"\\1\\2\" target=\"_blank\" rel=\"nofollow noopener noreferrer\">\\1\\2</a>";
    $proto = preg_replace($pattern, $replace, $proto);
    return $proto;
  } else {
    return $proto;
  }
}

/* ハッシュタグリンク */
function hashtag_link($hashtag)
{
  $self = PHP_SELF;
  $pattern = "/(?:^|[^ｦ-ﾟー゛゜々ヾヽぁ-ヶ一-龠ａ-ｚＡ-Ｚ０-９a-zA-Z0-9&_\/]+)[#＃]([ｦ-ﾟー゛゜々ヾヽぁ-ヶ一-龠ａ-ｚＡ-Ｚ０-９a-zA-Z0-9_]*[ｦ-ﾟー゛゜々ヾヽぁ-ヶ一-龠ａ-ｚＡ-Ｚ０-９a-zA-Z]+[ｦ-ﾟー゛゜々ヾヽぁ-ヶ一-龠ａ-ｚＡ-Ｚ０-９a-zA-Z0-9_]*)/u";
  $replace = " <a href=\"{$self}?mode=search&amp;tag=tag&amp;search=\\1\">#\\1</a>";
  $hashtag = preg_replace($pattern, $replace, $hashtag);
  return $hashtag;
}

/* 引用色設定 */
function quote($quote)
{
  $quote = preg_replace("/(^|>)((&gt;|＞)[^<]*)/i", "\\1" . RE_START . "\\2" . RE_END, $quote);
  return $quote;
}
