# V-eita

## Vue.jsのお絵かき掲示板

やっぱりなんかVueでお絵かき掲示板作れる気がするぞ、っておもってまたはじめました。

## ぼんやりとした概要

フロントエンドをVueで、バックエンドをphpで。その間を[axios](https://github.com/axios/axios)でつなぐ。

データベースはsqlite。phpでjson出力したらできそう。 -> [json-encode](https://www.php.net/manual/ja/function.json-encode.php)

---

## 履歴

### [2025/04/30]

- テンプレート分割

### [2025/04/06]

- レス表示できた

### [2025/04/05]

- css
- ヘッダーあたりできた
- スレッドまで表示できた

### [2025/04/03]

- またいちからやりなおし → バックエンドからデータを渡すまでできた

### [2023/05/27]

- jsonデータの出力と取得に成功

### [2023/05/12]

- いちからやりなおし

### [2022/10/21]

json_encodeがうまくいかない

### [2022/10/20]

- これBladeも使おうかなあ -> なんとなくわかってきた

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

---

## Recommended IDE Setup

[VSCode](https://code.visualstudio.com/) + [Volar](https://marketplace.visualstudio.com/items?itemName=Vue.volar) (and disable Vetur) + [TypeScript Vue Plugin (Volar)](https://marketplace.visualstudio.com/items?itemName=Vue.vscode-typescript-vue-plugin).

## Customize configuration

See [Vite Configuration Reference](https://vitejs.dev/config/).

## Project Setup

```sh
npm install
```

### Compile and Hot-Reload for Development

```sh
npm run dev
```

### Compile and Minify for Production

```sh
npm run build
```
