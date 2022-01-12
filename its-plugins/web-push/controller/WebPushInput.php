<?php
namespace tsframe\controller;

use tsframe\Config;
use tsframe\Http;
use tsframe\module\IP;
use tsframe\module\push\WebPushAPI;
use tsframe\module\push\WebPushClient;
use tsframe\module\user\User;
use tsframe\view\HtmlTemplate;
use tsframe\view\Template;

/**
 * @route POST /web-push/new-client
 */ 
class WebPushInput extends AbstractController {
	public function response(){
		$this->responseType = Http::TYPE_PLAIN;

		if(isset($_POST['data'])){
			$json = json_decode($_POST['data'], true);
			if(isset($json['endpoint']) && isset($json['keys']) && isset($json['keys']['p256dh']) && isset($json['keys']['auth'])){
				$client = new WebPushClient($json['endpoint'], $json['keys']['p256dh'], $json['keys']['auth']);
				$client->save();

				$user = User::current();
				if($user->isAuthorized()){
					$client->addUser($user);
				}

				return "OK";
			}
		}
		
		return "Input data error";
	}
}