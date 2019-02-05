<?php
namespace tsframe\controller;

use tsframe\Http;
use tsframe\exception\AccessException;
use tsframe\module\Log;
use tsframe\module\Paginator;
use tsframe\module\io\Input;
use tsframe\module\io\Output;
use tsframe\module\support\Chat;
use tsframe\module\user\User;
use tsframe\module\user\UserAccess;
use tsframe\view\HtmlTemplate;

/**
 * @route POST /ajax/support/[message:action]
 * @route POST /ajax/support/[update:action]
 */ 
class SupportAjaxDashboard extends AbstractAJAXController {
	public function response(){
		$user = User::current();
		$action = $this->params['action'];

		Input::post()
			->referer()
			->name('chat')->required()->minLength(1)->numeric()
		->assert();

		$chat = new Chat($_POST['chat']);
		if($chat->getOwnerId() != $user->get('id')){
			throw new AccessException('Access denied');
		}

		switch ($action) {
			case 'message':
				Input::post()->name('message')->required()->assert();
				$chat->addMessage($_POST['message']);
				$this->sendOK();
				break;
				
			case 'update':
				if($chat->hasNewMessages()){
					$messages = $chat->getNewMessages();
					$tpl = new HtmlTemplate('dashboard', 'update-messages');
					$tpl->var('chatMessages', $messages);
					$html = $tpl->render();
					$this->sendData(); // @todo // stopishere
				}
				break;
		}
	}
}