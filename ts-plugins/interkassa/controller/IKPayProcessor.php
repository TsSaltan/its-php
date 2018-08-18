<?php
namespace tsframe\controller;

use tsframe\module\interkassa\API;

/**
 * @route POST /interkassa/[:action]
 */
class IKPayProcessor extends AbstractController{


	public function response(){
		//var_dump(API::query('co-invoice'));
		//die;
		$this->responseType = 'text/plain';
/*
		if($this->params['action'] == 'success' && isset($_POST['ik_inv_st']) && $_POST['ik_inv_st'] == 'success'){
			$check = API::checkPayment($_POST);

			var_dump(['check' => $check]);
		}

		var_dump($_POST);*/
		file_put_contents(TEMP . 'pay_' . time() . '.txt', '$_POST = ' . var_export($_POST, true). "\n" . '$_SERVER = ' . var_export($_SERVER, true));
		die('OK');
	}
}