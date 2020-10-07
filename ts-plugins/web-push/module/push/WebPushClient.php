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
 * Работа с Push-клиентами
 */
class WebPushClient implements PaginatorInterface {
	/**
	 * @return array [['user' => (object) SingleUser, 'clients' => WebPushClient[] ], ... ]
	 */
	public static function getUsersClients(): array {
		// @todo
	}

	/**
	 * Найти id клиентов по гео параметрам
	 * @param  string|null $country 
	 * @param  string|null $city    
	 * @return array
	 */
	public static function findIdsByParams(?string $country = null, ?string $city = null): array {
		$countryQuery = null;
		$cityQuery = null;
		$args = [];
		if(!is_null($country) && $country !== '*'){
			$countryQuery = ' AND `country` = :country';
			$args['country'] = $country;
		}

		if(!is_null($city) && $city !== '*'){
			$cityQuery = ' AND `city` = :city';
			$args['city'] = $city;
		}

		$q = Database::exec('SELECT `id` FROM `web-push-clients` WHERE `id` > 0'.$countryQuery.$cityQuery, $args)->fetch();
		return array_column($q, 'id');
	}

	/**
	 * Получить названия стран и горовод, которые есть в базе клиентов
	 * @return array
	 */
	public static function getUniqueValues(): array {
		$countries = array_column(Database::exec('SELECT DISTINCT `country` FROM `web-push-clients` WHERE `country` IS NOT NULL ORDER BY `country` ASC')->fetch(), 'country');
		$cities = array_column(Database::exec('SELECT DISTINCT `city` FROM `web-push-clients` WHERE `city` IS NOT NULL ORDER BY `city` ASC')->fetch(), 'city');

		return [
			'country' => $countries, 
			'city' => $cities, 
		];
	}

	/**
	 * Получить количество строк в базе
	 * @override PaginatorInterface
	 * @return int
	 */
	public static function getDataSize(): int {
		return Database::exec('SELECT COUNT(`id`) c FROM `web-push-clients`')->fetch()[0]['c'];
	}

	/**
	 * Получить срез данных, соответствующих данной странице
	 * @override PaginatorInterface
	 * @param  int    $offset 
	 * @param  int    $limit  
	 * @return WebPushClient[]
	 */
	public static function getDataSlice(int $offset, int $limit): array {
		$data = Database::exec('SELECT * FROM `web-push-clients` LIMIT ' . $limit . ' OFFSET ' . $offset)->fetch();
		$items = [];

		foreach ($data as $item) {
			$i = new self($item['endpoint'], $item['p256dh'], $item['auth'], $item['ip'], $item['user-agent']);			
			$i->setLocation(new Location($item['country'], $item['city'], $item['city']));
			$i->setId($item['id']);
			$items[] = $i;
		}

		return $items;
	}


	/**
	 * Конструктор - получить клиента по его id в базе
	 * @param  int    $id 
	 * @return WebPushClient
	 * @throws BaseException
	 */
	public static function byId(int $id): WebPushClient {
		$q = Database::exec('SELECT * FROM `web-push-clients` WHERE `id` = :id', ['id' => $id])->fetch();
		if(isset($q[0]['id'])){
			$i = new self($q[0]['endpoint'], $q[0]['p256dh'], $q[0]['auth'], $q[0]['ip'], $q[0]['user-agent']);			
			$i->setLocation(new Location($q[0]['country'], $q[0]['city'], $q[0]['city']));
			$i->setId($q[0]['id']);
			return $i;
		}

		throw new BaseException('Invalid WebPush client_id = ' . $id);
	}

	private $authKey;
	private $p256Key;
	private $endpoint;
	private $ip;
	private $location;
	private $userAgent;
	private $id = -1;

	public function __construct(string $endpoint, string $p256Key, string $authKey, ?string $ip = null, ?string $userAgent = null){
		$this->authKey = $authKey;
		$this->p256Key = $p256Key;
		$this->endpoint = $endpoint;
		$this->ip = is_null($ip) ? IP::current() : $ip;
		$this->userAgent = is_null($userAgent) ? ($_SERVER['HTTP_USER_AGENT'] ?? null) : $userAgent;
	}

	/**
	 * Сохранить клиента в базе данных
	 */
	public function save(){
		$location = $this->getLocation();
		$q = Database::exec('INSERT INTO `web-push-clients` (`endpoint`, `p256dh`, `auth`, `country`, `city`, `ip`, `user-agent`) VALUES (:endpoint, :p256dh, :auth, :country, :city, :ip, :userAgent)', [
			'endpoint' => $this->endpoint,
			'p256dh' => $this->p256Key,
			'auth' => $this->authKey,
			'country' => $location->getCountry()->getName(),
			'city' => $location->getCity()->getName(),
			'ip' => $this->ip,
			'userAgent' => $this->userAgent,
		]);

		$this->id = $q->lastInsertId();
	}

	/**
	 * Установить месторасположение
	 * @param Location $location
	 */
	public function setLocation(Location $location){
		$this->location = $location;
	}

	/**
	 * Получить месторасположение
	 * @return Location
	 */
	public function getLocation(): Location {
		try {
			$this->location = is_null($this->location) ? $this->location = GeoIP::getLocation($this->ip) : $this->location;
		} catch (GeoException $e){
			$this->location = Location::NullLocation();
		}

		return $this->location;
	}

	/**
	 * Получить JSON-строку с oush ключами
	 * @return string JSON-строка
	 */
	public function getPushKeys(): string {
		return json_encode(['endpoint' => $this->endpoint, 'keys' => ['p256dh' => $this->p256Key, 'auth' => $this->authKey]]);
	}

	/**
	 * Получить id записи в базе
	 * @return int
	 */
	public function getId(): int {
		return $this->id;
	}

	/**
	 * Получить ip
	 * @return string
	 */
	public function getIp(): string {
		return $this->ip;
	}

	/**
	 * Установить id записи 
	 * @param int $id
	 */
	public function setId(int $id) {
		$this->id = $id;
	}

	/**
	 * Получить User-Agent
	 * @return string
	 */
	public function getUserAgent(): ?string {
		return $this->userAgent;
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

	public function delete(){
		Database::exec('DELETE FROM `web-push-user-to-clients` WHERE `client` = :client', [
			'client' => $this->getId()
		]);

		Database::exec('DELETE FROM `web-push-clients` WHERE `id` = :client', [
			'client' => $this->getId()
		]);
	}

	public function addUser(SingleUser $user){
		$q = Database::exec('INSERT INTO `web-push-user-to-clients` (`user`, `client`) VALUES (:user, :client)', [
			'endpoint' => $user->get('id'),
			'client' => $this->getId(),
		]);

		return $q->lastInsertId();
	}
}