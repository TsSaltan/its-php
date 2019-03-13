<?php
namespace tsframe\controller;

use tsframe\Http;
use tsframe\module\Log;
use tsframe\module\Paginator;
use tsframe\module\io\Input;
use tsframe\module\io\Output;
use tsframe\module\push\WebPushClient;
use tsframe\module\user\User;
use tsframe\module\user\UserAccess;

/**
 * @route GET  /dashboard/[web-push-clients:action]
 */ 
class WebPushClientsDashboard extends UserDashboard {
	public function __construct(){
		$this->setActionPrefix(null);
	}

	public function getWebPushClients(){
		UserAccess::assertCurrentUser('webpush');

		$this->vars['title'] = 'Список Web-Push клиентов';
		$pages = new Paginator(WebPushClient::class, 10);
		$this->vars['clients'] = $pages;
		/*$this->vars['logTypes'] = Log::getTypes();
		$this->vars['logType'] = $type;

		if($type == 'default'){
			$this->vars['logSize'] = Log::getSize();
		}

		$pages = new Paginator([], 10);
		$pages->setDataSize(Log::getLogsCount($type));
		$pages->setTotalDataCallback(function($offset, $limit) use ($type){
			$logs = Log::getLogs($type, $offset, $limit);
			return Output::of($logs)->specialChars()->getData();
		});

		*/
	}
}