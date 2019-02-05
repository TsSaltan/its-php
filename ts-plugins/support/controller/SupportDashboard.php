<?php
namespace tsframe\controller;

use tsframe\Http;
use tsframe\module\Log;
use tsframe\module\Paginator;
use tsframe\module\io\Input;
use tsframe\module\io\Output;
use tsframe\module\support\Chat;
use tsframe\module\user\User;
use tsframe\module\user\UserAccess;

/**
 * @route GET /dashboard/[support:action]
 * @route GET /dashboard/support/[chat:action]/[:chat_id]
 * @route POST /dashboard/support/[new:action]
 */ 
class SupportDashboard extends UserDashboard {
	public function __construct(){
		$this->setActionPrefix(null);
	}

	public function getChat(){
		UserAccess::assertCurrentUser('support.client');
		$chat = new Chat($this->params['chat_id']);
		if($chat->getOwnerId() !== $this->currentUser->get('id')){
			throw new AccessException('Invalid access');
		}

		$this->vars['chatTitle'] = $chat->getTitle();
		$this->vars['chatId'] = $chat->getId();
		$this->vars['chatMessages'] = $chat->getMessages();
		$chat->setCurrentDate();
	}

	public function getSupport(){
		UserAccess::assertCurrentUser('support.client');

		$pages = new Paginator([], 10);
		$pages->setDataSize(Chat::getUserChatCount($this->currentUser));
		$pages->setTotalDataCallback(function($offset, $limit){
			return Chat::getUserChats($this->currentUser, $offset, $limit);
		});

		$this->vars['userChats'] = $pages;
	}
	
	public function postNew(){
		UserAccess::assertCurrentUser('support.client');
		Input::post()
			->referer()
			->name('title')->required()->minlength(1)
			->name('message')->required()->minlength(1)
		->assert();

		$chat = Chat::create($this->currentUser, $_POST['title']);
		$chat->addMessage($_POST['message']);

		Http::redirect(Http::makeURI('/dashboard/support/chat/' . $chat->getId()));
		die;
	}
}