<?php
namespace tsframe\controller;

use tsframe\module\interkassa\API;
use tsframe\module\interkassa\Payment;
use tsframe\module\user\Cash;
use tsframe\module\Log;
use tsframe\Http;
use tsframe\Hook;

/**
 * @route GET|POST /interkassa/[:action]
 */
class IKPayProcessor extends AbstractController{
	public function response(){
		$this->responseType = 'text/plain';

		if(isset($_REQUEST['ik_inv_st']) && $_REQUEST['ik_inv_st'] == 'success' && $check = API::checkPayment($_REQUEST)){
			$am = $_REQUEST['ik_am'];
			$description = $_REQUEST['ik_desc'];
			$payId = $_REQUEST['ik_pm_no'];
			$userId = Cash::decodePayId($payId);

			Log::Cash('Успешный запрос от платёжного сервера', [
				'pay_id' => $payId, 
				'check' => $check, 
				'request' => $_REQUEST
			]);

			$cash = Cash::ofUserId($userId);
			
			if(!$cash->isTransactionExists($payId)){
				$cash->add($am, $description, $payId);
				Hook::call('cash.pay', [$userId, $am, $description, $payId]);
			} else {
				Log::Cash('Ошибка: платёж уже обработан!', ['pay_id' => $payId]);
			}

			Http::sendBody('OK', 200);
			die;

		} elseif(isset($_REQUEST['ik_inv_st'])){
			Log::Cash('Запрос от платёжного сервера', ['check' => $check, 'request' => $_REQUEST]);
		}

	
		return Http::redirect(Http::makeURI('/dashboard/user/me/edit', ['balance'=>'frompay'], 'balance'));
	}
}