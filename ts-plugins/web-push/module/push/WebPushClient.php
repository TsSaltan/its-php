<?php 	
namespace tsframe\module\push;

use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;
use tsframe\Config;
use tsframe\exception\GeoException;
use tsframe\module\Geo\GeoIP;
use tsframe\module\Geo\Location;
use tsframe\module\IP;
use tsframe\module\PaginatorInterface;
use tsframe\module\database\Database;
use tsframe\module\push\WebPushAPI;

class WebPushClient implements PaginatorInterface {
	public static function getDataSize(): int {
		return Database::exec('SELECT COUNT(`id`) c FROM `web-push-clients`')->fetch()[0]['c'];
	}

	public static function getDataSlice(int $offset, int $limit): array {
		return Database::exec('SELECT * FROM `web-push-clients` LIMIT ' . $limit . ' OFFSET ' . $offset)->fetch();
	}

	private $authKey;
	private $p256Key;
	private $endpoint;
	private $ip;
	private $location;

	public function __construct(string $endpoint, string $p256Key, string $authKey, ?string $ip = null){
		$this->authKey = $authKey;
		$this->p256Key = $p256Key;
		$this->endpoint = $endpoint;
		$this->ip = is_null($ip) ? IP::current() : $ip;
	}

	public function save(){
		$location = $this->getLocation();

		Database::exec('INSERT INTO `web-push-clients` (`endpoint`, `p256dh`, `auth`, `country`, `city`, `ip`) VALUES (:endpoint, :p256dh, :auth, :country, :city, :ip)', [
			'endpoint' => $this->endpoint,
			'p256dh' => $this->p256Key,
			'auth' => $this->authKey,
			'country' => $location->getCountry()->getName(),
			'city' => $location->getCity()->getName(),
			'ip' => $this->ip,
		]);
	}

	public function setLocation(Location $location){
		$this->location = $location;
	}

	public function getLocation(){
		try {
			$this->location = is_null($this->location) ? $this->location = GeoIP::getLocation($this->ip) : $this->location;
		} catch (GeoException $e){
			$this->location = Location::NullLocation();
		}

		return $this->location;
	}

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