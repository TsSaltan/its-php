<?php 	
namespace tsframe\module\push;

use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;
use tsframe\Config;
use tsframe\exception\BaseException;
use tsframe\exception\GeoException;
use tsframe\module\Geo\GeoIP;
use tsframe\module\Geo\Location;
use tsframe\module\IP;
use tsframe\module\PaginatorInterface;
use tsframe\module\database\Database;
use tsframe\module\push\WebPushAPI;
use tsframe\module\user\SingleUser;

/**
 * Одиночный WebPush-запрос
 */
class WebPushQuery {

	private $authKey;
	private $p256Key;
	private $endpoint;

	public function __construct(string $endpoint, string $p256Key, string $authKey){
		$this->authKey = $authKey;
		$this->p256Key = $p256Key;
		$this->endpoint = $endpoint;
	}

	/**
	 * Получить JSON-строку с oush ключами
	 * @return string JSON-строка
	 */
	public function getPushKeys(): string {
		return json_encode(['endpoint' => $this->endpoint, 'keys' => ['p256dh' => $this->p256Key, 'auth' => $this->authKey]]);
	}

	/**
	 * Генерация подписи по ключам клиента
	 * @return Subscription
	 */
	public function getSubscription(): Subscription {
		// this is the structure for the working draft from october 2018 (https://www.w3.org/TR/2018/WD-push-api-20181026/) 
		return Subscription::create([ 
        	"endpoint" => $this->endpoint,
            "keys" => [
            	"p256dh" => $this->p256Key,
                "auth" => $this->authKey 
            ],
        ]);
	}
}