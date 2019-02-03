<?php
namespace tsframe\controller;

use tsframe\Http;
use tsframe\exception\AccessException;
use tsframe\module\io\Input;
use tsframe\module\io\Output;
use tsframe\module\user\Cash;
use tsframe\module\user\SingleUser;
use tsframe\module\user\User;
use tsframe\module\user\UserAccess;

/**
 * @route POST /dashboard/user/edit-balance
 */ 
class EditBalanceDashboard extends UserDashboard {
	use AccessTrait;

	public function response(){
		$this->access('cash.payment');
		$data = Input::post()
			->referer()
			->name('user_id')->required()->numeric()
			->name('balance')->required()->numeric()
			->name('description')->required()
		->assert();

		$users = User::get(['id' => $data['user_id']]);
		if(sizeof($users) > 0){
			$user = current($users);
			$balance = floatval($data['balance']);
			$cash = new Cash($user);
			if($balance >= 0){
				$cash->add($balance, $data['description']);
			} else {
				$cash->sub($balance * -1, $data['description']);
			}

			return Http::redirect(Http::makeURI('/dashboard/user/'.$data['user_id'].'/edit', ['balance' => '1']));
		}

		throw new AccessException('Invalid user id: ' . $data['user_id']);
	}
}