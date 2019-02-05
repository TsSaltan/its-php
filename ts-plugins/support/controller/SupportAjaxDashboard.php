<?php
namespace tsframe\controller;

use tsframe\Http;
use tsframe\exception\AccessException;
use tsframe\module\Log;
use tsframe\module\Paginator;
use tsframe\module\io\Input;
use tsframe\module\io\Output;
use tsframe\module\support\Chat;
use tsframe\module\user\SingleUser;
use tsframe\module\user\User;
use tsframe\module\user\UserAccess;
use tsframe\view\HtmlTemplate;

/**
 * @route POST /ajax/support/[message:action]
 * @route POST /ajax/support/[message-operator:action]
 * @route POST /ajax/support/[updates:action]
 */ 
class SupportAjaxDashboard extends AbstractAJAXController {
	public function response(){
		Input::post()
			->referer()
			->name('chat')->required()->minLength(1)->numeric()
		->assert();

		$user = User::current();
		$chat = new Chat($_POST['chat']);
		$action = $this->params['action'];

		switch ($action) {
			case 'message-operator':
				UserAccess::assertCurrentUser('support.operator');
				Input::post()->name('message')->required()->assert();
				$chat->addMessage($_POST['message'], $user);
				$this->sendOK();
				break;

			case 'message':
				$this->checkCurrentUserChat($user, $chat);
				if($chat->getStatus() < 1){
					$this->sendError('closed');
					break;
				}
				Input::post()->name('message')->required()->assert();
				$chat->addMessage($_POST['message']);
				$this->sendOK();
				break;
				
			case 'updates':
				if(!UserAccess::checkCurrentUser('support.operator')){
					$this->checkCurrentUserChat($user, $chat);
				}

				Input::post()->name('from_id')->required()->assert();
				if($chat->hasNewMessages($_POST['from_id'])){
					$messages = $chat->getNewMessages($_POST['from_id']);
					$tpl = new HtmlTemplate('dashboard', 'update-messages');
					$tpl->var('chatMessages', $messages);
					$html = $tpl->render();
					$last = end($messages);
					$this->sendData(['updates' => true, 'html' => $html, 'from_id' => $last->getId()]);
				} else {
					$this->sendData(['updates' => false, 'html' => null]);
				}

				if($chat->getOwnerId() == $user->get('id')){
					$chat->setCurrentDate();
				}
				break;
		}
	}

	protected function checkCurrentUserChat(SingleUser $user, Chat $chat){
		if(!UserAccess::checkCurrentUser('support.client') || $chat->getOwnerId() != $user->get('id')){
			throw new AccessException('Access denied');
		}
	}
}