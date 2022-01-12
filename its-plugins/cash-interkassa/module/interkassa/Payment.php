<?php
namespace tsframe\module\interkassa;

use tsframe\module\Crypto;
use tsframe\module\user\Cash;
use tsframe\Config;
use tsframe\view\HtmlTemplate;

class Payment{
	protected $cashId;
	protected $payId;
	protected $amount;
	protected $currency;
	protected $user;
	protected $description;
	protected $tpl;

	public function calculateAmount(string $requiredAmount = '0'){
		$cash = new Cash($this->user);
		$this->amount = 0;
		if($cash->compare($requiredAmount) > 0){
			$this->amount = $cash->diff($requiredAmount);
		}
	}

	public function __construct($user = null, $amount = 10.0, string $description = null){
		$this->user = is_null($user) ? User::current() : $user;
		$this->cashId = Config::get('interkassa.cashId');
		$this->payId = Cash::createPayId($user->get('id'));
		$this->amount = $amount;
		$this->currency = Cash::getCurrency();
		$this->description = is_null($description) ? ('Пополнение баланса пользователя ' . $user->get('login') . ' через Interkassa') : $description;

		$this->tpl = new HtmlTemplate('interkassa', 'payform');
	}

	public function getProcessURI(): string {
		return "https://sci.interkassa.com/";
	}

	public function getForm(bool $amountEditable = true, bool $fieldsOnly = false){
		$this->tpl->vars([
			'cashId' => $this->cashId,
			'payId' => $this->payId,
			'amount' => $this->amount,
			'currency' => $this->currency,
			'description' => $this->description,
			'amountEditable' => $amountEditable,
			'fieldsOnly' => $fieldsOnly,
			'formAction' => $this->getProcessURI(),
		]);

		return $this->tpl->render();
	}

	public function getURI(): string {
		return 'https://sci.interkassa.com/?' . http_build_query([
			'ik_co_id' => $this->payId,
			'ik_pm_no' => $this->cashId, 
			'ik_am' => $this->amount,
			'ik_cur' => $this->currency,
			'ik_desc' => $this->description
		]);
	}
}