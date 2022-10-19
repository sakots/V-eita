# Veita

## Vue.jsのお絵かき掲示板

やっぱりなんかVueでお絵かき掲示板作れる気がするぞ、っておもってまたはじめました。

## ぼんやりとした概要

フロントエンドをVueで、バックエンドをphpで。その間を[axios](https://github.com/axios/axios)でつなぐ。

データベースはsqlite。phpでjson出力したらできそう。 -> [json-encode](https://www.php.net/manual/ja/function.json-encode.php)

参考になりそう。 -> [Vue.js × axios × Firebase で掲示板アプリをつくる - 登録と一覧表示を実装](https://zenn.dev/aono/articles/6094291e3825a1)

## 履歴

### [2022/10/19]

- cliからViteに
  - わからん！CDNに戻すか？ -> cliに。
  - CDNにもどすわ

### [2022/10/12]

- noReitaから移植開始
- cli使ってみる

### [2022/10/02]

- やっぱCDNに戻す
  - CDNでは.envが使えないことを確認

### [2022/08/27]

- cli使わないといけないやつかなこれ。
- ということでそうした。

### [2022/08/26]

- 開始
