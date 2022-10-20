<!DOCTYPE html>
<?
  require(__DIR__ . '/veita.php');
?>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= TITLE ?></title>
</head>
<body>
  
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