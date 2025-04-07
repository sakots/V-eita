<?php
//--------------------------------------------------
//  V-eita v0.0.0～
//  by sakots >> https://oekakibbs.moe/
//  V-eitaの設定ファイルです。
//--------------------------------------------------

/* ---------- 最初に設定する項目 ---------- */
//管理者パスワード
//必ず変更してください ! admin_pass のままではプログラムは動作しません !
$admin_pass = 'admin_pass';

//管理者名
//投稿の際に名前がこれでパスワードが管理パスのときに、名前のあとに管理者マークが付きます
$admin_name = '管理人';

//最大スレッド数
//古いスレッドから順番に消えます
define('LOG_MAX_T', 300);

//テーマ(テンプレート)のディレクトリ名。'/'は不要。
//別のディレクトリにしたい場合は設定してください。
//初期値は monoreita です。色選択のないmonorもあります。
define('THEMEDIR', 'monoreita');

//設置URL phpのあるディレクトリの'/'まで
//シェアボタンなどで使用
define('BASE', 'https://example.com/veita/');

//掲示板のタイトル（<title>とTOP）
define('TITLE', 'お絵かき掲示板');

//「ホーム」へのリンク
// 自分のサイトにお絵かき掲示板がある、という慣習からのものです。
// 自分のサイトのURL（絶対パスも可）をどうぞ。
define('HOME', '../');

// ChickenPaintを使う 使う:1 使わない:0
define('USE_CHICKENPAINT', 1);
// PaintBBS NEOはどの設定でも起動します。

/*----------絶対に設定が必要な項目はここまでです。ここから下は必要に応じて。----------*/

/* -------- データベース名 -------- */

//初期設定のままの場合、veita.dbとなります。
//拡張子は.dbで固定です。
define('DB_NAME', 'veita');

/* ---------- SNS連携 ---------- */

//シェアボタンを表示する する:1 しない:0
//設置場所のURL BASE で設定したurlをもとにリンクを作成
define('SHARE_BUTTON', 0);

/* ---------- NSFW画像 ---------- */

//NSFW画像機能を使う 使う:1 使わない:0
//自分の書いた画像に、表示するまでぼかしを掛けることができます
define('USE_NSFW', 1);

/* ---------- 個人識別 ---------- */

//IDを表示する する:1 しない:0
//違う名前でも同一人物だとわかります。
define('DISPLAY_ID', 1);

//ID生成の種
define('ID_SEED', 'IDの種');

//ID変更周期 なし(IDはずっと同じ):0 1日:1 1週間:2 1か月:3 1年:4
define('ID_CYCLE', 2);

//偽管理者用キャップ
//管理者名で投稿し、管理パス以外だと名前の後にこれがつきます。
define('ADMIN_CAP', '(ではない)');

/* ---------- スパム対策 ---------- */

//拒絶する文字列
$bad_string = array("irc.s16.xrea.com", "著作権の侵害", "未承諾広告");

//使用できない名前
$bad_name = array("ブランド", "通販", "販売", "口コミ");

//全角半角スペース改行を考慮する必要はありません
//スペースと改行を除去した文字列をチェックします

//設定しないなら ""で。
// $bad_name = array("");

//初期設定では「"通販"を含む名前」の投稿を拒絶します
//ここで設定したNGワードが有効になるのは「名前」だけです
//本文に「通販で買いました」と書いて投稿する事はできます

//名前以外の項目も設定する場合は
//こことは別の設定項目
//拒絶する文字列で

//AとBが両方あったら拒絶。
$bad_str_A = array("激安", "低価", "コピー", "品質を?重視", "大量入荷");
$bad_str_B = array("シャネル", "シュプリーム", "バレンシアガ", "ブランド");

//正規表現を使うことができます。
//全角半角スペース改行を考慮する必要はありません
//スペースと改行を除去した文字列をチェックします

//設定しないなら ""で。
//$bad_str_A = array("");
//$bad_str_B = array("");

//AとBの単語が2つあったら拒絶します。
//初期設定では「ブランド品のコピー」という投稿を拒絶します。
//1つの単語では拒絶されないので「コピー」のみ「ブランド」のみの投稿はできます。

//一つの単語で拒絶する場合は
//こことは別の設定項目
//拒絶する文字列で

//本文に日本語がなければ拒絶
define('USE_JAPANESE_FILTER', 1);

//本文へのURLの書き込みを禁止する する:1 しない:0
define('DENY_COMMENTS_URL', 0);
//管理者は設定に関わらず書き込み可

//指定した日数を過ぎたレスボタンを閉じる
//define('ELAPSED_DAYS','0');
//設定しないなら '0'で。レスボタンを閉じません。
//define('ELAPSED_DAYS','365');
//	↑ 365日
//でスレ立てから1年以上経過したスレッドに返信できなくなります。
define('ELAPSED_DAYS', 365);

//拒絶するファイルのmd5
//…使う？？
$bad_file = array("dummy", "dummy2");

//拒絶するホスト
$bad_ip = array("dummy.example.com", "198.51.100.0");

//ペイント画面の暗号化キー
//phpの内部で処理するので覚えておく必要はありません。
//管理パスとは別なものです。無作為な英数字を入れてください。
//あまり頻繁に変えないように
define('CRYPT_PASS', '0qYzf1x6nyN4gS1');

// 言語設定
define('LANG', 'Japanese');

// タイムゾーン
define('DEFAULT_TIMEZONE', 'Asia/Tokyo');

//ユーザー削除権限 (0:不可 1:許可)
//※treeのみを消して後に残ったlogは管理者のみ削除可能
define('USER_DEL', 1);

/* ---------- お絵かきディレクトリ設定 ---------- */

//複数のお絵描き掲示板を管理する際に便利です。

//neoのディレクトリ。index.phpから見て
define('NEO_DIR', 'neo/');

//chickenPaintのディレクトリ。index.phpから見て
define('CHICKEN_DIR', 'chickenpaint/');

/* ---------- お絵かきアプレット設定(neo) ---------- */

//アンドゥの回数
define('UNDO', 90);

//アンドゥを幾つにまとめて保存しておくか
define('UNDO_IN_MG', 45);

//セキュリティ関連－URLとクリック数かタイマーのどちらかが設定されていれば有効
//※アプレットのreadmeを参照し、十分テストした上で設定して下さい
//セキュリティクリック数。設定しないなら''で
define('SECURITY_CLICK', '');
//セキュリティタイマー(単位:秒)。設定しないなら''で
define('SECURITY_TIMER', '');
//セキュリティにヒットした場合の飛び先
define('SECURITY_URL', './security_c.html');

//続きを描くときのセキュリティ。利用しないなら両方''で
//続きを描くときのセキュリティクリック数。設定しないなら''で
define('C_SECURITY_CLICK', '');
//続きを描くときのセキュリティタイマー(単位:秒)。設定しないなら''で
define('C_SECURITY_TIMER', '');

/* ---------- メイン設定 ---------- */

//画像と動画データ保存ディレクトリ。index.phpから見て
define('IMG_DIR', 'images/');

//投稿容量制限 KB
define('MAX_KB', 2000);

//投稿サイズ（これ以上はサイズを縮小
define('MAX_W', 800);  //幅 px
define('MAX_H', 800);  //高さ px

//名前の制限文字数。半角で
define('MAX_NAME', 100);

//メールアドレスの制限文字数。半角で
define('MAX_EMAIL', 100);

//題名の制限文字数。半角で
define('MAX_SUB', 100);

//URLの制限文字数。半角で
define('MAX_URL', 100);

//本文の制限文字数。半角で
define('MAX_COM', 1000);

//1ページに表示する記事
define('PAGE_DEF', 10);

//1スレ内のレス表示件数
//レスがこの値より多いと省略されます
//返信画面で全件表示されます
define('DSP_RES', 7);

//そろそろ消える表示のボーダー。最大ログ数からみたパーセンテージ
//未実装
define('LOG_LIMIT', 94);

//カタログモードで表示する記事の数
define('CATALOG_N', 30);

//クッキー保存日数
define('SAVE_COOKIE', 7);

//日付フォーマット
define('DATE_FORMAT', 'Y/m/d H:i:s');

//強制sageレス数( 0 ですべてsage)
define('MAX_RES', 20);

//URLを自動リンクする する:1 しない:0
define('AUTOLINK', 1);

//名前を必須にする する:1 しない:0
define('USE_NAME', 0);
define('DEF_NAME', '名無しさん');  //未入力時の名前

//絵を描いた時は本文を必須にする する:1 しない:0
//(レス及び編集モードのときは必須)
define('USE_COM', 0);
define('DEF_COM', '本文無し');  //未入力時の本文

//題名を必須にする する:1 しない:0
define('USE_SUB', 0);
define('DEF_SUB', '無題');  //未入力時の題名

//レス時にスレ題名を引用する する:1 しない:0
define('USE_RE_SUB', 1);

//ハッシュタグリンク機能を使う 使う:1 使わない:0
define('USE_HASHTAG', 1);

//フォーム下の追加お知らせ <li></li>で囲まれます。
//(例) $add_info = array('まだまだ開発中…','バグがあったら教えてね');
//設定しないなら $add_info = array(""); で
$add_info = array("<a href='https://github.com/sakots/V-eita'>ソースはこちら</a>", "まだまだ開発中…バグがあったら教えてね。");

/* ---------- お絵かき設定 ---------- */

//お絵かき機能を使用する お絵かきのみ

//一時ファイルディレクトリ
define('TEMP_DIR', 'tmp/');

//一時ファイルディレクトリ内のファイル有効期限(日数)
define('TEMP_LIMIT', 14);

//お絵描き最大サイズ（これ以上は強制でこの値
//最小値は幅、高さともに 300 固定です
define('P_MAX_W', 800);  //幅
define('P_MAX_H', 800);  //高さ

//お絵描きデフォルトサイズ
define('P_DEF_W', 400);  //幅
define('P_DEF_H', 400);  //高さ

//描画時間の表示 する:1 しない:0
define('DSP_PAINT_TIME', 1);

//パレットデータファイル名
define('PALETTE_FILE', 'palette.txt');

//パレットデータファイル切り替え機能を使用する する:1 しない:0
//切り替えるパレットデータファイルが用意できない場合は しない:0。
define('USE_SELECT_PALETTES', 1);

//パレットデータファイル切り替え機能を使用する する:1 の時のパレットデーターファイル名
$pallets_dat = array(['標準', 'palette.txt'], ['PCCS_HSL', 'p_PCCS.txt'], ['マンセルHV/C', 'p_munsellHVC.txt']);

//動画機能を使用する する:1 しない:0
define('USE_ANIME', 1);

//動画記録デフォルトスイッチ ON:1 OFF:0
define('DEF_ANIME', 1);

//動画再生スピード 超高速:-1 高速:0 中速:10 低速:100 超低速:1000
define('PCH_SPEED', 0);

//「続きから書く」を使用する する:1 しない:0
define('USE_CONTINUE', 1);

//「続きから書く」で新規投稿する時にも削除キーが必要 必要:1 不要:0
//不要:0 で新規投稿なら誰でも続きを描く事ができるようになります
//みんなで塗り絵とかできます
define('CONTINUE_PASS', 0);

/* ------------- トラブルシューティング ------------- */

//問題なく動作している時は変更しない。

//画像やHTMLファイルのパーミッション。
define('PERMISSION_FOR_DEST', 0606); //初期値 0606
//ブラウザから直接呼び出さないログファイルのパーミッション(たぶん使ってない)
define('PERMISSION_FOR_LOG', 0600); //初期値 0600
//画像や動画ファイルを保存するディレクトリのパーミッション
define('PERMISSION_FOR_DIR', 0707); //初期値 0707

//csrfトークンを使って不正な投稿を拒絶する する:1 しない:0
//する:1 にすると外部サイトからの不正な投稿を拒絶することができます
define('CHECK_CSRF_TOKEN', 1);

/* ------------- 変更してほしくないところ ------------- */
//スクリプト名
define('PHP_SELF', 'index.php');

/* -------------------- */

//編集したときの目印
//※記事を編集したら日付の後ろに付きます
define('UPDATE_MARK', ' *');

//名前引用時の「さん」
define('A_NAME_SAN', 'さん');

//「そうだね」
define('SODANE', 'そうだね');

//描画時間の書式
//※日本語だと、"1日1時間1分1秒"
//※英語だと、"1day 1hr 1min 1sec"
define('PTIME_D', '日');
define('PTIME_H', '時間');
define('PTIME_M', '分');
define('PTIME_S', '秒');

//＞が付いた時の書式
//※RE_STARTとRE_ENDで囲むのでそれを考慮して
//ここは変更せずにcssで設定するの推奨
define('RE_START', '<span class="resma">');
define('RE_END', '</span>');

//エラーメッセージ
define('MSG001', "該当記事がみつかりません[Log is not found.]");
define('MSG002', "絵が選択されていません[Picture has not been selected.]");
define('MSG003', "アップロードに失敗しました[It failed in up-loading.]<br>サーバーがサポートしていない可能性があります[There is a possibility that the server doesn't support it.]");
define('MSG004', "アップロードに失敗しました[It failed in up-loading.]<br>画像ファイル以外は受け付けません[It is not accepted excluding the picture file.]");
define('MSG005', "アップロードに失敗しました[It failed in up-loading.]<br>同じ画像がありました[The same image existed.]");
define('MSG006', "不正な投稿です[Please do not do an illegal contribution.]<br>POST以外での投稿は受け付けません[The contribution excluding 'POST' is not accepted.]");
define('MSG007', "画像がありません[no image.]");
define('MSG008', "何か書いて下さい[write something.]");
define('MSG009', "名前がありません[no name.]");
define('MSG010', "題名がありません[no subject]");
define('MSG011', "本文が長すぎます[comment is too long.]");
define('MSG012', "名前が長すぎます[name is too long.]");
define('MSG013', "メールアドレスが長すぎます[email is too long.]");
define('MSG014', "題名が長すぎます[subject is too long.]");
define('MSG015', "異常です[Abnormality]");
define('MSG016', "拒絶されました[was rejected.]<br>そのHOSTからの投稿は受け付けません[Post from the 'HOST' is not accepted.]");
define('MSG017', "ＥＲＲＯＲ！[Error]　公開ＰＲＯＸＹ規制中！！[Open-PROXY is limited.](80)");
define('MSG018', "ＥＲＲＯＲ！[Error]　公開ＰＲＯＸＹ規制中！！[Open-PROXY is limited.](8080)");
define('MSG019', "ログの読み込みに失敗しました[It failed in reading the log.]");
define('MSG020', "連続投稿はもうしばらく時間を置いてからお願い致します[Please wait for a continuous post for a while.]");
define('MSG021', "画像連続投稿はもうしばらく時間を置いてからお願い致します[Please wait for a continuous post of the image for a while.]");
define('MSG022', "このコメントで一度投稿しています[Post once by this comment.]<br>別のコメントでお願い致します[Please put another comment.]");
define('MSG023', "ツリーの更新に失敗しました[It failed in the renewal of the tree.]");
define('MSG024', "ツリーの削除に失敗しました[It failed in the deletion of the tree.]");
define('MSG025', "スレッドがありません[no thread.]");
define('MSG026', "スレッドが最後の1つなので削除できません[thread is the last one, not delete.]");
define('MSG027', "削除に失敗しました(ユーザー)[failed in deletion.(User)]");
define('MSG028', "該当記事が見つからないかパスワードが間違っています[article is not found or password is wrong.]");
define('MSG029', "パスワードが違います[password is wrong.]");
define('MSG030', "削除に失敗しました(管理者権限)[failed in deletion.(Admin)]");
define('MSG031', "記事Noが未入力です[Please input No.]");
define('MSG032', "拒絶されました[was rejected.]<br>不正な文字列があります[illegal character string.]");
define('MSG033', "削除に失敗しました[failed in deletion.]<br>ユーザーに削除権限がありません[user doesn't have deletion authority.]");
define('MSG034', "アップロードに失敗しました[It failed in up-loading.]<br>規定の画像容量をオーバーしています[size over is picture file.]");
define('MSG035', "何か日本語で書いてください[Comment should have at least some Japanese characters.]");
define('MSG036', "本文にそのURLを書く事はできません。[This URL can not be used in text.]");
define('MSG037', "予備");
define('MSG038', "予備");
define('MSG039', "予備");
define('MSG040', "予備");

/* ------------- コンフィグ互換性管理 ------------- */

define('CONF_VER', 250403);
