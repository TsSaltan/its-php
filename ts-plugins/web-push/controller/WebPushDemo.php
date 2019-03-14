<?php
namespace tsframe\controller;

use tsframe\Config;
use tsframe\Http;
use tsframe\module\IP;
use tsframe\module\push\WebPushAPI;
use tsframe\module\push\WebPushClient;
use tsframe\view\HtmlTemplate;
use tsframe\view\Template;

/**
 * @route GET /web-push/index
 * @route GET /web-push/
 * @route POST /web-push/send-push
 */ 
class WebPushDemo extends AbstractController {
	public function response(){
		if(isset($_POST['data'])){
			$json = json_decode($_POST['data'], true);
			if(isset($json['endpoint']) && isset($json['keys']) && isset($json['keys']['p256dh']) && isset($json['keys']['auth'])){
				$client = new WebPushClient($json['endpoint'], $json['keys']['p256dh'], $json['keys']['auth']);
				$client->save();
				$api = new WebPushAPI;
				$api->addPushMessage($client, ['body' => uniqid('Random text '), 'title' => 'Demo push', 'icon' => 'https://pp.userapi.com/c847021/v847021989/1a09a7/JZPoLafRQsU.jpg', 'link' => 'https://google.by/asd']);
				return var_dump($api->send());
			} else {
				return "Input data error";
			}
		}

		$this->responseType = Http::TYPE_HTML;
		
		$jsFile = new HtmlTemplate('index', 'push/index');
		$jsFile->var('publicKey', WebPushAPI::getPublicKey());

		return $jsFile->render();
	}
}