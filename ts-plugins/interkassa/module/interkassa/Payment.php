<?php
namespace tsframe\module\interkassa;

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
		$this->payId = uniqid('ID_' . $user->get('id') . '_');
		$this->amount = $amount;
		$this->currency = Cash::getCurrency();
		$this->description = is_null($description) ? ('Пополнение баланса пользователя ' . $user->get('login')) : $description;

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

	/*

	public function getForm(): string {
		$action = $this->getFormAction();

		$form = <<<HTML
		<form id="payment" name="payment" method="post" action="$action" enctype="utf-8">
HTML;		

		$form .= $this->getFormFields();

		$form .= <<<HTML
			<input type="submit" value="Оплатить">
		</form>
HTML;
	}

	public function getFormFields(): string {
		$host = $_SERVER['HTTP_HOST'];

		return <<<HTML
			<input type="hidden" name="ik_co_id" value="$this->cashId" />
			<input type="hidden" name="ik_pm_no" value="$this->payId" />
			<input type="hidden" name="ik_am" value="$this->amount" />
			<input type="hidden" name="ik_cur" value="$this->currency" />
			<input type="hidden" name="ik_desc" value="$this->description" />
HTML;
	}*/

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