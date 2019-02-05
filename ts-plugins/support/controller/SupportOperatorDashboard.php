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
 * @route GET /dashboard/[operator:action]
 * @route GET /dashboard/operator/[chat:action]/[:chat_id]
 * @route POST /dashboard/operator/[close:action]
 * @route POST /dashboard/operator/[delete:action]
 */ 
class SupportOperatorDashboard extends UserDashboard {
	public function __construct(){
		$this->setActionPrefix(null);
	}

	public function getOperator(){
		UserAccess::assertCurrentUser('support.operator');

		$pages = new Paginator([], 10);
		$pages->setDataSize(Chat::getChatCount());
		$pages->setTotalDataCallback(function($offset, $limit){
			return Chat::getChats($offset, $limit);
		});

		$this->vars['chats'] = $pages;
	}

	public function getChat(){
		UserAccess::assertCurrentUser('support.operator');
		$chat = new Chat($this->params['chat_id']);

		$messages = $chat->getMessages();
		$last = end($messages);

		$this->vars['chatTitle'] = $chat->getTitle();
		$this->vars['chatId'] = $chat->getId();
		$this->vars['fromId'] = $last->getId();
		$this->vars['chatMessages'] = $messages;
		$this->vars['isClosed'] = $chat->getStatus() < 1;
		$this->vars['chatRole'] = 'operator';
	}

	public function postClose(){
		UserAccess::assertCurrentUser('support.operator');
		Input::post()->referer()->name('chat_id')->required()->numeric()->minLength(1);
		$chat = new Chat($_POST['chat_id']);
		$chat->close();

		Http::redirect(Http::makeURI('/dashboard/operator?closed'));
		die;
	}

	public function postDelete(){
		UserAccess::assertCurrentUser('support.operator');
		Input::post()->referer()->name('chat_id')->required()->numeric()->minLength(1);
		$chat = new Chat($_POST['chat_id']);
		$chat->delete();

		Http::redirect(Http::makeURI('/dashboard/operator?deleted'));
		die;
	}
}