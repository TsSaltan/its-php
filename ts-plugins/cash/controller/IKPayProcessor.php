<?php
namespace tsframe\controller;

use tsframe\module\interkassa\API;
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
		$t = time();
		if(isset($_REQUEST['ik_inv_st']) && $_REQUEST['ik_inv_st'] == 'success' && $check = API::checkPayment($_REQUEST)){
			//file_put_contents(TEMP.'pay_success_' . $t . '.txt', 'check => ' . var_export($check, true ) . "\n\n" . 'REQUEST = ' . var_export($_REQUEST, true) . "\n\n" . 'SERVER = ' . var_export($_SERVER, true));

			$am = $_REQUEST['ik_am'];
			$description = $_REQUEST['ik_desc'];
			$payId = $_REQUEST['ik_pm_no'];
			$userId = explode('_', $payId)[1];

			Log::Cash('Успешный запрос от платёжного сервера', ['pay_id' => $payId, 'check' => $check, 'request' => $_REQUEST]);

			$cash = Cash::ofUserId($userId);
			
			if(!$cash->isTransactionExists()){
				$cash->add($am, $description . '; Transaction ID: ' . $payId);
				Hook::call('cash.pay', [$userId, $am, $description, $payId]);
			} else {
				Log::Cash('Ошибка: платёж уже обработан!', ['pay_id' => $payId]);
			}

			Http::send('OK', 200);

			die;
		}

		Log::Cash('Запрос от платёжного сервера', ['check' => $check, 'request' => $_REQUEST]);
	
		return Http::redirect(Http::makeURI('/dashboard/user/me/edit?balance=frompay'));
	}
}