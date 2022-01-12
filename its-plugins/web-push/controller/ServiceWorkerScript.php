<?php
namespace tsframe\controller;

use tsframe\Config;
use tsframe\Http;
use tsframe\module\push\WebPushAPI;
use tsframe\view\Template;

/**
 * @route GET /service-worker.js
 * @route GET /service-worker
 */ 
class ServiceWorkerScript extends AbstractController {
	public function response(){
		$this->responseType = Http::TYPE_JAVASCRIPT;
		
		$jsFile = new Template('index', 'push/service-worker');
		$jsFile->var('publicKey', WebPushAPI::getPublicKey());

		return $jsFile->render();
	}
}