<?php 	
namespace tsframe\module\push;

use Minishlink\WebPush\WebPush;
use tsframe\Config;
use tsframe\Http;
use tsframe\module\push\WebPushClient;

/**
 * API для отправки пушей
 */
class WebPushAPI {
	/**
	 * Получить публичный ключ
	 * @return string
	 */
	public static function getPublicKey(): ?string {
		return Config::get('push.publicKey');
	}

	/**
	 * Получить приватный ключ
	 * @return string
	 */
	private static function getPrivateKey(): ?string {
		return Config::get('push.privateKey');
	}

	/**
	 * @var WebPush
	 */
	private $webPush;

	public function __construct(){
		// Генерируем запрос с VAPID авторизацией
		$this->webPush = new WebPush([
	    	'VAPID' => [
		        'subject' => Http::makeURI('/', [], 'from=push'),
	        	'publicKey' => self::getPublicKey(),
	        	'privateKey' => self::getPrivateKey(),
	        ]
	    ]);
	}

	/**
	 * Добавить сообщение для отправки
	 * @param WebPushClient $client  Клиент, которому будет отправлено сообщение
	 * @param array|string        $payload Данные для отправки (массив или json-строка), необходимые поля: body, title, link, icon
	 */
	public function addPushMessage(WebPushClient $client, $payload){
		$payload = is_array($payload) ? json_encode($payload) : $payload;
		$this->webPush->sendNotification(
	        $client->getSubscription(),
        	$payload
    	);
	}

	/**
	 * Отправить пуши клментам
	 * @return array Массив с ответами от серверов
	 */
	public function send(): array {
		$results = [];
		foreach ($this->webPush->flush() as $report) {
		    $endpoint = $report->getRequest()->getUri()->__toString();
		    if ($report->isSuccess()) {
		        $results[] = ['endpoint' => $endpoint, 'result' => 'success'];
		    } else {
		        $results[] = ['endpoint' => $endpoint, 'result' => 'error', 'error' => $report->getReason()];
		    }
		}

		return $results;
	}
}