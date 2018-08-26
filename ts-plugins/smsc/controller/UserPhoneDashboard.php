<?php
namespace tsframe\controller;

use tsframe\Http;
use tsframe\module\Log;
use tsframe\module\SMS;
use tsframe\exception\SMSException;
use tsframe\module\user\User;
use tsframe\module\user\UserAccess;
use tsframe\utils\io\Validator;

/**
 * @route POST /dashboard/user/[:user_id]/edit/[phone:action]
 * @route POST /dashboard/config/[sendsms:action]
 * @route GET /dashboard/config/[sendsms:action]
 */ 
class UserPhoneDashboard extends UserDashboard {
	protected $actionPrefix = '';

	public function postPhone(){
		UserAccess::assert($this->currentUser, ($this->self ? 'user.self' : 'user.edit'));
		$data = Validator::post()
					->name('phone')
						->required()
					 	->phone()
					->assert();

		$meta = $this->currentUser->getMeta()->set('phone', $data['phone']);
		return Http::redirect(Http::makeURI('/dashboard/user/'.$this->currentUser->get('id').'/edit?phone'));
	}

	public function getSendsms(){
		return Http::redirect(Http::makeURI('/dashboard/config'));
	}

	public function postSendsms(){
		UserAccess::assertCurrentUser('user.editConfig');
		$data = Validator::post()
					->name('phone')
						->required()
					 	->phone()
					->name('message')
						->required()
						->minLength(1)
					->assert();

		try{
			SMS::send($data['phone'], $data['message']);
		} catch (SMSException $e){
			
		}

		return $this->getSendsms();
	}
}