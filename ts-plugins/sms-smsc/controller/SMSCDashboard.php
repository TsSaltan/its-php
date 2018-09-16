<?php
namespace tsframe\controller;

use tsframe\Http;
use tsframe\module\Log;
use tsframe\module\SMSC as SMS;
use tsframe\exception\SMSCException;
use tsframe\module\user\User;
use tsframe\module\user\UserAccess;
use tsframe\module\io\Input;

/**
 * @route POST /dashboard/config/[sendsms:action]
 * @route GET /dashboard/config/[sendsms:action]
 */ 
class UserPhoneDashboard extends UserDashboard {
	protected $actionPrefix = '';

	public function getSendsms(){
		return Http::redirect(Http::makeURI('/dashboard/config'));
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
		} catch (SMSCException $e){
			
		}

		return $this->getSendsms();
	}
}