<?php 	
namespace tsframe\module\push;

use Minishlink\WebPush\WebPush;
use tsframe\Config;
use tsframe\Http;
use tsframe\module\push\WebPushClient;

class WebPushAPI {
	public static function getPublicKey(){
		return "BA_xZ25u4B1faT95glQ09nettLkY2pV2RLhq4PnzHG9uq4cReUD87rW5XAD7JtsjkLgYqz9J0GzCTIQzBsCCIX0";
		return Config::get('push.publicKey');
	}

	private static function getPrivateKey(){
		return "rXzfcFuPCoUmL7f3vqo6lI_XsnFMS48K7KkARs_vS40";
		return Config::get('push.privateKey');
	}

	private $webPush;

	public function __construct(){
		$this->webPush = new WebPush([
	    	'VAPID' => [
		        // 'subject' => 'mailto:tssaltan@gmail.com', // can be a mailto: or your website address
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