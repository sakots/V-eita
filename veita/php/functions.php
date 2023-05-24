<?php

//設定の読み込み
require_once(__DIR__ . '/config.php');
require_once(__DIR__ . '/theme/' . THEMEDIR . '/theme_conf.php');

/* 関数ども */

//テーマがXHTMLか設定されてないなら
defined('TH_XHTML') or define('TH_XHTML', 0);

//ログの行数が最大値を超えていたら削除
function logdel()
{
	//オーバーした行の画像とスレ番号を取得
	try {
		$db = new PDO(DB_PDO);
		$sqlimg = "SELECT * FROM tlog ORDER BY tid LIMIT 1";
		$msgs = $db->prepare($sqlimg);
		$msgs->execute();
		$msg = $msgs->fetch();

		$dtid = (int)$msg["tid"]; //消す行のスレ番号
		$msgpic = $msg["picfile"]; //画像の名前取得できた
		//画像とかの削除処理
		if (is_file(IMG_DIR . $msgpic)) {
			$msgdat = pathinfo($msgpic, PATHINFO_FILENAME); //拡張子除去
			if (is_file(IMG_DIR . $msgdat . '.png')) {
				unlink(IMG_DIR . $msgdat . '.png');
			}
			if (is_file(IMG_DIR . $msgdat . '.jpg')) {
				unlink(IMG_DIR . $msgdat . '.jpg'); //一応jpgも
			}
			if (is_file(IMG_DIR . $msgdat . '.pch')) {
				unlink(IMG_DIR . $msgdat . '.pch');
			}
			if (is_file(IMG_DIR . $msgdat . '.spch')) {
				unlink(IMG_DIR . $msgdat . '.spch');
			}
			if (is_file(IMG_DIR . $msgdat . '.dat')) {
				unlink(IMG_DIR . $msgdat . '.dat');
			}
			if (is_file(IMG_DIR . $msgdat . '.chi')) {
				unlink(IMG_DIR . $msgdat . '.chi');
			}
		}

		//レスあれば削除
		//カウント
		$sqlc = "SELECT COUNT(*) as cnti FROM tlog WHERE parent = $dtid";
		$countres = $db->query("$sqlc");
		$countres = $countres->fetch();
		$logcount = $countres["cnti"];
		//削除
		if ($logcount !== 0) {
			$delres = "DELETE FROM tlog WHERE parent = $dtid";
			$db->exec($delres);
		}
		//スレ削除
		$delths = "DELETE FROM tlog WHERE tid = $dtid";
		$db->exec($delths);

		$sqlimg = null;
		$delths = null;
		$msg = null;
		$dtid = null;
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

/* 改行を<br>に */
function tobr($com)
{
	if (TH_XHTML !== 1) {
		$com = nl2br($com, false);
	} else {
		$com = nl2br($com);
	}
	return $com;
}

/* '>'色設定 */
function quote($quote)
{
	$quote = preg_replace("/(^|>)((&gt;|＞)[^<]*)/i", "\\1" . RE_START . "\\2" . RE_END, $quote);
	return $quote;
}
