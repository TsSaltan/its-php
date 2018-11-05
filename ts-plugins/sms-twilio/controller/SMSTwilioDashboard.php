<?php
namespace tsframe\controller;

use tsframe\Http;
use tsframe\module\Log;
use tsframe\module\twilio\SMS;
use tsframe\exception\SMSException;
use tsframe\module\user\User;
use tsframe\module\user\UserAccess;
use tsframe\module\io\Input;

/**
 * @route POST /dashboard/config/[sendsms:action]
 * @route GET /dashboard/config/[sendsms:action]
 */ 
class SMSTwilioDashboard extends UserDashboard {
	protected $actionPrefix = '';

	public function getSendsms(string $message = null){
		$append = is_null($message) ? '' : '?sms=' . $message;
		return Http::redirect(Http::makeURI('/dashboard/config' . $append . '#sms'));
	}

	public function postSendsms(){
		UserAccess::assertCurrentUser('user.editConfig');
		$data = Input::post()
					->name('phone')
						->required()
					 	->phone()
					->name('message')
						->required()
						->minLength(1)
					->assert();

		try{
			SMS::send($data['phone'], $data['message']);
			return $this->getSendsms('ok');
		} catch (SMSException $e){
			return $this->getSendsms('fail');
		}
	}
}