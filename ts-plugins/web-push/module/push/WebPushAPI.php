<?php 	
namespace tsframe\module\push;

use Minishlink\WebPush\WebPush;
use tsframe\Config;
use tsframe\Http;
use tsframe\module\push\WebPushClient;

class WebPushAPI {
	public static function getPublicKey(){
		return Config::get('push.publicKey');
	}

	public static function getSender(){
		return Config::get('push.sender');
	}

	private static function getPrivateKey(){
		return Config::get('push.privateKey');
	}

	private $webPush;

	public function __construct(){
		$this->webPush = new WebPush([
	    	'VAPID' => [
		        'subject' => Http::makeURI('/', [], 'from=push'),
	        	'publicKey' => self::getPublicKey(),
	        	'privateKey' => self::getPrivateKey(),
	        ]
	    ]);
	}

	public function addPushMessage(WebPushClient $client, $payload){
		$payload = is_array($payload) ? json_encode($payload) : $payload;
		$this->webPush->sendNotification(
	        $client->getSubscription(),
        	$payload
    	);
	}

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