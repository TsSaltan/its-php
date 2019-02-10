<?php
namespace tsframe\controller;

use tsframe\module\interkassa\API;
use tsframe\module\interkassa\Payment;
use tsframe\module\user\Cash;
use tsframe\module\Log;
use tsframe\Http;
use tsframe\Hook;

/**
 * @route GET|POST /paysera/[:action]
 */
class PayseraProcessor extends AbstractController{
	public function response(){
		$this->responseType = 'text/plain';

		// return Http::redirect(Http::makeURI('/dashboard/user/me/edit', ['balance'=>'frompay'], 'balance'));
	}
}