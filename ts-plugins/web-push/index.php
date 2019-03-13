<?
/**
 * Отправка Push сообщений в браузер
 *
 * @link https://web-push-codelab.glitch.me/
 * @link https://developers.google.com/web/fundamentals/codelabs/push-notifications/
 * @link https://github.com/web-push-libs/web-push-php
 * @link https://github.com/GoogleChromeLabs/web-push-codelab
 */

namespace tsframe;

use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;
use tsframe\App;
use tsframe\Config;
use tsframe\Http;
use tsframe\module\menu\MenuItem;
use tsframe\module\user\UserAccess;
use tsframe\view\TemplateRoot;

Hook::registerOnce('plugin.install', function(){
	Plugins::required('geodata');
	
	return [
		PluginInstaller::withKey('push.publicKey')
					->setType('text')
					->setDescription("<u>Публичный</u> ключ для Web Push (<a href='https://web-push-codelab.glitch.me/' target='_blank'>отсюда</a>)"),		

		PluginInstaller::withKey('push.privateKey')
					->setType('text')
					->setDescription("<u>Приватный</u> ключ для Web Push"),

		PluginInstaller::withKey('push.sender')
					->setType('text')
					->setDescription("URL aдрес отправителя для Web Push. Может быть вида <u>mailto:email@mail.com</u> или <u>https://mysite.com</u>"),

		PluginInstaller::withKey('access.webpush')
					->setType('select')
					->setDescription("Права доступа: доступ к базе данных web-push клиентов")
					->setDefaultValue(UserAccess::Admin)
					->setValues(array_flip(UserAccess::getArray())),
	];
});

/**
 * Загрузка плагина
 */
Hook::registerOnce('plugin.load', function(){
	TemplateRoot::add('index', __DIR__ . DS . 'template' . DS . 'index');
	TemplateRoot::add('dashboard', __DIR__ . DS . 'template' . DS . 'dashboard');
});

Hook::registerOnce('menu.render.dashboard-admin-sidebar', function(MenuItem $menu){
	$menu->add(new MenuItem('Web-Push клиенты', ['url' => Http::makeURI('/dashboard/web-push-clients'), 'fa' => 'commenting', 'access' => UserAccess::getAccess('webpush')]));
});

Hook::registerOnce('app.start', function(){
/*	
	$endpoint = "https://fcm.googleapis.com/fcm/send/egEAXPLM1EQ:APA91bG1u0MihniKwXPQGTvDG7DTJCsx4QlnWAOkDIFx1zSEum_cVCdZzZhOmLsxfkmg37bpQkhO5VlkJUr8W6H729lXh_FXbzUQ1FsXqBlnbhFiqxvXCr9iaimxcV25efyLSxVZCyx6";
	$p256dh = "BKb1LR8BcyImwY5qFoH4FQCejLOYEFGuL0PagLiYH2s0tJkH34sYOlt3rEnOFghi-CfMNy_oZyaQg3-rf1vwSuo";
	$auth = "flSDbsJ-KnmuSYqlwaEMAg";

	$subscription = Subscription::create([ // this is the structure for the working draft from october 2018 (https://www.w3.org/TR/2018/WD-push-api-20181026/) 
              "endpoint" => $endpoint,
              "keys" => [
                  'p256dh' => $p256dh,
                  'auth' => $auth 
              ],
          ]);
    $payload = json_encode(['message' => 'Hello from server', 'title' => 'hello world!']);

    $auth = [
	    'VAPID' => [
	        'subject' => 'mailto:tssaltan@gmail.com', // can be a mailto: or your website address
	        'publicKey' => 'BA_xZ25u4B1faT95glQ09nettLkY2pV2RLhq4PnzHG9uq4cReUD87rW5XAD7JtsjkLgYqz9J0GzCTIQzBsCCIX0', // (recommended) uncompressed public key P-256 encoded in Base64-URL
	        'privateKey' => 'rXzfcFuPCoUmL7f3vqo6lI_XsnFMS48K7KkARs_vS40', // (recommended) in fact the secret multiplier of the private key encoded in Base64-URL
	    ],
	];


    $webPush = new WebPush($auth);
    $a = $webPush->sendNotification(
        $subscription,
        $payload, 
        true
    );

    foreach ($webPush->flush() as $report) {
	    $endpoint = $report->getRequest()->getUri()->__toString();

	    if ($report->isSuccess()) {
	        echo "[v] Message sent successfully for subscription {$endpoint}.";
	    } else {
	        echo "[x] Message failed to sent for subscription {$endpoint}: {$report->getReason()}";
	    }
	}

    var_dump($a);
    die( );*/
});