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
 * @route POST /dashboard/web-push-clients/[queue:action]
 */ 
class WebPushClientsDashboard extends UserDashboard {
	public function __construct(){
		$this->setActionPrefix(null);
	}

	public function getWebPushClients(){
		UserAccess::assertCurrentUser('webpush');

		$this->vars['title'] = 'Список Web-Push клиентов';
		$this->vars['clients'] = new Paginator(WebPushClient::class, 10);
		$this->vars['location'] = WebPushClient::getLocations();
	}

	public function postQueue(){

	}
}