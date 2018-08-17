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
		if($this->params['action'] == 'success' && isset($_POST['ik_inv_st']) && $_POST['ik_inv_st'] == 'success'){

		}

		var_dump($_POST);
		var_dump($this->params);
		die;
	}
}