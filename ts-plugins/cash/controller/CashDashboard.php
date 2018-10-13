<?php
namespace tsframe\controller;

use tsframe\module\user\User;
use tsframe\module\user\UserAccess;
use tsframe\module\user\Cash;
use tsframe\module\Paginator;

/**
 * @route GET /dashboard/[cash:action]
 */ 
class CashDashboard extends UserDashboard {

	protected $actionPrefix = '';

	public function getCash(){
		UserAccess::assertCurrentUser('cash.global');
		$cashHistory = Cash::getGlobalHistory();
		$this->vars['title'] = 'Денежные операции';
		$this->vars['cashHistory'] = new Paginator($cashHistory);
		$this->vars['userList'] = User::get();
	}

}