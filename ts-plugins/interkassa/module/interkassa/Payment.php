<?php
namespace tsframe\module\interkassa;

use tsframe\module\database\Database;
use tsframe\module\database\Query;
use tsframe\module\Crypto;
use tsframe\Config;
use tsframe\Hook;
use tsframe\Cache;


class Payment{
	protected $cashId;
	protected $payId;
	protected $amount;
	protected $currency;
	protected $user;
	protected $description;

	public function __construct($user = null, $amount = 10.0, string $description = null){
		$this->user = is_null($user) ? User::current() : $user;
		$this->cashId = Config::get('interkassa.cashId');
		$this->payId = uniqid('ID_' . $user->get('id') . '_');
		$this->amount = $amount;
		$this->currency = Config::get('interkassa.currency');
		$this->description = is_null($description) ? ('Пополнение баланса пользователя ' . $user->get('login')) : $description;
	}

	public function getForm(): string {
		$host = $_SERVER['HTTP_HOST'];
		return <<<HTML
		<form id="payment" name="payment" method="post" action="https://sci.interkassa.com/" enctype="utf-8">
			<input type="hidden" name="ik_co_id" value="$this->cashId" />
			<input type="hidden" name="ik_pm_no" value="$this->payId" />
			<input type="number" name="ik_am" value="$this->amount" />
			<input type="hidden" name="ik_cur" value="$this->currency" />
			<input type="hidden" name="ik_desc" value="$this->description" />

			<input type="hidden" name="ik_suc_u" value="//$host/interkassa/success" />
			<input type="hidden" name="ik_suc_m" value="post" />
			<input type="hidden" name="ik_fal_u" value="//$host/interkassa/fail" />
			<input type="hidden" name="ik_fal_m" value="post" />
			<input type="hidden" name="ik_pnd_u" value="//$host/interkassa/pending" />
			<input type="hidden" name="ik_pnd_m" value="post" />
		    <input type="submit" value="Оплатить">
		</form>
HTML;
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