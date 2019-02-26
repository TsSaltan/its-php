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
        <p><button disabled class="js-test-btn mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect">Получить тестовое уведомление</button></p>

        <section class="subscription-details js-subscription-details is-invisible">
            <p>Once you've subscribed your user, you'd send their subscription to your
            server to store in a database so that when you want to send a message
            you can lookup the subscription and send a message to it.</p>
            <p>To simplify things for this code lab copy the following details
            into the <a href="https://web-push-codelab.glitch.me//">Push Companion
            Site</a> and it'll send a push message for you, using the application
            server keys on the site - so make sure they match.</p>
            <pre><code class="js-subscription-json"></code></pre>
        </section>
    </main>

    <script src="https://code.getmdl.io/1.2.1/material.min.js"></script>

    <?$this->js('push/scripts/main.js')?>
    <script type="text/javascript">
        var btn = document.querySelector('.js-push-btn');
        var test = document.querySelector('.js-test-btn');

        WebPush.publicKey = "BA_xZ25u4B1faT95glQ09nettLkY2pV2RLhq4PnzHG9uq4cReUD87rW5XAD7JtsjkLgYqz9J0GzCTIQzBsCCIX0";
        WebPush.init(function(){
            checkPush();
        });

        function checkPush(){
            if(WebPush.isSubscribed){
                btn.textContent = 'Доступ к уведомлениям получен';
                test.disabled = false;
            } else {
                btn.textContent = 'Доступ к уведомлениям НЕ получен';
                test.disabled = true;

                setTimeout(function(){
                    WebPush.subscribe(function(){
                        checkPush();
                    }, function(){
                        btn.textContent = 'Доступ к уведомлениям ЗАПРЕЩЁН';
                        test.disabled = true;
                    });
                }, 1500);
            }
        }

        test.addEventListener('click', function(){
            console.log(JSON.stringify(WebPush.subscriptionData));
            $.ajax({
              type: "POST",
              url: <?=json_encode($this->makeURI('/web-push/send-push'))?>,
              data: {data: JSON.stringify(WebPush.subscriptionData)},
            });
        });

    </script>
</body>
</html>
