<?php
namespace tsframe\controller;

use tsframe\module\interkassa\API;
use tsframe\module\user\Cash;
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
			file_put_contents(TEMP.'pay_success_' . $t . '.txt', 'check => ' . var_export($check, true ) . "\n\n" . 'REQUEST = ' . var_export($_REQUEST, true) . "\n\n" . 'SERVER = ' . var_export($_SERVER, true));

			$am = $_REQUEST['ik_am'];
			$description = $_REQUEST['ik_desc'];
			$payId = $_REQUEST['ik_pm_no'];
			$userId = explode('_', $payId)[1];

			$cash = Cash::ofUserId($userId);
			$cash->add($am, $description . '; Transaction ID: ' . $payId);

			Hook::call('cash.pay', [$userId, $am, $description, $payId]);
			Http::send('OK', 200);

			die;
		}

		file_put_contents(TEMP.'pay_log_' . $t . '.txt', 'check => ' . var_export($check, true ) . "\n\n" . 'REQUEST = ' . var_export($_REQUEST, true) . "\n\n" . 'SERVER = ' . var_export($_SERVER, true));
	
		return Http::redirect(Http::makeURI('/dashboard/user/me/edit?balance=frompay'));
	}
}