<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>Web-Push Access</title>

  <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
  <link rel="stylesheet" href="https://code.getmdl.io/1.2.1/material.indigo-pink.min.css">
  <script defer src="https://code.getmdl.io/1.2.1/material.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
  <?php $this->css('push/styles/index.css')?>
</head>

<body>

    <header>
        <h1>Получение доступа к Web-Push</h1>
    </header>

    <main>
        <p><button disabled id="access-btn" class="js-push-btn mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect">Уведомления не поддерживаются</button></p>
        <!--p><button disabled class="js-test-btn mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect">Получить тестовое уведомление</button></p-->
        <p id="description">Во всплывающем окне нажмите "Разрешить"</p>
    </main>

    <script src="https://code.getmdl.io/1.2.1/material.min.js"></script>

    <?php $this->js('push/scripts/main.js')?>
    <script type="text/javascript">
        var btn = document.querySelector('#access-btn');
        var descript = document.querySelector('#description');

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
                btn.textContent = 'Доступ к push-уведомлениям получен';
                btn.className += ' access-granted';
                descript.textContent = 'Вы подписаны на web-push уведомления';

                console.log(JSON.stringify(WebPush.subscriptionData));
                $.ajax({
                  type: "POST",
                  url: <?=json_encode($this->makeURI('/web-push/new-client'))?>,
                  data: {data: JSON.stringify(WebPush.subscriptionData)},
                });
            } else {
                // Если же не разрешено, пытаемся отправить уведомление пользователю
                btn.textContent = 'Нужен доступ к push уведомлениям';
                setTimeout(function(){
                    WebPush.subscribe(function(){
                        checkPush();
                    }, function(err){
                        btn.textContent = 'Доступ к push-уведомлениям не получен';
                        btn.className += ' access-denied';
                        descript.textContent = 'Ошибка: ' + err.message;
                    });
                }, 1500);
            }
        }
    </script>
</body>
</html>
