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
use tsframe\module\user\cash\Codes;

/**
 * @route GET|POST /dashboard/[cash-codes:action]
 * @route POST /dashboard/user/[:user_id]/[cash-code:action]
 */ 
class CashCodesDashboard extends UserDashboard {
	use AccessTrait;

	public $actionPrefix = null;

	function getCashCodes(){
		UserAccess::assertCurrentUser('access.cash.codes');

		$codes = Codes::getCodes();
		$this->vars['codes'] = $codes;

		if(isset($_GET['code'])){
			$this->alert('Добавлен код: <b>' . Output::of($_GET['code'])->xss()->getData() . '</b>');
		}
	}

	function postCashCodes(){
		UserAccess::assertCurrentUser('access.cash.codes');

		Input::post()
			->name('balance')
			->float()
			->required()
			->assert();
		$code = Codes::addCode($_POST['balance']);
		return Http::redirect(Http::makeURI('/dashboard/cash-codes', ['code' => $code]));
	}

	function postCashCode(){
		Input::post()
			->name('code')
			->required()
			->name('user_id')
			->required()
		->assert();

		if($this->self){
			UserAccess::assertCurrentUser('access.cash.self');
		} else {
			UserAccess::assertCurrentUser('ccess.cash.payment');
		}

		$balance = Codes::getCodeBalance($_POST['code']); // string | null
		if(strlen($balance) == 0 || $balance == '0'){ 
			return Http::redirect(Http::makeURI('/dashboard/user/' . $this->selectUser->get('id') . '/edit', ['balance' => 1, 'cash-code' => 'error']));
		}

		$cash = new Cash($this->selectUser);
		$cash->add($balance, 'Использование платёжного кода ' . $_POST['code']);
		Codes::deleteCode($_POST['code']);
		return Http::redirect(Http::makeURI('/dashboard/user/' . $this->selectUser->get('id') . '/edit', ['balance' => 1, 'cash-code' => 'success']));
	}
}