<?php 
/**
 * Отправка Push сообщений в браузер
 *
 * @link https://web-push-codelab.glitch.me/
 * @link https://developers.google.com/web/fundamentals/codelabs/push-notifications/
 * @link https://github.com/web-push-libs/web-push-php
 * @link https://github.com/GoogleChromeLabs/web-push-codelab
 *
 * Использование WebPush скрипта:
 * 1. Вызвать в head шаблона хук template.web-push.script
 * $tpl->hook('template.web-push.script');
 * 
 * 2. После инициализации скриптов выполнить скрипт:
<script type="text/javascript">
 	WebPush.init(function(){
        checkPush();
    });

    function checkPush(){
        if(WebPush.isSubscribed){
            // Если пуши разрешены, получаем ключи доступа
             console.log(JSON.stringify(WebPush.subscriptionData));
        } else {
            // Если нет доступа к пушам, через некоторое время отправляем пользователю запрос
            setTimeout(function(){
                WebPush.subscribe(function(){
                    checkPush();
                }, function(err){
					console.log('WebPush access error: ' + err.message);
                    checkPush(); // Try again
                });
            }, 1000);
        }
    }
</script>
 */

namespace tsframe;

use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;
use tsframe\App;
use tsframe\Config;
use tsframe\Http;
use tsframe\module\menu\MenuItem;
use tsframe\module\push\WebPushAPI;
use tsframe\module\push\WebPushQueue;
use tsframe\module\scheduler\Scheduler;
use tsframe\module\scheduler\Task;
use tsframe\module\user\UserAccess;
use tsframe\view\HtmlTemplate;
use tsframe\view\TemplateRoot;

Hook::registerOnce('plugin.install', function(){	
	return [
		PluginInstaller::withKey('push.publicKey')
					->setType('text')
					->setDescription("<u>Публичный</u> ключ для Web Push (<a href='https://web-push-codelab.glitch.me/' target='_blank'>отсюда</a>)"),		

		PluginInstaller::withKey('push.privateKey')
					->setType('text')
					->setDescription("<u>Приватный</u> ключ для Web Push")
	];
});

/**
 * Загрузка плагина
 */
Hook::registerOnce('app.init', function(){
	TemplateRoot::addDefault(__DIR__ . DS . 'template');
	TemplateRoot::add('web-push', __DIR__ . DS . 'template' . DS . 'web-push');
});

Hook::register('template.web-push.script', function(HtmlTemplate $tpl){
	$publicKey = WebPushAPI::getPublicKey();
	$tpl->js('web-push/main.js');
	?>
	<script type="text/javascript">
		WebPush.publicKey = "<?=$publicKey?>";
        WebPush.swPath = "<?=$tpl->makeURI('/service-worker.js')?>";
	</script>
    <?php
});