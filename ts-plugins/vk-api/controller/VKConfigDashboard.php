<?php
namespace tsframe\controller;

use tsframe\Config;
use tsframe\Http;
use tsframe\module\io\Input;
use tsframe\module\user\User;
use tsframe\module\user\UserAccess;

/**
 * @route POST /dashboard/config/vk-api/[callback:action]
 */ 
class VKConfigDashboard extends UserDashboard {
	protected $actionPrefix = '';

	public function postCallback(){
		UserAccess::assertCurrentUser('user.editConfig');

		$data = Input::post()
					->name('group_id')
						->required()
						->minLength(1)
					->name('confirm_code')
						->required()
						->minLength(1)
					->name('secret_key')
						->required()
						->minLength(1)
					->assert();

		Config::set('vk.groups.'.$data['group_id'], ['confirm' => $data['confirm_code'], 'secret' => $data['secret_key']]);
		return Http::redirect(Http::makeURI('/dashboard/config', [], 'vk-callback-api'));
	}
}