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
 * @route GET /web-push-demo
 * @route GET /web-push-demo/
 */ 
class WebPushDemo extends AbstractController {
	public function response(){
		$this->responseType = Http::TYPE_HTML;
		
		$jsFile = new HtmlTemplate('index', 'push/index');
		$jsFile->var('publicKey', WebPushAPI::getPublicKey());

		return $jsFile->render();
	}
}