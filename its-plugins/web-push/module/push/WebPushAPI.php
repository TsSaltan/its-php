<?php 	
namespace tsframe\module\push;

use Minishlink\WebPush\WebPush;
use tsframe\Config;
use tsframe\Http;
use tsframe\exception\BaseException;
use tsframe\module\push\WebPushClient;
use tsframe\module\push\WebPushQuery;

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
	 * @param WebPushClient|WebPushUser $client  Клиент, которому будет отправлено сообщение
	 * @param array|string        $payload Данные для отправки (массив или json-строка), необходимые поля: body, title, link, icon
	 */
	public function addPushMessage(object $client, $payload){
		if($client instanceof WebPushClient){
			$subscript = $client->getQuery()->getSubscription();
		}
		elseif($client instanceof WebPushQuery){
			$subscript = $client->getSubscription();
		}
		else {
			throw new BaseException('Invalid client input');
		}

		$payload = is_array($payload) ? json_encode($payload) : $payload;
		$this->webPush->sendOneNotification(
	        $subscript,
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