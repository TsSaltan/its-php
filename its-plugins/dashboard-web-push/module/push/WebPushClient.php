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
use tsframe\module\push\WebPushQuery;
use tsframe\module\user\SingleUser;

/**
 * WebPush-клиентам с привязкой к пользователю и к геолокации
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
	 * @param  string|null 	$country 
	 * @param  string|null 	$city    
	 * @param  int 			$userAccess  Минимальный уровень доступа, см. UserAccess константы, -1 все пользователи, в т.ч. незарегистрированные 
	 * @return array
	 */
	public static function findIdsByParams(?string $country = null, ?string $city = null, int $userAccess = -1): array {
		$condQuery = null;
		$args = [];

		if(!is_null($country) && $country !== '*'){
			$condQuery .= ' AND `country` = :country';
			$args['country'] = $country;
		}

		if(!is_null($city) && $city !== '*'){
			$condQuery .= ' AND `city` = :city';
			$args['city'] = $city;
		}

		if($userAccess > -1){
			$args['access'] = $userAccess;
			$q = Database::exec("SELECT wc.`id` 'id' FROM `web-push-clients` wc 
								 INNER JOIN `users` u ON u.`access` >= :access
								 INNER JOIN `web-push-user-to-clients` u2c ON u.`id` = u2c.`user`
								 WHERE wc.`id` = u2c.`client`" . $condQuery, $args)->fetch();
		} else {
			$q = Database::exec('SELECT `id` FROM `web-push-clients` WHERE `id` > 0' . $condQuery, $args)->fetch();
		}

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

	private $query;
	private $ip;
	private $location;
	private $userAgent;
	private $id = -1;

	public function __construct(string $endpoint, string $p256Key, string $authKey, ?string $ip = null, ?string $userAgent = null){
		$this->query = new WebPushQuery($endpoint, $p256Key, $authKey);
		$this->ip = is_null($ip) ? IP::current() : $ip;
		$this->userAgent = is_null($userAgent) ? ($_SERVER['HTTP_USER_AGENT'] ?? null) : $userAgent;
	}

	public function getQuery(): WebPushQuery {
		return $this->query;
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
		return $this->query->getPushKeys();
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
		return $this->query->getSubscription();
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
			'user' => $user->get('id'),
			'client' => $this->getId(),
		]);

		return $q->lastInsertId();
	}
}