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
  <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
  <?php $this->css('styles/index.css')?>

  <?php $this->hook('template.web-push.script')?>

</head>

<body>

    <header>
        <h1>Получение доступа к Web-Push</h1>
    </header>

    <main>
        <p><button disabled id="access-btn" class="js-push-btn mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect">Необходим доступ к push уведомлениям</button></p>
        <!--p><button disabled class="js-test-btn mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect">Получить тестовое уведомление</button></p-->
        <p id="description">Во всплывающем окне нажмите "Разрешить"</p>
    </main>

    <script type="text/javascript">
        var btn = document.querySelector('#access-btn');
        var descript = document.querySelector('#description');
        
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
                btn.classList.remove('access-denied');
                btn.classList.add('access-granted');
                descript.textContent = 'Вы подписаны на web-push уведомления';

                console.log(JSON.stringify(WebPush.subscriptionData));
                $.ajax({
                  type: "POST",
                  url: <?=json_encode($this->makeURI('/web-push/new-client'))?>,
                  data: {data: JSON.stringify(WebPush.subscriptionData)},
                });
            } else {
                // Если же не разрешено, пытаемся отправить уведомление пользователю
                setTimeout(function(){
                    WebPush.subscribe(function(){
                        checkPush();
                    }, function(err){
                        btn.textContent = 'Доступ к push-уведомлениям не получен';
                        btn.classList.add('access-denied');
                        btn.classList.remove('access-granted');
                        descript.textContent = 'Ошибка: ' + err.message;

                        checkPush();
                    });
                }, 700);
            }
        }
    </script>
</body>
</html>
