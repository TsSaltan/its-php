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

	const DEFAULT_ICON = 'https://raw.githubusercontent.com/GoogleChromeLabs/web-push-codelab/master/app/images/icon.png';

	private $authKey;
	private $p256Key;
	private $endpoint;

	public static function ofString(string $json){
		$data = json_decode($json, true);
		if(isset($data['endpoint']) && isset($data['keys']['auth']) && isset($data['keys']['p256dh'])){
			return new self($data['endpoint'], $data['keys']['p256dh'], $data['keys']['auth']);
		}

		throw new BaseException('Invalid input JSON string', 0, ['input_string' => $json]);
	}

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

	public function send(string $body, string $title, string $link, ?string $icon){
		if(strlen($icon) == 0) $icon = self::DEFAULT_ICON;
		
		$api = new WebPushAPI;
		$api->addPushMessage($this, ['body' => $body, 'title' => $title, 'link' => $link, 'icon' => $icon]);
		return $api->send();
	}
}