<?php
//--------------------------------------------------
//  おえかきけいじばん「V-eita」
//  by sakots & OekakiBBS reDev.Team  https://oekakibbs.moe/
//--------------------------------------------------

//スクリプトのバージョン
define('VEITA_VER', 'v0.0.0'); //lot.250403.0

//設定の読み込み
require_once(__DIR__ . '/config.php');

//タイムゾーン設定
date_default_timezone_set(DEFAULT_TIMEZONE);

//phpのバージョンが古い場合動かさせない
if (($php_ver = phpversion()) < "7.3.0") {
	die("PHP version 7.3 or higher is required for this program to work. <br>\n(Current PHP version:{$php_ver})");
}
//コンフィグのバージョンが古くて互換性がない場合動かさせない
if (CONF_VER < 250430 || !defined('CONF_VER')) {
	die("コンフィグファイルに互換性がないようです。再設定をお願いします。<br>\n The configuration file is incompatible. Please reconfigure it.");
}

//管理パスが初期値(admin_pass)の場合は動作させない
if ($admin_pass === 'admin_pass') {
	die("管理パスが初期設定値のままです！危険なので動かせません。<br>\n The admin pass is still at its default value! This program can't run it until you fix it.");
}

//var_dump($_POST);

//絶対パス取得
$path = realpath("./") . '/' . IMG_DIR;
$temp_path = realpath("./") . '/' . TEMP_DIR;

define('IMG_PATH', $path);
define('TMP_PATH', $temp_path);

//格納する配列
$dat = array();

$message = "";
$self = PHP_SELF;

$dat['path'] = IMG_DIR;

$dat['neo_dir'] = NEO_DIR;
$dat['chicken_dir'] = CHICKEN_DIR;

$dat['ver'] = VEITA_VER;
$dat['base'] = BASE;
$dat['b_title'] = TITLE;
$dat['home'] = HOME;
$dat['self'] = PHP_SELF;
$dat['message'] = $message;
$dat['p_def_w'] = P_DEF_W;
$dat['p_def_h'] = P_DEF_H;
$dat['p_max_w'] = P_MAX_W;
$dat['p_max_h'] = P_MAX_H;

$dat['max_name'] = MAX_NAME;
$dat['max_email'] = MAX_EMAIL;
$dat['max_sub'] = MAX_SUB;
$dat['max_url'] = MAX_URL;
$dat['max_com'] = MAX_COM;

$dat['use_chicken'] = USE_CHICKENPAINT;

$dat['select_palettes'] = USE_SELECT_PALETTES;
$dat['pallets_dat'] = $pallets_dat;

$dat['display_id'] = DISPLAY_ID;
$dat['use_re_sub'] = USE_RE_SUB;

$dat['use_anime'] = USE_ANIME;
$dat['def_anime'] = DEF_ANIME;
$dat['use_continue'] = USE_CONTINUE;
$dat['new_post_no_password'] = !CONTINUE_PASS;

$dat['use_name'] = USE_NAME;
$dat['use_com'] = USE_COM;
$dat['use_sub'] = USE_SUB;

$dat['add_info'] = $add_info;

$dat['dp_time'] = DSP_PAINT_TIME;

$dat['share_button'] = SHARE_BUTTON;

$dat['use_hashtag'] = USE_HASHTAG;

if (!isset($admin_name)) {
	$admin_name = '管理人';
}

defined('ADMIN_CAP') or define('ADMIN_CAP', '(ではない)');

$dat['sodane'] = SODANE;

//ペイント画面の$pwdの暗号化
define('CRYPT_METHOD', 'aes-128-cbc');
define('CRYPT_IV', 'T3pkYxNyjN7Wz3pu'); //半角英数16文字

//NSFW画像機能を使う
$dat['use_nsfw'] = USE_NSFW;

//データベース接続PDO
define('DB_PDO', 'sqlite:' . DB_NAME . '.db');

//初期設定(初期設定後は不要なので削除可)
init();

del_temp();

$message = "";

//var_dump($_COOKIE);

$pwd_c = filter_input(INPUT_COOKIE, 'pwd_c');
$user_code = filter_input(INPUT_COOKIE, 'user_code'); //nullならuser-codeを発行

//$_SERVERから変数を取得
//var_dump($_SERVER);

$req_method = isset($_SERVER["REQUEST_METHOD"]) ? $_SERVER["REQUEST_METHOD"] : "";
//INPUT_SERVER が動作しないサーバがあるので$_SERVERを使う。

//ユーザーip
function get_uip()
{
	if ($user_ip = getenv("HTTP_CLIENT_IP")) {
		return $user_ip;
	} elseif ($user_ip = getenv("HTTP_X_FORWARDED_FOR")) {
		return $user_ip;
	} elseif ($user_ip = getenv("REMOTE_ADDR")) {
		return $user_ip;
	} else {
		return $user_ip;
	}
}
//csrfトークンを作成
function get_csrf_token()
{
	if (!isset($_SESSION)) {
		session_save_path(__DIR__ . '/session/');
		session_start();
	}
	header('Expires:');
	header('Cache-Control:');
	header('Pragma:');
	return hash('sha256', session_id(), false);
}
//csrfトークンをチェック
function check_csrf_token()
{
	session_save_path(__DIR__ . '/session/');
	session_start();
	$token = filter_input(INPUT_POST, 'token');
	$session_token = isset($_SESSION['token']) ? $_SESSION['token'] : '';
	if (!$session_token || $token !== $session_token) {
		error(MSG006);
	}
}

//user-codeの発行
if (!$user_code) { //falseなら発行
	$user_ip = get_uip();
	$user_code = substr(crypt(md5($user_ip . ID_SEED . date("Ymd", time())), 'id'), -12);
	//念の為にエスケープ文字があればアルファベットに変換
	$user_code = strtr($user_code, "!\"#$%&'()+,/:;<=>?@[\\]^`/{|}~", "ABCDEFGHIJKLMNOabcdefghijklmn");
}
setcookie("user_code", $user_code, time() + (86400 * 365)); //1年間

$dat['user_code'] = $user_code;

//var_dump($_GET);

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
if (filter_input(INPUT_GET, 'mode') === "pic_com") {
	$s_time = filter_input(INPUT_GET, 's_time', FILTER_VALIDATE_INT);
	$res_to = filter_input(INPUT_GET, 'res_to', FILTER_VALIDATE_INT);
	$mode = "pic_com";
}
if (filter_input(INPUT_GET, 'mode') === "pic_tmp") {
	$mode = "pic_tmp";
}
if (filter_input(INPUT_GET, 'mode') === "pic_rep") {
	$no = filter_input(INPUT_GET, 'no');
	$pwd = (string)trim(filter_input(INPUT_GET, 'pwd'));
	$rep_code = filter_input(INPUT_GET, 'rep_code');
	$s_time = filter_input(INPUT_GET, 's_time', FILTER_VALIDATE_INT);
	$mode = "pic_rep";
}
if (filter_input(INPUT_GET, 'mode') === "resist") {
	$mode = "resist";
}
if (filter_input(INPUT_GET, 'mode') === "reply") {
	$mode = "reply";
}
if (filter_input(INPUT_GET, 'mode') === "res") {
	$mode = "res";
}
if (filter_input(INPUT_GET, 'mode') === "sodane") {
	$mode = "sodane";
	$res_to = filter_input(INPUT_GET, 'res_to', FILTER_VALIDATE_INT);
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
if (filter_input(INPUT_GET, 'mode') === "edit_exec") {
	$mode = "edit_exec";
}
if (filter_input(INPUT_GET, 'mode') === "catalog") {
	$mode = "catalog";
}
if (filter_input(INPUT_GET, 'mode') === "search") {
	$mode = "search";
}
if (filter_input(INPUT_GET, 'mode') === "get_header") {
	$mode = "get_header";
}

switch ($mode) {
	case 'resist':
		return resist();
	case 'reply':
		return reply();
	case 'res':
		return res();
	case 'sodane':
		return sodane();
	case 'paint':
		$rep = "";
		return paint_form($rep);
	case 'pic_com':
		$tmp_mode = "";
		return paint_com($tmp_mode);
	case 'pic_tmp':
		$tmp_mode = "tmp";
		return paint_com($tmp_mode);
	case 'anime':
		if (!isset($sp)) {
			$sp = "";
		}
		return open_pch($pch, $sp);
	case 'continue':
		return in_continue($no);
	case 'cont_paint':
		//パスワードが必要なのは差し換えの時だけ
		$type = filter_input(INPUT_POST, 'type');
		if (CONTINUE_PASS || $type === 'rep') user_check();
		$rep = $type;
		return paint_form($rep);
	case 'pic_rep':
		return pic_replace();
	case 'catalog':
		return catalog();
	case 'search':
		return search();
	case 'edit':
		return edit_form();
	case 'edit_exec':
		return edit_exec();
	case 'del':
		return del_mode();
	case 'admin_in':
		return admin_in();
	case 'admin':
		return admin();
	case 'get_header':
		return get_header();
	default:
		return def();
}
exit;

/*-----------Main-------------*/

function init()
{
	try {
		if (!is_file(DB_NAME . '.db')) {
			// はじめての実行なら、テーブルを作成
			// id, 書いた日時, 修正日時, スレ親orレス, 親スレ, コメントid, スレ構造ID,
			// 名前, メール, タイトル, 本文, url, ホスト,
			// そうだね, 投稿者ID, パスワード, 絵の時間(内部), 絵の時間, 絵のurl, pchのurl, 絵の幅, 絵の高さ,
			// age/sage記憶, 表示/非表示, 絵のツール, 認証マーク, そろそろ消える, nsfw, 予備2, 予備3, 予備4
			$db = new PDO(DB_PDO);
			$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$sql = "CREATE TABLE tlog (tid integer primary key autoincrement, created TIMESTAMP, modified TIMESTAMP, thread VARCHAR(1), parent INT, comid BIGINT, tree BIGINT, a_name TEXT, mail TEXT, sub TEXT, com TEXT, a_url TEXT, host TEXT, exid TEXT, id TEXT, pwd TEXT, psec INT, utime TEXT, picfile TEXT, pchfile TEXT, img_w INT, img_h INT, age INT, invz VARCHAR(1), tool TEXT, admins VARCHAR(1), shd VARCHAR(1), ext01 TEXT, ext02 TEXT, ext03 TEXT, ext04 TEXT)";
			$db = $db->query($sql);
			$db = null; //db切断
		}
	} catch (PDOException $e) {
		echo "DB接続エラー:" . $e->getMessage();
	}
	$err = '';
	if (!is_writable(realpath("./"))) error("カレントディレクトリに書けません<br>");
	if (!is_dir(IMG_DIR)) {
		mkdir(IMG_DIR, PERMISSION_FOR_DIR);
		chmod(IMG_DIR, PERMISSION_FOR_DIR);
	}
	if (!is_dir(IMG_DIR)) $err .= IMG_DIR . "がありません<br>";
	if (!is_writable(IMG_DIR)) $err .= IMG_DIR . "を書けません<br>";
	if (!is_readable(IMG_DIR)) $err .= IMG_DIR . "を読めません<br>";

	if (!is_dir(TEMP_DIR)) {
		mkdir(TEMP_DIR, PERMISSION_FOR_DIR);
		chmod(TEMP_DIR, PERMISSION_FOR_DIR);
	}
	if (!is_dir(__DIR__ . '/session/')) {
		mkdir(__DIR__ . '/session/', PERMISSION_FOR_DIR);
		chmod(__DIR__ . '/session/', PERMISSION_FOR_DIR);
	}
	if (!is_dir(TEMP_DIR)) $err .= TEMP_DIR . "がありません<br>";
	if (!is_writable(TEMP_DIR)) $err .= TEMP_DIR . "を書けません<br>";
	if (!is_readable(TEMP_DIR)) $err .= TEMP_DIR . "を読めません<br>";
	if ($err) error($err);
}


//投稿があればデータベースへ保存する
/* 記事書き込み スレ立て */
function resist()
{
	global $bad_ip, $admin_pass, $admin_name;
	global $req_method;
	global $dat;

	//CSRFトークンをチェック
	if (CHECK_CSRF_TOKEN) {
		check_csrf_token();
	}

	$sub = (string)filter_input(INPUT_POST, 'sub');
	$name = (string)filter_input(INPUT_POST, 'name');
	$mail = (string)filter_input(INPUT_POST, 'mail');
	$url = (string)filter_input(INPUT_POST, 'url');
	$com = (string)filter_input(INPUT_POST, 'com');
	$pic_file = filter_input(INPUT_POST, 'pic_file');
	$invz = trim(filter_input(INPUT_POST, 'invz'));
	$img_w = trim(filter_input(INPUT_POST, 'img_w', FILTER_VALIDATE_INT));
	$img_h = trim(filter_input(INPUT_POST, 'img_h', FILTER_VALIDATE_INT));
	$pwd = (string)trim(filter_input(INPUT_POST, 'pwd'));
	$pwd_h = password_hash($pwd, PASSWORD_DEFAULT);
	$exid = trim(filter_input(INPUT_POST, 'exid', FILTER_VALIDATE_INT));
	$pal = filter_input(INPUT_POST, 'palettes');
	$nsfw_flag = (string)filter_input(INPUT_POST, 'nsfw', FILTER_VALIDATE_INT);

	if ($req_method !== "POST") {
		error(MSG006);
	}

	//NGワードがあれば拒絶
	Reject_if_NGword_exists_in_the_post($com, $name, $mail, $url, $sub);
	if (USE_NAME && !$name) {
		error(MSG009);
	}
	//レスの時は本文必須
	//if(filter_input(INPUT_POST, 'modid') && !$com) {error(MSG008);}
	if (USE_COM && !$com) {
		error(MSG008);
	}
	if (USE_SUB && !$sub) {
		error(MSG010);
	}

	if (strlen($com) > MAX_COM) {
		error(MSG011);
	}
	if (strlen($name) > MAX_NAME) {
		error(MSG012);
	}
	if (strlen($mail) > MAX_EMAIL) {
		error(MSG013);
	}
	if (strlen($sub) > MAX_SUB) {
		error(MSG014);
	}
	if (strlen($url) > MAX_URL) {
		error(MSG015);
	}

	//ホスト取得
	$host = gethostbyaddr(get_uip());

	foreach ($bad_ip as $value) { //拒絶host
		if (preg_match("/$value$/i", $host)) {
			error(MSG016);
		}
	}
	//セキュリティ関連ここまで

	try {
		$db = new PDO(DB_PDO);
		if (isset($_POST["send"])) {

			$strlen_com = strlen($com);

			if ($name   === "") $name = DEF_NAME;
			if ($com  === "") $com  = DEF_COM;
			if ($sub  === "") $sub  = DEF_SUB;

			// 二重投稿チェック
			//最新コメント取得
			$sql_w = "SELECT * FROM tlog WHERE thread=1 ORDER BY tid DESC LIMIT 1";
			$msg_w = $db->prepare($sql_w);
			$msg_w->execute();
			$msg_wc = $msg_w->fetch();
			if (!empty($msg_wc)) {
				$msg_sub = $msg_wc["sub"]; //最新タイトル
				$msg_w_com = $msg_wc["com"]; //最新コメント取得できた
				$msg_w_host = $msg_wc["host"]; //最新ホスト取得できた
				//どれも一致すれば二重投稿だと思う
				if ($strlen_com > 0 && $com == $msg_w_com && $host == $msg_w_host && $sub == $msg_sub) {
					$msg_w = null;
					$db = null; //db切断
					error('二重投稿ですか？');
				}
				//画像番号が一致の場合(投稿してブラウザバック、また投稿とか)
				//二重投稿と判別(画像がない場合は処理しない)
				if (!empty($_POST["modid"])) {
					if ($msg_wc["pic_file"] !== "" && $pic_file == $msg_wc["pic_file"]) {
						$db = null; //db切断
						error('二重投稿ですか？');
					}
				}
			}
			//↑ 二重投稿チェックおわり

			//画像ファイルとか処理
			if ($pic_file) {
				$path_filename = pathinfo($pic_file, PATHINFO_FILENAME); //拡張子除去
				$fp = fopen(TEMP_DIR . $path_filename . ".dat", "r");
				$userdata = fread($fp, 1024);
				fclose($fp);
				list($u_ip, $u_host,,, $u_code,, $start_time, $posted_time, $u_res_to, $tool) = explode("\t", rtrim($userdata) . "\t");
				//描画時間を$userdataをもとに計算
				if ($start_time && DSP_PAINT_TIME) {
					$p_sec = $posted_time - $start_time; //内部保存用
					$utime = calcPtime($p_sec);
				}
				//ツール
				if ($tool === 'neo') {
					$used_tool = 'PaintBBS NEO';
				} elseif ($tool === 'shi') {
					$used_tool = 'Shi Painter';
				} elseif ($tool === 'chicken') {
					$used_tool = 'Chicken Paint';
				} else {
					$used_tool = '???';
				}
				list($img_w, $img_h) = getimagesize(TEMP_DIR . $pic_file);
				rename(TEMP_DIR . $pic_file, IMG_DIR . $pic_file);
				chmod(IMG_DIR . $pic_file, PERMISSION_FOR_DEST);

				$pic_dat = $path_filename . '.dat';

				$chi_file = $path_filename . '.chi';
				$spch_file = $path_filename . '.spch';
				$pch_file = $path_filename . '.pch';

				if (is_file(TEMP_DIR . $pch_file)) {
					rename(TEMP_DIR . $pch_file, IMG_DIR . $pch_file);
					chmod(IMG_DIR . $pch_file, PERMISSION_FOR_DEST);
				} elseif (is_file(TEMP_DIR . $spch_file)) {
					rename(TEMP_DIR . $spch_file, IMG_DIR . $spch_file);
					chmod(IMG_DIR . $spch_file, PERMISSION_FOR_DEST);
					$pch_file = $spch_file;
				} elseif (is_file(TEMP_DIR . $chi_file)) {
					rename(TEMP_DIR . $chi_file, IMG_DIR . $chi_file);
					chmod(IMG_DIR . $chi_file, PERMISSION_FOR_DEST);
					$pch_file = $chi_file;
				} else {
					$pch_file = "";
				}
				chmod(TEMP_DIR . $pic_dat, PERMISSION_FOR_DEST);
				unlink(TEMP_DIR . $pic_dat);

				//nsfw
				if (USE_NSFW == 1 && $nsfw_flag == 1) {
					$nsfw = true;
				} else {
					$nsfw = false;
				}
			} else {
				$img_w = 0;
				$img_h = 0;
				$pch_file = "";
				$utime = "";
				$used_tool = "";
			}

			// 値を追加する

			//不要改行圧縮
			$com = preg_replace("/(\n|\r|\r\n){3,}/us", "\n\n", $com);

			//id生成
			$id = gen_id($host, $utime);

			//管理者名は管理パスじゃないと使えない
			if ($name === $admin_name && $pwd !== $admin_pass) {
				$name = $name . ADMIN_CAP;
			}

			//管理者名の投稿でパスワードが管理パスなら管理者バッジつける
			$admins = ($pwd === $admin_pass && $name === $admin_name) ? 1 : 0;

			// 'のエスケープ(入りうるところがありそうなとこだけにしといた)
			$name = str_replace("'", "''", $name);
			$sub = str_replace("'", "''", $sub);
			$com = str_replace("'", "''", $com);
			$mail = str_replace("'", "''", $mail);
			$url = str_replace("'", "''", $url);
			$host = str_replace("'", "''", $host);
			$id = str_replace("'", "''", $id);

			//age値取得
			$sql_age = "SELECT MAX(age) FROM tlog";
			$age = $db->exec("$sql_age");
			$tree = time() * 100000000;

			//スレ建て
			$thread = 1;
			$shd = 0;
			$age = 0;
			$parent = NULL;
			$sql = "INSERT INTO tlog (created, modified, thread, parent, comid, tree, a_name, sub, com, mail, a_url, picfile, pchfile, img_w, img_h, psec, utime, pwd, id, exid, age, invz, host, tool, admins, shd, ext01) VALUES (datetime('now', 'localtime'), datetime('now', 'localtime'), :thread, :parent, :tree, :tree, :a_name, :sub, :com, :mail, :a_url, :picfile, :pch_file, :img_w, :img_h, :psec, :utime, :pwd_h, :id, :exid, :age, :invz, :host, :used_tool, :admins, :shd, :nsfw)";

			$stmt = $db->prepare($sql);
			$stmt->execute(
				[
					'thread'=>$thread, 'parent'=>$parent, 'tree'=>$tree, 'a_name'=>$name,'sub'=>$sub,'com'=>$com,'mail'=>$mail,'a_url'=>$url,'picfile'=> $pic_file,'pch_file'=> $pch_file, 'img_w'=>$img_w,'img_h'=> $img_h, 'psec'=>$psec,'utime'=> $utime,'pwd_h'=> $pwd_h,'id'=> $id,'exid'=> $exid,'age'=> $age,'invz'=> $invz,'host'=> $host,'used_tool'=> $used_tool,'admins'=> $admins,'shd'=> $shd,'nsfw'=> $nsfw,
				]
			);
			//$db->exec($sql);

			$c_pass = $pwd;
			//-- クッキー保存 --
			//クッキー項目："クッキー名 クッキー値"
			$cookies = [["name_c",$name],["email_c",$mail] , ["url_c", $url], ["pwd_c", $c_pass] ,[ "palette_c" , $pal]];
			foreach ($cookies as $cookie) {
				list($c_name, $c_cookie) = $cookie;
				$c_name = (string)$c_name;
				$c_cookie = (string)$c_cookie;
				setcookie($c_name, $c_cookie, time() + (SAVE_COOKIE * 24 * 3600));
			}

			$dat['message'] = '書き込みに成功しました。';
			$msg_w = null;
			$db = null; //db切断
		}
	} catch (PDOException $e) {
		echo "DB接続エラー:" . $e->getMessage();
	}
	unset($name, $mail, $sub, $com, $url, $pwd, $pwd_h, $res_to, $pic_tmp, $pic_file, $mode);
	//header('Location:'.PHP_SELF);
	//ログ行数オーバー処理
	//スレ数カウント
	try {
		$db = new PDO(DB_PDO);
		$sql_th = "SELECT SUM(thread) as cnt FROM tlog";
		$th_cnt_sql = $db->query("$sql_th");
		$th_cnt_sql = $th_cnt_sql->fetch();
		$th_cnt = $th_cnt_sql["cnt"];
	} catch (PDOException $e) {
		echo "DB接続エラー:" . $e->getMessage();
	}
	if ($th_cnt > LOG_MAX_T) {
		log_del();
	}
	//そろそろ消える用
	//$thid = (int)round( LOG_MAX_T * LOG_LIMIT/100 ); //閾値 … 新しい方からこの件数以降がもうすぐ消える
	//if ($th_cnt > $thid) {
	//	loglimit();
	//}

	ok('書き込みに成功しました。画面を切り替えます。');
}

//記事書き込み - リプライ
function reply()
{
	global $bad_ip, $admin_pass, $admin_name;
	global $req_method;
	global $dat;

	//CSRFトークンをチェック
	if (CHECK_CSRF_TOKEN) {
		check_csrf_token();
	}

	$sub = (string)filter_input(INPUT_POST, 'sub');
	$name = (string)filter_input(INPUT_POST, 'name');
	$mail = (string)filter_input(INPUT_POST, 'mail');
	$url = (string)filter_input(INPUT_POST, 'url');
	$com = (string)filter_input(INPUT_POST, 'com');
	$parent = trim(filter_input(INPUT_POST, 'parent', FILTER_VALIDATE_INT));
	$invz = trim(filter_input(INPUT_POST, 'invz', FILTER_VALIDATE_INT));
	$pwd = trim(filter_input(INPUT_POST, 'pwd'));
	$pwd_h = password_hash($pwd, PASSWORD_DEFAULT);
	$exid = trim(filter_input(INPUT_POST, 'exid', FILTER_VALIDATE_INT));
	$pal = filter_input(INPUT_POST, 'palettes');

	if ($req_method !== "POST") {
		error(MSG006);
	}

	//NGワードがあれば拒絶
	Reject_if_NGword_exists_in_the_post($com, $name, $mail, $url, $sub);
	if (USE_NAME && !$name) {
		error(MSG009);
	}
	//レスの時は本文必須
	if (!$com) {
		error(MSG008);
	}
	if (USE_SUB && !$sub) {
		error(MSG010);
	}

	if (strlen($com) > MAX_COM) {
		error(MSG011);
	}
	if (strlen($name) > MAX_NAME) {
		error(MSG012);
	}
	if (strlen($mail) > MAX_EMAIL) {
		error(MSG013);
	}
	if (strlen($sub) > MAX_SUB) {
		error(MSG014);
	}
	if (strlen($url) > MAX_URL) {
		error(MSG015);
	}

	//ホスト取得
	$host = gethostbyaddr(get_uip());

	foreach ($bad_ip as $value) { //拒絶host
		if (preg_match("/$value$/i", $host)) {
			error(MSG016);
		}
	}
	//セキュリティ関連ここまで

	try {
		$db = new PDO(DB_PDO);
		if (isset($_POST["send"])) {

			$strlen_com = strlen($com);

			if ($name  === "") $name = DEF_NAME;
			if ($com  === "") $com  = DEF_COM;
			if ($sub  === "") $sub  = DEF_SUB;

			// 二重投稿チェック
			//最新コメント取得
			$sql_w = "SELECT * FROM tlog WHERE thread=0 ORDER BY tid DESC LIMIT 1";
			$msg_w = $db->prepare($sql_w);
			$msg_w->execute();
			$msg_wc = $msg_w->fetch() ?: [];
			if (!empty($msg_wc)) {
				$msg_w_sub = $msg_wc["sub"]; //最新タイトル
				$msg_w_com = $msg_wc["com"]; //最新コメント取得できた
				$msg_w_host = $msg_wc["host"]; //最新ホスト取得できた
				//どれも一致すれば二重投稿だと思う
				if ($strlen_com > 0 && $com == $msg_w_com && $host == $msg_w_host && $sub == $msg_w_sub) {
					$msg_w = null;
					$db = null; //db切断
					error('二重投稿ですか？');
				}
			} else {
				//最初のレスのage処理対策
				$msg_wc["tid"] = 0;
				$msg_wc["age"] = 0;
				$msg_wc["tree"] = 0;
			}
			//↑ 二重投稿チェックおわり

			// 値を追加する

			//不要改行圧縮
			$com = preg_replace("/(\n|\r|\r\n){3,}/us", "\n\n", $com);

			//id生成
			$id = gen_id($host, time());

			//管理者名は管理パスじゃないと使えない
			if ($name === $admin_name && $pwd !== $admin_pass) {
				$name = $name . ADMIN_CAP;
			}
			//管理者名の投稿でパスワードが管理パスなら管理者バッジつける
			$admins = ($pwd === $admin_pass && $name === $admin_name) ? 1 : 0;

			// 'のエスケープ(入りうるところがありそうなとこだけにしといた)
			$name = str_replace("'", "''", $name);
			$sub = str_replace("'", "''", $sub);
			$com = str_replace("'", "''", $com);
			$mail = str_replace("'", "''", $mail);
			$url = str_replace("'", "''", $url);
			$host = str_replace("'", "''", $host);
			$id = str_replace("'", "''", $id);

			//レスの位置
			$tree = time() - $parent - (int)$msg_wc["tid"];
			$com_id = $tree + time();

			//メール欄にsageが含まれるならageない
			$age = (int)$msg_wc["age"];
			if (strpos($mail, 'sage') !== false) {
				//sage
				$age = $age;
			} else {
				//age
				$age++;
				$age_tree = $age + (time() * 100000000);
				$sql_age = "UPDATE tlog SET age = $age, tree = $age_tree WHERE tid = $parent";
				$db->exec($sql_age);
			}

			//リプ処理
			$thread = 0;
			$sql = "INSERT INTO tlog (created, modified, thread, parent, comid, tree, a_name, sub, com, mail, a_url, pwd, id, exid, age, invz, host, admins) VALUES (datetime('now', 'localtime'), datetime('now', 'localtime'), :thread, :parent, :comid, :tree, :a_name, :sub, :com, :mail, :a_url, :pwd_h, :id, :exid, :age, :invz, :host, :admins)";

			// プレースホルダ
			$stmt = $db->prepare($sql);
			$stmt->execute(
				[
					'thread'=>$thread, 'parent'=>$parent, 'comid'=>$com_id,'tree'=>$tree, 'a_name'=>$name,'sub'=>$sub,'com'=>$com,'mail'=>$mail,'a_url'=>$url,'pwd_h'=> $pwd_h,'id'=> $id,'exid'=> $exid,'age'=> $age,'invz'=> $invz,'host'=> $host,'admins'=> $admins,
				]
			);
			//$db->exec($sql);

			$c_pass = $pwd;
			//-- クッキー保存 --
			//クッキー項目："クッキー名 クッキー値"
			$cookies = [["name_c",$name],["email_c",$mail] , ["url_c", $url], ["pwd_c", $c_pass]];
			foreach ($cookies as $cookie) {
				list($c_name, $c_cookie) = $cookie;
				$c_name = (string)$c_name;
				$c_cookie = (string)$c_cookie;
				setcookie($c_name, $c_cookie, time() + (SAVE_COOKIE * 24 * 3600));
			}

			$dat['message'] = '書き込みに成功しました。';
			$msg_w = null;
			$db = null; //db切断
		}
	} catch (PDOException $e) {
		echo "DB接続エラー:" . $e->getMessage();
	}
	unset($name, $mail, $sub, $com, $url, $pwd, $pwd_h, $res_to, $pic_tmp, $pic_file, $mode);
	//header('Location:'.PHP_SELF);
	ok('書き込みに成功しました。画面を切り替えます。');
}

//ヘッダー表示
function get_header()
{
	global $dat;
	header('Access-Control-Allow-Origin: http://localhost:5173');
	echo json_encode($dat, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
}

//通常表示モード
function def()
{
	global $dat;
	$dsp_res = DSP_RES;
	$page_def = PAGE_DEF;

	//ログ行数オーバー処理
	//スレ数カウント
	try {
		$db = new PDO(DB_PDO);
		$sql_th = "SELECT SUM(thread) as cnt FROM tlog";
		$th_cnt_sql = $db->query("$sql_th");
		$th_cnt_sql = $th_cnt_sql->fetch();
		$th_cnt = $th_cnt_sql["cnt"];
	} catch (PDOException $e) {
		echo "DB接続エラー:" . $e->getMessage();
	}
	if ($th_cnt > LOG_MAX_T) {
		log_del();
	}

	//古いスレのレスボタンを表示しない
	$elapsed_time = ELAPSED_DAYS * 86400; //デフォルトの1年だと31536000
	$now_time = time(); //いまのunixタイムスタンプを取得
	//あとはテーマ側で計算する
	$dat['now_time'] = $now_time;
	$dat['elapsed_time'] = $elapsed_time;

	//ページング
	try {
		$db = new PDO(DB_PDO);
		$sql_cnt = "SELECT SUM(thread) as cnt FROM tlog WHERE invz=0";
		$th_cnt_sql = $db->query("$sql_cnt");
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
		$dat['now_page'] = $page;
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

		$i = 0; //スレ
		$j = 0; //レス
		while ($i < PAGE_DEF) {
			$bbs_line = $posts->fetch();
			if (empty($bbs_line)) {
				break;
			} //スレがなくなったら抜ける
			$oya_id = $bbs_line["tid"]; //スレのtid(親番号)を取得
			$sql_i = "SELECT * FROM tlog WHERE parent = $oya_id AND invz=0 AND thread=0 ORDER BY comid ASC";
			//レス取得
			$posts_i = $db->query($sql_i);
			$j = 0;
			$flag = true;
			while ($flag == true) {
				$_pch_ext = pathinfo($bbs_line['pchfile'], PATHINFO_EXTENSION);
				if ($_pch_ext === 'chi') {
					$bbs_line['pchfile'] = ''; //ChickenPaintは動画リンクを出さない
				}
				$res = $posts_i->fetch();
				if (empty($res)) { //レスがなくなったら
					$bbs_line['res_su'] = $j; //スレのレス数
					$bbs_line['res_d_su'] = $j - DSP_RES; //スレのレス省略数
					if ($j > DSP_RES) { //スレのレス数が規定より多いと
						$bbs_line['r_flag'] = true; //省略フラグtrue
					} else {
						$bbs_line['r_flag'] = false; //省略フラグfalse
					}
					$flag = false;
					break;
				} //抜ける
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
				$bbs_line['res'][$j] = $res;
				$j++;
			}
			// http、https以外のURLの場合表示しない
			if (!filter_var($bbs_line['a_url'], FILTER_VALIDATE_URL) || !preg_match('|^https?://.*$|', $bbs_line['a_url'])) {
				$bbs_line['a_url'] = "";
			}
			$bbs_line['com'] = htmlspecialchars($bbs_line['com'], ENT_QUOTES | ENT_HTML5);

			//オートリンク
			if (AUTOLINK) {
				$bbs_line['com'] = auto_link($bbs_line['com']);
			}
			//ハッシュタグ
			if (USE_HASHTAG) {
				$bbs_line['com'] = hashtag_link($bbs_line['com']);
			}
			//空行を縮める
			$bbs_line['com'] = preg_replace('/(\n|\r|\r\n){3,}/us', "\n\n", $bbs_line['com']);
			//引用の色
			$bbs_line['com'] = quote($bbs_line['com']);
			//日付をUNIX時間にしたあと整形
			$bbs_line['past'] = strtotime($bbs_line['created']); // このスレは古いので用
			$bbs_line['created'] = date(DATE_FORMAT, strtotime($bbs_line['created']));
			$bbs_line['modified'] = date(DATE_FORMAT, strtotime($bbs_line['modified']));
			$dat['oya'][$i] = $bbs_line;
			$i++;
		}

		$dat['dsp_res'] = DSP_RES;
		$dat['path'] = IMG_DIR;

		header('Access-Control-Allow-Origin: http://localhost:5173');
		echo json_encode($dat, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
		$db = null; //db切断
	} catch (PDOException $e) {
		echo "DB接続エラー:" . $e->getMessage();
	}
}

//カタログモード
function catalog()
{
	global $blade, $dat;
	$page_def = CATALOG_N;

	//ページング
	try {
		$db = new PDO(DB_PDO);
		if (isset($_GET['page']) && is_numeric($_GET['page'])) {
			$page = $_GET['page'];
			$page = max($page, 1);
		} else {
			$page = 1;
		}
		$start = $page_def * ($page - 1);

		//最大何ページあるのか
		$sql_th = "SELECT SUM(thread) as cnt FROM tlog WHERE invz=0";
		$th_cnt_sql = $db->query("$sql_th");
		$th_cnt_sql = $th_cnt_sql->fetch();
		$th_cnt = $th_cnt_sql["cnt"];
		$max_page = floor($th_cnt / $page_def) + 1;
		//最後にスレ数0のページができたら表示しない処理
		if (($th_cnt % $page_def) == 0) {
			$max_page = $max_page - 1;
			//ただしそれが1ページ目なら困るから表示
			$max_page = max($max_page, 1);
		}
		$dat['max_page'] = $max_page;

		//リンク作成用
		$dat['now_page'] = $page;
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
		$sql = "SELECT tid, created, modified, a_name, mail, sub, com, a_url, host, exid, id, pwd, utime, picfile, pchfile, img_w, img_h, utime, tree, parent, age, utime FROM tlog WHERE thread=1 AND invz=0 ORDER BY age DESC, tree DESC LIMIT :start, :page_def";
		$posts = $db->prepare($sql);
		$posts->bindValue(':start', $start, PDO::PARAM_INT);
		$posts->bindValue(':page_def', $page_def, PDO::PARAM_INT);
		$posts->execute();


		$oya = array();

		$i = 0;
		while ($i < CATALOG_N) {
			$bbs_line = $posts->fetch();
			if (empty($bbs_line)) {
				break;
			} //スレがなくなったら抜ける
			$bbs_line['com'] = nl2br(htmlspecialchars($bbs_line['com'], ENT_QUOTES | ENT_HTML5), false);
			$oya[] = $bbs_line;
			$i++;
		}

		$dat['oya'] = $oya;
		$dat['path'] = IMG_DIR;

		//$smarty->debugging = true;
		$dat['catalog_mode'] = 'catalog';
		echo $blade->run(CATALOGFILE, $dat);
		$db = null; //db切断
	} catch (PDOException $e) {
		echo "DB接続エラー:" . $e->getMessage();
	}
}

//検索モード 現在全件表示のみ対応
function search()
{
	global $blade, $dat;

	$search_f = filter_input(INPUT_GET, 'search');
	$search = str_replace("'", "''", $search_f); //SQL
	//部分一致検索
	$division =  filter_input(INPUT_GET, 'division');
	//本文検索
	$tag = filter_input(INPUT_GET, 'tag');

	//読み込み
	try {
		$db = new PDO(DB_PDO);
		//全スレッド取得
		//まずtagがあれば全文検索
		if ($tag == 'tag') {
			$sql = "SELECT * FROM tlog WHERE com LIKE ? AND invz=0 ORDER BY age DESC, tree DESC";
			$posts = $db->prepare($sql);
			$posts->execute(["%$search%"]);
			$dat['catalog_mode'] = 'hash_search';
			$dat['tag'] = $search_f;
		} else {
			//tagがなければ作者名検索(スレッドのみ)
			if ($division === "division") {
				$sql = "SELECT * FROM tlog WHERE a_name LIKE ? AND invz=0 AND thread=1 ORDER BY age DESC, tree DESC";
				$posts = $db->prepare($sql);
				$posts->execute(["%$search%"]);
			} else {
				//完全一致
				$sql = "SELECT * FROM tlog WHERE a_name LIKE ? AND invz=0 AND thread=1 ORDER BY age DESC, tree DESC";
				$posts = $db->prepare($sql);
				$posts->execute([$search]);
			}
			$dat['catalog_mode'] = 'search';
			$dat['author'] = $search_f;
		}

		$oya = array();

		$i = 0;
		while ($bbs_line = $posts->fetch()) {
			$bbs_line['com'] = nl2br(htmlspecialchars($bbs_line['com'], ENT_QUOTES | ENT_HTML5), false);
			$oya[] = $bbs_line;
			$i++;
		}

		$dat['oya'] = $oya;
		$dat['path'] = IMG_DIR;

		$dat['s_result'] = $i;
		echo $blade->run(CATALOGFILE, $dat);
		$db = null; //db切断
	} catch (PDOException $e) {
		echo "DB接続エラー:" . $e->getMessage();
	}
}

//そうだね
function sodane()
{
	$res_to = filter_input(INPUT_GET, 'res_to', FILTER_VALIDATE_INT);
	try {
		$db = new PDO(DB_PDO);
		$stmt = $db->prepare("UPDATE tlog SET exid = exid + 1 WHERE tid = ?");
		$stmt->execute([$res_to]);
		$db = null;
	} catch (PDOException $e) {
		echo "DB接続エラー:" . $e->getMessage();
	}
	header('Location:' . PHP_SELF);
	header('Access-Control-Allow-Origin: http://localhost:5173');
	def();
}
//レス画面

function res()
{
	global $blade, $dat;
	$resno = filter_input(INPUT_GET, 'res',FILTER_VALIDATE_INT);
	$dat['resno'] = $resno;

	//csrfトークンをセット
	$dat['token'] = '';
	if (CHECK_CSRF_TOKEN) {
		$token = get_csrf_token();
		$_SESSION['token'] = $token;
		$dat['token'] = $token;
	}

	//古いスレのレスフォームを表示しない
	$elapsed_time = ELAPSED_DAYS * 86400; //デフォルトの1年だと31536000
	$nowtime = time(); //いまのunixタイムスタンプを取得
	//あとはテーマ側で計算する
	$dat['elapsed_time'] = $elapsed_time;
	$dat['nowtime'] = $nowtime;

	try {
		$db = new PDO(DB_PDO);
		$sql = "SELECT * FROM tlog WHERE tid = ? ORDER BY tree DESC";
		$posts = $db->prepare($sql);
		$posts->execute([$resno]);

		$oya = array();
		$ko = array();
		while ($bbs_line = $posts->fetch()) {
			//スレッドの記事を取得
			$sqli = "SELECT * FROM tlog WHERE parent = $resno AND invz = 0 ORDER BY comid ASC";
			$postsi = $db->query($sqli);
			$rresname = array();
			while ($res = $postsi->fetch()) {
				$res['com'] = htmlspecialchars($res['com'], ENT_QUOTES | ENT_HTML5);

				if (AUTOLINK) {
					$res['com'] = auto_link($res['com']);
				}
				//ハッシュタグ
				if (USE_HASHTAG) {
					$res['com'] = hashtag_link($res['com']);
				}
				//空行を縮める
				$res['com'] = preg_replace('/(\n|\r|\r\n){3,}/us', "\n\n", $res['com']);
				//引用の色
				$res['com'] = quote($res['com']);
				//日付をUNIX時間に
				$bbs_line['past'] = strtotime($bbs_line['created']); // このスレは古いので用
				$res['created'] = date(DATE_FORMAT, strtotime($res['created']));
				$res['modified'] = date(DATE_FORMAT, strtotime($res['modified']));
				$ko[] = $res;
				//投稿者名取得
				if (!in_array($res['a_name'], $rresname)) { //重複除外
					$rresname[] = $res['a_name']; //投稿者名を配列に入れる
				}
				// http、https以外のURLの場合表示しない
				if (!filter_var($res['a_url'], FILTER_VALIDATE_URL) || !preg_match('|^https?://.*$|', $res['a_url'])) {
					$res['a_url'] = "";
				}
			}
			$bbs_line['com'] = htmlspecialchars($bbs_line['com'], ENT_QUOTES | ENT_HTML5);

			if (AUTOLINK) {
				$bbs_line['com'] = auto_link($bbs_line['com']);
			}
			//ハッシュタグ
			if (USE_HASHTAG) {
				$bbs_line['com'] = hashtag_link($bbs_line['com']);
			}
			//空行を縮める
			$bbs_line['com'] = preg_replace('/(\n|\r|\r\n){3,}/us', "\n", $bbs_line['com']);
			//引用の色
			$bbs_line['com'] = quote($bbs_line['com']);
			//日付をUNIX時間に
			$bbs_line['past'] = strtotime($bbs_line['created']); //古いので用
			$bbs_line['created'] = date(DATE_FORMAT, strtotime($bbs_line['created']));
			$bbs_line['modified'] = date(DATE_FORMAT, strtotime($bbs_line['modified']));
			$oya[] = $bbs_line;
			if (!in_array($bbs_line['a_name'], $rresname)) {
				$rresname[] = $bbs_line['a_name'];
			}
			// http、https以外のURLの場合表示しない
			if (!filter_var($bbs_line['a_url'], FILTER_VALIDATE_URL) || !preg_match('|^https?://.*$|', $bbs_line['a_url'])) {
				$bbs_line['a_url'] = "";
			}
			//名前付きレス用
			$resname = implode(A_NAME_SAN . ' ', $rresname);
			$dat['resname'] = $resname;

			$dat['oya'] = $oya;
			$dat['ko'] = $ko;
		}
		$db = null;
	} catch (PDOException $e) {
		echo "DB接続エラー:" . $e->getMessage();
	}

	$dat['path'] = IMG_DIR;

	echo $blade->run(RESFILE, $dat);
}

//お絵描き画面
function paint_form($rep)
{
	global $message, $user_code, $quality, $qualitys, $no;
	global $mode, $ctype, $pch, $type;
	global $blade, $dat;
	global $pallets_dat;

	$pwd = (string)filter_input(INPUT_POST, 'pwd');
	$imgfile = filter_input(INPUT_POST, 'img');

	//ツール
	if (isset($_POST["tools"])) {
		$tool = filter_input(INPUT_POST, 'tools');
	} else {
		$tool = "neo";
	}
	$dat['tool'] = $tool;

	$dat['message'] = $message;

	$picw = filter_input(INPUT_POST, 'picw', FILTER_VALIDATE_INT);
	$pich = filter_input(INPUT_POST, 'pich', FILTER_VALIDATE_INT);

	if ($mode === "cont_paint") {
		list($picw, $pich) = getimagesize(IMG_DIR . $imgfile); //キャンバスサイズ

	}

	$anime = isset($_POST["anime"]) ? true : false;
	$dat['anime'] = $anime;

	if ($picw < 300) $picw = 300;
	if ($pich < 300) $pich = 300;
	if ($picw > P_MAX_W) $picw = P_MAX_W;
	if ($pich > P_MAX_H) $pich = P_MAX_H;

	$dat['picw'] = $picw;
	$dat['pich'] = $pich;

	if ($tool == "shi") { //しぃペインターの時の幅と高さ
		$ww = $picw + 510;
		$hh = $pich + 172;
	} else { //NEOのときの幅と高さ
		$ww = $picw + 150;
		$hh = $pich + 172;
	}
	if ($hh < 560) {
		$hh = 560;
	} //共通の最低高
	$dat['w'] = $ww;
	$dat['h'] = $hh;

	$dat['undo'] = UNDO;
	$dat['undo_in_mg'] = UNDO_IN_MG;

	$dat['useanime'] = USE_ANIME;

	$dat['path'] = IMG_DIR;

	$dat['s_time'] = time();

	$user_ip = get_uip();

	//続きから
	if ($rep !== "") {
		$ctype = filter_input(INPUT_POST, 'ctype');
		$type = $rep;
		$pwdf = filter_input(INPUT_POST, 'pwd');

		$dat['no'] = $no;
		$dat['pwd'] = $pwdf;
		$dat['ctype'] = $ctype;
		if (is_file(IMG_DIR . $pch . '.pch')) {
			$dat['useneo'] = true;
		} elseif (is_file(IMG_DIR . $pch . '.spch')) {
			$dat['useneo'] = false;
		}
		if ((C_SECURITY_CLICK || C_SECURITY_TIMER) && SECURITY_URL) {
			$dat['security'] = true;
			$dat['security_click'] = C_SECURITY_CLICK;
			$dat['security_timer'] = C_SECURITY_TIMER;
		}
	} else {
		if ((SECURITY_CLICK || SECURITY_TIMER) && SECURITY_URL) {
			$dat['security'] = true;
			$dat['security_click'] = SECURITY_CLICK;
			$dat['security_timer'] = SECURITY_TIMER;
		}
		$dat['newpaint'] = true;
	}
	$dat['security_url'] = SECURITY_URL;

	//パレット設定
	//初期パレット
	$lines = array();
	$initial_palette = 'Palettes[0] = "#000000\n#FFFFFF\n#B47575\n#888888\n#FA9696\n#C096C0\n#FFB6FF\n#8080FF\n#25C7C9\n#E7E58D\n#E7962D\n#99CB7B\n#FCECE2\n#F9DDCF";';
	foreach ($pallets_dat as $p_value) {
		if ($p_value[1] == filter_input(INPUT_POST, 'palettes')) { // キーと入力された値が同じなら
			$set_palettec = $p_value[1];
			setcookie("palettec", $set_palettec, time() + (86400 * SAVE_COOKIE)); // Cookie保存
			if (is_array($p_value)) {
				$lines = file($p_value[1]);
			} else {
				$lines = file($p_value);
			}
			break;
		}
	}

	$pal = array();
	$DynP = array();
	$p_cnt = 0;
	$arr_pal=[];
	foreach ($lines as $i => $line) {
		$line = charconvert(str_replace(["\r", "\n", "\t"], "", $line));
		list($pid, $pname, $pal[0], $pal[2], $pal[4], $pal[6], $pal[8], $pal[10], $pal[1], $pal[3], $pal[5], $pal[7], $pal[9], $pal[11], $pal[12], $pal[13]) = explode(",", $line);
		$DynP[] = $pname;
		$p_cnt = $i + 1;
		$palettes = 'Palettes[' . $p_cnt . '] = "#';
		ksort($pal);
		$palettes .= implode('\n#', $pal);
		$palettes .= '";';
		$arr_pal[$i] = $palettes;
	}
	$user_pallete_i = $initial_palette . implode('', $arr_pal);
	$dat['palettes'] = $user_pallete_i;

	$count_dynp = count($DynP) + 1;

	$dat['palsize'] = $count_dynp;

	//パスワード暗号化
	$pwdf = openssl_encrypt($pwd, CRYPT_METHOD, CRYPT_PASS, true, CRYPT_IV); //暗号化
	$pwdf = bin2hex($pwdf); //16進数に
	$arr_dynp=[];
	foreach ($DynP as $p) {
		$arr_dynp[] = '<option>' . $p . '</option>';
	}
	$dat['dynp'] = implode('', $arr_dynp);

	if ($ctype == 'pch') {
		$pch_file = filter_input(INPUT_POST, 'pch');
		$dat['pch_file'] = IMG_DIR . $pch_file;
	}
	if ($ctype == 'img') {
		$dat['animeform'] = false;
		$dat['anime'] = false;
		$imgfile = filter_input(INPUT_POST, 'img');
		$dat['imgfile'] = IMG_DIR . $imgfile;
	}
	$user_code .= '&tool=' . $tool . '&s_time=' . time(); //拡張ヘッダにツールと描画開始時間をセット

	//差し換え時の認識コード追加
	if ($type === 'rep') {
		$no = filter_input(INPUT_POST, 'no', FILTER_VALIDATE_INT);
		$user_ip = get_uip();
		$time = time();
		$rep_code = substr(crypt(md5($no . $user_ip . $pwdf . date("Ymd", $time)), $time), -8);
		//念の為にエスケープ文字があればアルファベットに変換
		$rep_code = strtr($rep_code, "!\"#$%&'()+,/:;<=>?@[\\]^`/{|}~", "ABCDEFGHIJKLMNOabcdefghijklmn");
		$datmode = 'pic_rep&no=' . $no . '&pwd=' . $pwdf . '&rep_code=' . $rep_code;
		$user_code .= '&rep_code=' . $rep_code;
	}
	$dat['user_code'] = $user_code; //user_codeにいろいろくっついたものをまとめて出力

	//出口
	if ($type === 'rep') {
		//差し替え
		$dat['mode'] = $datmode;
	} else {
		//新規投稿
		$dat['mode'] = 'pic_com';
	}
	//出力
	if ($tool === 'chicken') {
		echo $blade->run(PAINTFILE_BE, $dat);
	} else {
		echo $blade->run(PAINTFILE, $dat);
	}
}

//アニメ再生

function open_pch($pch, $sp = "")
{
	global $blade, $dat;
	$message = "";

	$pch = filter_input(INPUT_GET, 'pch');
	$pchh = str_replace(strrchr($pch, "."), "", $pch); //拡張子除去
	$extn = substr($pch, strrpos($pch, '.') + 1); //拡張子取得

	$pic_file = IMG_DIR . $pchh . ".png";

	if ($extn == 'spch') {
		$pch_file = IMG_DIR . $pch;
		$dat['tool'] = 'shi'; //拡張子がspchのときはしぃぺ
	} elseif ($extn == 'pch') {
		$pch_file = IMG_DIR . $pch;
		$dat['tool'] = 'neo'; //拡張子がpchのときはNEO
		//}elseif($extn=='chi'){
		//	$pch_file = IMG_DIR.$pch;
		//	$dat['tool'] = 'chicken'; //拡張子がchiのときはChickenPaint 対応してくれるといいな
	} else {
		$w = $h = $picw = $pich = $datasize = ""; //動画が無い時は処理しない
		$dat['tool'] = 'neo';
	}
	$datasize = filesize($pch_file);
	$size = getimagesize($pic_file);
	if (!$sp) $sp = PCH_SPEED;
	$picw = $size[0];
	$pich = $size[1];
	$w = $picw;
	$h = $pich + 26;
	if ($w < 300) {
		$w = 300;
	}
	if ($h < 326) {
		$h = 326;
	}

	$dat['picw'] = $picw;
	$dat['pich'] = $pich;
	$dat['w'] = $w;
	$dat['h'] = $h;
	$dat['pch_file'] = './' . $pch;
	$dat['datasize'] = $datasize;

	$dat['speed'] = PCH_SPEED;

	$dat['path'] = IMG_DIR;
	$dat['a_s_time'] = time();

	echo $blade->run(ANIMEFILE, $dat);
}

//お絵かき投稿
function paint_com($tmp_mode)
{
	global $user_code, $ptime;
	global $blade, $dat;

	$dat['parent'] = $_SERVER['REQUEST_TIME'];
	$dat['user_code'] = $user_code;

	//----------

	//csrfトークンをセット
	$dat['token'] = '';
	if (CHECK_CSRF_TOKEN) {
		$token = get_csrf_token();
		$_SESSION['token'] = $token;
		$dat['token'] = $token;
	}

	//投稿途中一覧 or 画像新規投稿 or 画像差し替え
	if ($tmp_mode == "tmp") {
		$dat['picmode'] = 'is_temp';
	} elseif ($tmp_mode == "rep") {
		$dat['picmode'] = 'pict_rep';
	} else {
		$dat['picmode'] = 'pict_up';
	}

	//----------

	//var_dump($_POST);
	$user_ip = get_uip();
	//テンポラリ画像リスト作成
	$tmplist = array();
	$handle = opendir(TEMP_DIR);
	while (false !== ($file = readdir($handle))) {
		if (!is_dir($file) && preg_match("/\.(dat)\z/i", $file)) {
			$fp = fopen(TEMP_DIR . $file, "r");
			$userdata = fread($fp, 1024);
			fclose($fp);
			list($uip, $uhost, $uagent, $imgext, $ucode,, $starttime, $postedtime,, $tool) = explode("\t", rtrim($userdata) . "\t");
			$file_name = preg_replace("/\.(dat)\z/i", "", $file); //拡張子除去
			if (is_file(TEMP_DIR . $file_name . $imgext)) //画像があればリストに追加
				//描画時間を$userdataをもとに計算
				//(表示用)
				$utime = calcPtime((int)$postedtime - (int)$starttime);
			//描画時間(内部用)
			$psec = (int)$postedtime - (int)$starttime;
			$tmplist[] = $ucode . "\t" . $uip . "\t" . $file_name . $imgext . "\t" . $utime . "\t" . $psec . "\t" . $tool;
		}
	}
	closedir($handle);
	$tmp = array();
	if (count($tmplist) != 0) {
		//user-codeとipアドレスでチェック
		foreach ($tmplist as $tmpimg) {
			list($ucode, $uip, $ufilename, $utime, $psec, $tool) = explode("\t", $tmpimg);
			if ($ucode == $user_code || $uip == $user_ip) {
				$tmp[] = $ufilename;
			}
		}
	}

	$post_mode = true;
	$resist = true;
	$ipcheck = true;
	if (count($tmp) == 0) {
		$notmp = true;
		$pic_tmp = 1;
	} else {
		$pic_tmp = 2;
		sort($tmp);
		reset($tmp);
		$temp = array();
		foreach ($tmp as $tmpfile) {
			$src = TEMP_DIR . $tmpfile;
			$srcname = $tmpfile;
			$date = gmdate("Y/m/d H:i", filemtime($src) + 9 * 60 * 60);
			$utime = $utime;
			$psec = $psec;
			$temp[] = compact('src', 'srcname', 'date', 'tool', 'utime', 'psec');
		}
		$dat['temp'] = $temp;
	}

	$tmp2 = array();
	$dat['tmp'] = $tmp2;

	echo $blade->run(PICFILE, $dat);
}

//コンティニュー画面in
function in_continue($no)
{
	global $blade, $dat;
	$dat['othermode'] = 'in_continue';
	$dat['continue_mode'] = true;

	if (isset($_POST["tools"])) {
		$tool = filter_input(INPUT_POST, 'tools');
	} else {
		$tool = "neo";
	}
	$dat['tool'] = $tool;

	//コンティニュー時は削除キーを常に表示
	$dat['passflag'] = true;
	//新規投稿で削除キー不要の時 true
	if (!CONTINUE_PASS) $dat['newpost_nopassword'] = true;

	try {
		$db = new PDO(DB_PDO);
		$sql = "SELECT * FROM tlog WHERE picfile=? ORDER BY tree DESC";
		$posts = $db->prepare($sql);
		$posts->execute([$no]);
		$oya = array();
		while ($bbs_line = $posts->fetch()) {
			$bbs_line['com'] = nl2br(htmlentities($bbs_line['com'], ENT_QUOTES | ENT_HTML5), false);
			$oya[] = $bbs_line;
			$dat['oya'] = $oya; //配列に格納
		}
		$hist_ope = pathinfo($no, PATHINFO_FILENAME); //拡張子除去
		$histfilename = IMG_DIR . $hist_ope;
		if (is_file($histfilename . '.pch')) {
			//$pch_file = IMG_DIR.$pch;
			$dat['tool'] = 'neo'; //拡張子がpchのときはNEO
			$dat['useshi'] = false;
			$dat['useneo'] = true;
			$dat['ctype_pch'] = true;
		} elseif (is_file($histfilename . '.chi')) {
			$dat['tool'] = 'chicken'; //拡張子がchiのときはChickenPaint
			$dat['useshi'] = false;
			$dat['useneo'] = false;
			$dat['ctype_pch'] = true;
		} else { // どれでもない＝動画が無い時
			//$w=$h=$picw=$pich=$datasize="";
			$dat['useneo'] = true;
			$dat['useshi'] = true;
			$dat['ctype_pch'] = false;
		}
		// useshi, useneoは互換のためにいちおう残してある
		$dat['ctype_img'] = true;

		$db = null; //db切断
	} catch (PDOException $e) {
		echo "DB接続エラー:" . $e->getMessage();
	}

	echo $blade->run(OTHERFILE, $dat);
}

//削除くん

function del_mode()
{
	global $admin_pass;
	global $dat;
	$delno = filter_input(INPUT_POST, 'delno',FILTER_VALIDATE_INT);

	$ppwd = filter_input(INPUT_POST, 'pwd');

	//記事呼び出し
	try {
		$db = new PDO(DB_PDO);

		//パスワードを取り出す
		$sql = "SELECT pwd FROM tlog WHERE tid = ?";
		$msgs = $db->prepare($sql);
		if ($msgs == false) {
			error('そんな記事ない気がします。');
		}
		$msgs->execute([$delno]);
		$msg = $msgs->fetch();
		if (empty($msg)) {
			error('そんな記事ない気がします。');
		}

		//削除記事の画像を取り出す
		$sqlp = "SELECT picfile FROM tlog WHERE tid = ?";
		$msgsp = $db->prepare($sqlp);
		$msgsp->execute([$delno]);
		$msgsp->execute();
		$msgp = $msgsp->fetch();
		if (empty($msgp)) {
			error('画像が見当たりません。');
		}
		$msgpic = $msgp['picfile']; //画像の名前取得できた

		if (isset($_POST["admindel"])) {
			$admin_del_mode = 1;
		} else {
			$admin_del_mode = 0;
		}

		if (password_verify($ppwd, $msg['pwd'])) {
			//画像とかファイル削除
			if (is_file(IMG_DIR . $msgpic)) {
				$msgdat = str_replace(strrchr($msgpic, "."), "", $msgpic); //拡張子除去
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
			//↑画像とか削除処理完了
			//データベースから削除
			$sql = "DELETE FROM tlog WHERE tid = ?";
			$stmt = $db->prepare($sql);
			$stmt->execute([$delno]);
			$dat['message'] = '削除しました。';
		} elseif ($admin_pass == $ppwd && $admin_del_mode == 1) {
			//画像とかファイル削除
			if (is_file(IMG_DIR . $msgpic)) {
				$msgdat = str_replace(strrchr($msgpic, "."), "", $msgpic); //拡張子除去
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
			//↑画像とか削除処理完了
			//データベースから削除
			$sql = "DELETE FROM tlog WHERE tid = ? OR parent = ?";
			$stmt = $db->prepare($sql);
			$stmt->execute([$delno, $delno]);
			$dat['message'] = '削除しました。';
		} elseif ($admin_pass == $ppwd && $admin_del_mode != 1) {
			//管理モード以外での管理者削除は
			//データベースから削除はせずに非表示
			$sql = "UPDATE tlog SET invz=1 WHERE tid = ?";
			$stmt = $db->prepare($sql);
			$stmt->execute([$delno]);
			$dat['message'] = '非表示にしました。';
		} else {
			error('パスワードまたは記事番号が違います。');
		}
		$msgp = null;
		$msg = null;
		$db = null; //db切断
	} catch (PDOException $e) {
		echo "DB接続エラー:" . $e->getMessage();
	}
	//変数クリア
	unset($delno, $delt);
	//header('Location:'.PHP_SELF);
	ok('削除しました。画面を切り替えます。');
}

//画像差し替え
function pic_replace()
{
	global $type;
	global $path, $badip;
	$s_time = filter_input(INPUT_GET, 's_time', FILTER_VALIDATE_INT);
	$no = filter_input(INPUT_GET, 'no', FILTER_VALIDATE_INT);
	$rep_code = filter_input(INPUT_GET, 'rep_code');
	$pwdf = filter_input(INPUT_GET, 'pwd');
	$pwdf = hex2bin($pwdf); //バイナリに
	$pwdf = openssl_decrypt($pwdf, CRYPT_METHOD, CRYPT_PASS, true, CRYPT_IV); //復号化
	$nsfw_flag = filter_input(INPUT_POST, 'nsfw');

	//ホスト取得
	$host = gethostbyaddr(get_uip());

	foreach ($badip as $value) { //拒絶host
		if (preg_match("/$value$/i", $host)) error(MSG016);
	}

	/*--- テンポラリ捜査 ---*/
	$find = false;
	$handle = opendir(TEMP_DIR);
	while (false !== ($file = readdir($handle))) {
		if (!is_dir($file) && preg_match("/\.(dat)\z/i", $file)) {
			$fp = fopen(TEMP_DIR . $file, "r");
			$userdata = fread($fp, 1024);
			fclose($fp);
			list($uip, $uhost, $uagent, $imgext, $ucode, $u_rep_code, $starttime, $postedtime,, $tool) = explode("\t", rtrim($userdata) . "\t"); //区切りの"\t"を行末にして配列へ格納
			$file_name = pathinfo($file, PATHINFO_FILENAME); //拡張子除去
			if ($file_name && is_file(TEMP_DIR . $file_name . $imgext) && $u_rep_code === $rep_code) {
				$find = true;
				break;
			}
		}
	}
	closedir($handle);
	if (!$find) {
		error(MSG007);
	}

	// ログ読み込み
	try {
		$db = new PDO(DB_PDO);
		//記事を取り出す
		$sql = "SELECT * FROM tlog WHERE tid = ?";
		$msgs = $db->prepare($sql);
		$msgs->execute([$no]);
		$msg_d = $msgs->fetch();
		//パスワード照合
		// $flag = false;
		if (password_verify($pwdf, $msg_d["pwd"])) {
			//パスワードがあってたら画像アップロード処理
			$up_pic_file = TEMP_DIR . $file_name . $imgext;
			$dest = IMG_DIR . $s_time . '.tmp';
			copy($up_pic_file, $dest);

			if (!is_file($dest)) error(MSG003);
			chmod($dest, PERMISSION_FOR_DEST);
			//元ファイル削除
			unlink(IMG_DIR . $msg_d["picfile"]);

			$img_type = mime_content_type($dest);
			$imgext = getImgType($img_type, $dest);

			//新しい画像の名前(DB保存用)
			$new_pic_file = $file_name . $imgext;

			chmod($dest, PERMISSION_FOR_DEST);
			rename($dest, IMG_DIR . $new_pic_file);

			//ワークファイル削除
			if (is_file($up_pic_file)) unlink($up_pic_file);
			if (is_file(TEMP_DIR . $file_name . ".dat")) unlink(TEMP_DIR . $file_name . ".dat");

			//動画ファイルアップロード
			//拡張子チェック
			$pchext = '';
			if (is_file(TEMP_DIR . $file_name . '.chi')) {
				$pchext = '.chi';
			} elseif (is_file(TEMP_DIR . $file_name . '.spch')) {
				$pchext = '.spch';
			} elseif (is_file(TEMP_DIR . $file_name . '.pch')) {
				$pchext = '.pch';
			}
			//元ファイル削除
			safe_unlink(IMG_DIR . $msg_d["pchfile"]);

			//新しい動画ファイルの名前(DB保存用)
			$new_pch_file = $file_name . $pchext;

			//動画ファイルアップロード本編
			if (is_file(TEMP_DIR . $file_name . $pchext)) {
				$pchsrc = TEMP_DIR . $file_name . $pchext;
				$dst = IMG_DIR . $new_pch_file;
				if (copy($pchsrc, $dst)) {
					chmod($dst, PERMISSION_FOR_DEST);
					unlink($pchsrc);
				}
			}

			//描画時間を$userdataをもとに計算
			$psec = (int)$msg_d['psec'] + ((int)$postedtime - (int)$starttime);
			$utime = calcPtime($psec);

			//ホスト名取得
			$host = gethostbyaddr(get_uip());

			//id生成
			$id = gen_id($host, $psec);

			// 念のため'のエスケープ
			$host = str_replace("'", "''", $host);

			//nsfw
			if (USE_NSFW == 1 && $nsfw_flag == 1) {
				$nsfw = true;
			} else {
				$nsfw = false;
			}

			//db上書き
			$sqlrep = "UPDATE tlog set modified = datetime('now', 'localtime'), host = :host, picfile = :new_pic_file, pchfile = :new_pch_file, id = :id, psec = :psec, utime = :utime, ext01 = :nsfw WHERE tid = :no";
			// プレースホルダ
			try {
				$stmt = $db->prepare($sqlrep);
				$stmt->execute(
					[
						':host'=>$host, ':new_pic_file'=>$new_pic_file, ':new_pch_file'=>$new_pch_file, ':id'=>$id,':psec'=>$psec,':utime'=>$utime,':nsfw'=>$nsfw,':no'=>$no,
					]
				);
			} catch(PDOException $e) {
				echo "DB接続エラー:" . $e->getMessage();
			}
			$db = $db->exec($sqlrep);
		} else {
			error(MSG028);
		}
		$db = null; //db切断
	} catch (PDOException $e) {
		echo "DB接続エラー:" . $e->getMessage();
	}
	ok('編集に成功しました。画面を切り替えます。');
}


//編集モードくん入口
function edit_form()
{
	global $admin_pass;
	global $blade, $dat;

	//csrfトークンをセット
	$dat['token'] = '';
	if (CHECK_CSRF_TOKEN) {
		$token = get_csrf_token();
		$_SESSION['token'] = $token;
		$dat['token'] = $token;
	}

	//入力されたパスワード
	$postpwd = filter_input(INPUT_POST, 'pwd');

	$editno = filter_input(INPUT_POST, 'delno',FILTER_VALIDATE_INT);
	if ($editno == "") {
		error('記事番号を入力してください');
	}

	//記事呼び出し
	try {
		$db = new PDO(DB_PDO);

		//パスワードを取り出す
		$sql = "SELECT pwd FROM tlog WHERE tid = ?";
		$stmt = $db->prepare($sql);
		$stmt->execute([$editno]);
		$msg = $stmt->fetch();
		if (empty($msg)) {
			error('そんな記事ないです。');
		}
		if (password_verify($postpwd, $msg['pwd'])) {
			//パスワードがあってたら
			$sqli = "SELECT * FROM tlog WHERE tid = $editno";
			$posts = $db->query($sqli);
			$oya = array();
			while ($bbs_line = $posts->fetch()) {
				$bbs_line['com'] = nl2br(htmlentities($bbs_line['com'], ENT_QUOTES | ENT_HTML5), false);
				$oya[] = $bbs_line;
				$dat['oya'] = $oya;
			}
			$dat['message'] = '編集モード...';
		} elseif ($admin_pass == $postpwd) {
			//管理者編集モード
			$sqli = "SELECT * FROM tlog WHERE tid = $editno";
			$posts = $db->query($sqli);
			$oya = array();
			while ($bbs_line = $posts->fetch()) {
				$bbs_line['com'] = nl2br(htmlentities($bbs_line['com'], ENT_QUOTES | ENT_HTML5), false);
				$oya[] = $bbs_line;
				$dat['oya'] = $oya;
			}
			$dat['message'] = '管理者編集モード...';
		} else {
			$db = null;
			$msgs = null;
			$db = null; //db切断
			error('パスワードまたは記事番号が違います。');
		}
		$db = null;
		$msgs = null;
		$posts = null;
		$db = null; //db切断

		$dat['othermode'] = 'edit'; //編集モード
		echo $blade->run(OTHERFILE, $dat);
	} catch (PDOException $e) {
		echo "DB接続エラー:" . $e->getMessage();
	}
}

//編集モードくん本体
function edit_exec()
{
	global $badip;
	global $req_method;
	global $dat;

	//CSRFトークンをチェック
	if (CHECK_CSRF_TOKEN) {
		check_csrf_token();
	}

	$resedit = trim((string)filter_input(INPUT_POST, 'resedit'));
	$e_no = trim((string)filter_input(INPUT_POST, 'e_no'));

	if ($req_method !== "POST") {
		error(MSG006);
	}

	$sub = (string)filter_input(INPUT_POST, 'sub');
	$name = (string)filter_input(INPUT_POST, 'name');
	$mail = (string)filter_input(INPUT_POST, 'mail');
	$url = (string)filter_input(INPUT_POST, 'url');
	$com = (string)filter_input(INPUT_POST, 'com');
	$pic_file = trim((string)filter_input(INPUT_POST, 'pic_file'));
	$pwd = (string)trim(filter_input(INPUT_POST, 'pwd'));
	$pwd_h = password_hash($pwd, PASSWORD_DEFAULT);
	$exid = trim((string)filter_input(INPUT_POST, 'exid', FILTER_VALIDATE_INT));

	//NGワードがあれば拒絶
	Reject_if_NGword_exists_in_the_post($com, $name, $mail, $url, $sub);

	if (USE_NAME && !$name) {
		error(MSG009);
	}
	//本文必須でいいだろ
	if (!$com) {
		error(MSG008);
	}
	if (USE_SUB && !$sub) {
		error(MSG010);
	}

	if (strlen($com) > MAX_COM) {
		error(MSG011);
	}
	if (strlen($name) > MAX_NAME) {
		error(MSG012);
	}
	if (strlen($mail) > MAX_EMAIL) {
		error(MSG013);
	}
	if (strlen($sub) > MAX_SUB) {
		error(MSG014);
	}

	//ホスト取得
	$host = gethostbyaddr(get_uip());

	foreach ($badip as $value) { //拒絶host
		if (preg_match("/$value$/i", $host)) {
			error(MSG016);
		}
	}
	//↑セキュリティ関連ここまで

	// 'のエスケープ(入りうるところがありそうなとこだけにしといた)
	$name = str_replace("'", "''", $name);
	$sub = str_replace("'", "''", $sub);
	$com = str_replace("'", "''", $com);
	$mail = str_replace("'", "''", $mail);
	$url = str_replace("'", "''", $url);
	$host = str_replace("'", "''", $host);

	try {
		$db = new PDO(DB_PDO);
		$sql = "UPDATE tlog set modified = datetime('now', 'localtime'), a_name = :name, mail = :mail, sub = :sub, com = :com, a_url = :url, host = :host, exid = :exid, pwd = :pwd_h where tid = :e_no";

		// プレースホルダ
		try {
			$stmt = $db->prepare($sql);
			$stmt->execute(
				[
					':name'=>$name, ':mail'=>$mail, ':sub'=>$sub, ':com'=>$com,':url'=>$url,':host'=>$host,':exid'=> $exid,':pwd_h'=> $pwd_h, ':e_no'=>$e_no,
					]
			);
			} catch(PDOException $e) {
				echo "DB接続エラー:" . $e->getMessage();
			}

		$db = $db->exec($sql);
		$db = null;
		$dat['message'] = '編集完了しました。';
	} catch (PDOException $e) {
		echo "DB接続エラー:" . $e->getMessage();
	}
	unset($name, $mail, $sub, $com, $url, $pwd, $pwd_h, $res_to, $pic_tmp, $pic_file, $mode);
	//header('Location:'.PHP_SELF);
	ok('編集に成功しました。画面を切り替えます。');
}

//管理モードin
function admin_in()
{
	global $blade, $dat;
	$dat['othermode'] = 'admin_in';

	echo $blade->run(OTHERFILE, $dat);
}

//管理モード
function admin()
{
	global $admin_pass;
	global $blade, $dat;

	$dat['path'] = IMG_DIR;

	//最大何ページあるのか
	//記事呼び出しから
	try {
		$db = new PDO(DB_PDO);
		//読み込み
		$adminpass = filter_input(INPUT_POST, 'adminpass');
		if ($adminpass === $admin_pass) {
			$sql = "SELECT * FROM tlog WHERE thread=1 ORDER BY age DESC,tree DESC";
			$oya = array();
			$posts = $db->prepare($sql);
			$posts->execute();
			while ($bbs_line = $posts->fetch()) {
				if (empty($bbs_line)) {
					break;
				} //スレがなくなったら抜ける
				//$oya_id = $bbs_line["tid"]; //スレのtid(親番号)を取得
				$bbs_line['com'] = htmlentities($bbs_line['com'], ENT_QUOTES | ENT_HTML5);
				$oya[] = $bbs_line;
			}
			$dat['oya'] = $oya;

			//スレッドの記事を取得
			$sqli = "SELECT * FROM tlog WHERE thread=0 ORDER BY tree ASC";
			$ko = array();
			$postsi = $db->query($sqli);
			while ($res = $postsi->fetch()) {
				$res['com'] = htmlentities($res['com'], ENT_QUOTES | ENT_HTML5);
				$ko[] = $res;
			}
			$dat['ko'] = $ko;
			echo $blade->run(ADMINFILE, $dat);
		} else {
			$db = null; //db切断
			error('管理パスを入力してください');
		}
		$db = null; //db切断
	} catch (PDOException $e) {
		echo "DB接続エラー:" . $e->getMessage();
	}
}

// コンティニュー認証 (画像)
function user_check()
{
	$no = filter_input(INPUT_POST, 'no', FILTER_VALIDATE_INT);
	$pwdf = filter_input(INPUT_POST, 'pwd');
	$flag = FALSE;
	try {
		$db = new PDO(DB_PDO);
		//パスワードを取り出す
		$sql = "SELECT pwd FROM tlog WHERE tid = ?";
		$msgs = $db->prepare($sql);
		$msgs->execute([$no]);
		$msg = $msgs->fetch();
		if (password_verify($pwdf, $msg['pwd'])) {
			$flag = true;
		} else {
			$flag = false;
		}
		$db = null; //切断
	} catch (PDOException $e) {
		echo "DB接続エラー:" . $e->getMessage();
	}
	if (!$flag) {
		error(MSG028);
	}
}

//OK画面
function ok($mes)
{
	global $blade, $dat;
	$dat['okmes'] = $mes;
	$dat['othermode'] = 'ok';
	$async_flag = (bool)filter_input(INPUT_POST,'asyncflag',FILTER_VALIDATE_BOOLEAN);
	$http_x_requested_with = (bool)(isset($_SERVER['HTTP_X_REQUESTED_WITH']));
	if($http_x_requested_with || $async_flag){
		return die("OK!\n$mes");
	}
	echo $blade->run(OTHERFILE, $dat);
}

//エラー画面
function error($mes)
{
	global $db;
	global $blade, $dat;
	$db = null; //db切断
	$dat['errmes'] = $mes;
	$dat['othermode'] = 'err';
	$async_flag = (bool)filter_input(INPUT_POST,'asyncflag',FILTER_VALIDATE_BOOLEAN);
	$http_x_requested_with = (bool)(isset($_SERVER['HTTP_X_REQUESTED_WITH']));
	if($http_x_requested_with || $async_flag){
		return die("error\n$mes");
	}
	echo $blade->run(OTHERFILE, $dat);
	exit;
}

//画像差し替え失敗
function error2()
{
	global $db;
	global $blade, $dat;
	global $self;
	$db = null; //db切断
	$dat['othermode'] = 'err2';
	$async_flag = (bool)filter_input(INPUT_POST,'asyncflag',FILTER_VALIDATE_BOOLEAN);
	$http_x_requested_with = (bool)(isset($_SERVER['HTTP_X_REQUESTED_WITH']));
	if($http_x_requested_with || $async_flag){
		return die("error?\n画像が見当たりません。投稿に失敗している可能性があります。<a href=\"{{$self}}?mode=pic_com\">アップロード途中の画像</a>に残っているかもしれません。");
	}
	echo $blade->run(OTHERFILE, $dat);
	exit;
}

//Asyncリクエストの時は処理を中断
function check_AsyncRequest($pic_file='') {
	//ヘッダーが確認できなかった時の保険
	$asyncflag = (bool)filter_input(INPUT_POST,'asyncflag',FILTER_VALIDATE_BOOLEAN);
	$http_x_requested_with = (bool)(isset($_SERVER['HTTP_X_REQUESTED_WITH']));
	if($http_x_requested_with || $asyncflag){
		safe_unlink($pic_file);
		exit;
	}
}

/* テンポラリ内のゴミ除去 */
function del_temp()
{
	$handle = opendir(TEMP_DIR);
	while ($file = readdir($handle)) {
		if (!is_dir($file)) {
			$lapse = time() - filemtime(TEMP_DIR . $file);
			if ($lapse > (TEMP_LIMIT * 24 * 3600)) {
				unlink(TEMP_DIR . $file);
			}
			//pchアップロードペイントファイル削除
			if (preg_match("/\A(pchup-.*-tmp\.s?pch)\z/i", $file)) {
				$lapse = time() - filemtime(TEMP_DIR . $file);
				if ($lapse > (300)) { //5分
					unlink(TEMP_DIR . $file);
				}
			}
		}
	}
	closedir($handle);
}

// 文字コード変換
function charconvert($str)
{
	mb_language(LANG);
	return mb_convert_encoding($str, "UTF-8", "auto");
}

/* NGワードがあれば拒絶 */
function Reject_if_NGword_exists_in_the_post($com, $name, $email, $url, $sub)
{
	global $badstring, $badname, $badstr_A, $badstr_B, $pwd, $admin_pass;
	//チェックする項目から改行・スペース・タブを消す
	$chk_com  = preg_replace("/\s/u", "", $com);
	$chk_name = preg_replace("/\s/u", "", $name);
	$chk_email = preg_replace("/\s/u", "", $email);
	$chk_sub = preg_replace("/\s/u", "", $sub);

	//本文に日本語がなければ拒絶
	if (USE_JAPANESE_FILTER) {
		mb_regex_encoding("UTF-8");
		if (strlen($com) > 0 && !preg_match("/[ぁ-んァ-ヶー一-龠]+/u", $chk_com)) error(MSG035);
	}

	//本文へのURLの書き込みを禁止
	if (!($pwd === $admin_pass)) { //どちらも一致しなければ
		if (DENY_COMMENTS_URL && preg_match('/:\/\/|\.co|\.ly|\.gl|\.net|\.org|\.cc|\.ru|\.su|\.ua|\.gd/i', $com)) error(MSG036);
	}

	// 使えない文字チェック
	if (is_ngword($badstring, [$chk_com, $chk_sub, $chk_name, $chk_email])) {
		error(MSG032);
	}

	// 使えない名前チェック
	if (is_ngword($badname, $chk_name)) {
		error(MSG037);
	}

	//指定文字列が2つあると拒絶
	$bstr_A_find = is_ngword($badstr_A, [$chk_com, $chk_sub, $chk_name, $chk_email]);
	$bstr_B_find = is_ngword($badstr_B, [$chk_com, $chk_sub, $chk_name, $chk_email]);
	if ($bstr_A_find && $bstr_B_find) {
		error(MSG032);
	}
}

//念のため画像タイプチェック
function getImgType($img_type, $dest)
{
	switch ($img_type) {
		case "image/gif":
			return ".gif";
		case "image/jpeg":
			return ".jpg";
		case "image/png":
			return ".png";
		case "image/webp":
			return ".webp";
		default:
			return error(MSG004, $dest);
	}
}

/**
 * NGワードチェック
 * @param $ngwords
 * @param string|array $strs
 * @return bool
 */
function is_ngword($ngwords, $strs)
{
	if (empty($ngwords)) {
		return false;
	}
	if (!is_array($strs)) {
		$strs = [$strs];
	}
	foreach ($strs as $str) {
		foreach ($ngwords as $ngword) { //拒絶する文字列
			if ($ngword !== '' && preg_match("/{$ngword}/ui", $str)) {
				return true;
			}
		}
	}
	return false;
}

/**
 * 描画時間を計算
 * @param $starttime
 * @return string
 */
function calcPtime($psec)
{

	$D = floor($psec / 86400);
	$H = floor($psec % 86400 / 3600);
	$M = floor($psec % 3600 / 60);
	$S = $psec % 60;

	return ($D ? $D . PTIME_D : '') . ($H ? $H . PTIME_H : '') . ($M ? $M . PTIME_M : '') . ($S ? $S . PTIME_S : '');
}
/**
 * ファイルがあれば削除
 * @param $path
 * @return bool
 */
function safe_unlink($path)
{
	if ($path && is_file($path)) {
		return unlink($path);
	}
	return false;
}

//ログの行数が最大値を超えていたら削除
function log_del()
{
	//オーバーした行の画像とスレ番号を取得
	try {
		$db = new PDO(DB_PDO);
		$sqlimg = "SELECT * FROM tlog ORDER BY tid LIMIT 1";
		$msgs = $db->prepare($sqlimg);
		$msgs->execute();
		$msg = $msgs->fetch();

		$del_tid = (int)$msg["tid"]; //消す行のスレ番号
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
		$sqlc = "SELECT COUNT(*) as cnti FROM tlog WHERE parent = $del_tid";
		$countres = $db->query("$sqlc");
		$countres = $countres->fetch();
		$logcount = $countres["cnti"];
		//削除
		if ($logcount !== 0) {
			$delres = "DELETE FROM tlog WHERE parent = $del_tid";
			$db->exec($delres);
		}
		//スレ削除
		$delths = "DELETE FROM tlog WHERE tid = $del_tid";
		$db->exec($delths);

		$sqlimg = null;
		$delths = null;
		$msg = null;
		$del_tid = null;
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

/* '>'色設定 */
function quote($quote)
{
	$quote = preg_replace("/(^|>)((&gt;|＞)[^<]*)/i", "\\1" . RE_START . "\\2" . RE_END, $quote);
	return $quote;
}

/* ID生成 */
function gen_id($user_ip, $time)
{
	if (ID_CYCLE === '0') {
		return substr(crypt(md5($user_ip . ID_SEED), 'id'), -8);
	} elseif (ID_CYCLE === '1') {
		return substr(crypt(md5($user_ip . ID_SEED . date("Ymd", $time)), 'id'), -8);
	} elseif (ID_CYCLE === '2') {
		$week = ceil(date("d", $time) / 7);
		return substr(crypt(md5($user_ip . ID_SEED . date("Ym", $time) . $week), 'id'), -8);
	} elseif (ID_CYCLE === '3') {
		return substr(crypt(md5($user_ip . ID_SEED . date("Ym", $time)), 'id'), -8);
	} elseif (ID_CYCLE === '4') {
		return substr(crypt(md5($user_ip . ID_SEED . date("Y", $time)), 'id'), -8);
	} else {
		return substr(crypt(md5($user_ip . ID_SEED), 'id'), -8);
	}
}

//リダイレクト
function redirect($url): void {
	header("Location: {$url}");
	exit();
}

//シェアするserverの選択画面
function set_share_server(): void {
	global $servers,$blade,$dat;

	//ShareするServerの一覧
	//｢"ラジオボタンに表示するServer名","snsのserverのurl"｣
	$servers = $servers ??
	[
		["X","https://x.com"],
		["Bluesky","https://bsky.app"],
		["Threads","https://www.threads.net"],
		["pawoo.net","https://pawoo.net"],
		["fedibird.com","https://fedibird.com"],
		["misskey.io","https://misskey.io"],
		["xissmie.xfolio.jp","https://xissmie.xfolio.jp"],
		["misskey.design","https://misskey.design"],
		["nijimiss.moe","https://nijimiss.moe"],
		["sushi.ski","https://sushi.ski"],
	];
	//設定項目ここまで
	$servers[]=["直接入力","direct"];//直接入力の箇所はそのまま。
	$dat['servers'] = $servers;

	$dat['encoded_t'] = filter_input_data('GET',"encoded_t");
	$dat['encoded_u'] = filter_input_data('GET',"encoded_u");
	$dat['sns_server_radio_cookie'] = (string)filter_input_data('COOKIE',"sns_server_radio_cookie");
	$dat['sns_server_direct_input_cookie'] = (string)filter_input_data('COOKIE',"sns_server_direct_input_cookie");

	$dat['admin_pass'] = null;
	//HTML出力
	echo $blade->run(SET_SHARE_SERVER, $dat);
}

//SNSへ共有リンクを送信
function post_share_server(): void {

	$sns_server_radio=(string)filter_input_data('POST',"sns_server_radio",FILTER_VALIDATE_URL);
	$sns_server_radio_for_cookie=(string)filter_input_data('POST',"sns_server_radio");//directを判定するためurlでバリデーションしていない
	$sns_server_radio_for_cookie=($sns_server_radio_for_cookie === 'direct') ? 'direct' : $sns_server_radio;
	$sns_server_direct_input=(string)filter_input_data('POST',"sns_server_direct_input",FILTER_VALIDATE_URL);
	$encoded_t=(string)filter_input_data('POST',"encoded_t");
	$encoded_t=urlencode($encoded_t);
	$encoded_u=(string)filter_input_data('POST',"encoded_u");
	$encoded_u=urlencode($encoded_u);
	setcookie("sns_server_radio_cookie",$sns_server_radio_for_cookie, time()+(86400*30),"","",false,true);
	setcookie("sns_server_direct_input_cookie",$sns_server_direct_input, time()+(86400*30),"","",false,true);
	$share_url='';
	if($sns_server_radio){
		$share_url=$sns_server_radio."/share?text=";
	} elseif($sns_server_direct_input){//直接入力時
		$share_url=$sns_server_direct_input."/share?text=";
		if($sns_server_direct_input==="https://bsky.app"){
			$share_url="https://bsky.app/intent/compose?text=";
		} elseif($sns_server_direct_input==="https://www.threads.net"){
			$share_url="https://www.threads.net/intent/post?text=";
		}
	}
	if(in_array($sns_server_radio,["https://x.com","https://twitter.com"])){
		// $share_url="https://x.com/intent/post?text=";
		$share_url="https://twitter.com/intent/tweet?text=";
	} elseif($sns_server_radio === "https://bsky.app"){
		$share_url="https://bsky.app/intent/compose?text=";
	}	elseif($sns_server_radio === "https://www.threads.net"){
		$share_url="https://www.threads.net/intent/post?text=";
	}
	$share_url.=$encoded_t.'%20'.$encoded_u;
	$share_url = filter_var($share_url, FILTER_VALIDATE_URL) ? $share_url : '';
	if(!$share_url){
		error("SNSの共有先を選択してください。");
	}
	redirect($share_url);
}

//filter_input のラッパー関数
function filter_input_data(string $input, string $key, int $filter=0) {
	// $_GETまたは$_POSTからデータを取得
	$value = null;
	if ($input === 'GET') {
			$value = $_GET[$key] ?? null;
	} elseif ($input === 'POST') {
			$value = $_POST[$key] ?? null;
	} elseif ($input === 'COOKIE') {
			$value = $_COOKIE[$key] ?? null;
	}

	// データが存在しない場合はnullを返す
	if ($value === null) {
			return null;
	}

	// フィルタリング処理
	switch ($filter) {
		case FILTER_VALIDATE_BOOLEAN:
			return  filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
		case FILTER_VALIDATE_INT:
			return filter_var($value, FILTER_VALIDATE_INT);
		case FILTER_VALIDATE_URL:
			return filter_var($value, FILTER_VALIDATE_URL);
		default:
			return $value;  // 他のフィルタはそのまま返す
	}
}

//session開始
function session_sta(): void {

	$session_name = SESSION_NAME;

	if(!isset($_SESSION)){
		ini_set('session.use_strict_mode', 1);
		session_set_cookie_params(
			0,"","",false,true
		);
		session_name($session_name);
		session_start();
		header('Expires:');
		header('Cache-Control:');
		header('Pragma:');
	}
}
