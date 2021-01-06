<?php
namespace tsframe\controller;

use tsframe\Http;
use tsframe\module\Log;
use tsframe\module\Paginator;
use tsframe\module\io\Input;
use tsframe\module\io\Output;
use tsframe\module\push\WebPushClient;
use tsframe\module\push\WebPushQueue;
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
		$this->vars['location'] = WebPushClient::getUniqueValues();
		$this->vars['queues'] = WebPushQueue::getList();
		$this->vars['userAccesses'] = UserAccess::getArray();
	}

	public function postQueue(){
		UserAccess::assertCurrentUser('webpush');
		
		Input::post()
			->name('country')->required()
			->name('city')->required()
			->name('user-group')->required()
			->name('title')->required()->minLength(1)->maxLength(250)
			->name('body')->required()->minLength(1)->maxLength(1000)
			->name('icon')->required()->minLength(1)->maxLength(500)
			->name('link')->required()->minLength(1)->maxLength(500)
		->assert();

		$clients = WebPushClient::findIdsByParams($_POST['country'], $_POST['city']);
		WebPushQueue::add($clients, $_POST['title'], $_POST['body'], $_POST['link'], $_POST['icon']);

		return Http::redirect(Http::makeURI('/dashboard/web-push-clients', ['queue' => 'added']));
	}
}