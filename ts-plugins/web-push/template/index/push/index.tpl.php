<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>Push Codelab</title>

  <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
  <link rel="stylesheet" href="https://code.getmdl.io/1.2.1/material.indigo-pink.min.css">
  <script defer src="https://code.getmdl.io/1.2.1/material.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
  <?$this->css('push/styles/index.css')?>
</head>

<body>

    <header>
        <h1>Web-Push Testing</h1>
    </header>

    <main>
        <p><button disabled class="js-push-btn mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect">Уведомления не поддерживаются</button></p>
        <!--p><button disabled class="js-test-btn mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect">Получить тестовое уведомление</button></p-->
    </main>

    <script src="https://code.getmdl.io/1.2.1/material.min.js"></script>

    <?$this->js('push/scripts/main.js')?>
    <script type="text/javascript">
        var btn = document.querySelector('.js-push-btn');

        WebPush.publicKey = "<?=$publicKey?>";
        WebPush.init(function(){
            checkPush();
        });

        /**
         * Функция для проверки доступности пушей
         */
        function checkPush(){
            if(WebPush.isSubscribed){
                // Если разрешено - шлём ключи на сервер
                btn.textContent = 'Доступ к уведомлениям получен';
                console.log(JSON.stringify(WebPush.subscriptionData));
                $.ajax({
                  type: "POST",
                  url: <?=json_encode($this->makeURI('/web-push/new-client'))?>,
                  data: {data: JSON.stringify(WebPush.subscriptionData)},
                });
            } else {
                // Если же не разрешено, пытаемся отправить уведомление пользователю
                btn.textContent = 'Доступ к уведомлениям НЕ получен';
                setTimeout(function(){
                    alert('м?');
                    WebPush.subscribe(function(){
                        checkPush();
                    }, function(){
                        btn.textContent = 'Доступ к уведомлениям ЗАПРЕЩЁН';

                    });
                }, 1500);
            }
        }
    </script>
</body>
</html>
