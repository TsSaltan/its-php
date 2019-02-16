<?php
namespace tsframe\module;

use tsframe\Config;
use tsframe\Http;
use tsframe\exception\WebToPayException;
use tsframe\module\WebToPay;
use tsframe\module\user\Cash;
use tsframe\module\user\User;

class Paysera {
	public static function createPayment(string $amount){
		$user = User::current();
		$request = WebToPay::redirectToPayment([
	        'projectid'     => Config::get('paysera.projectid'),
	        'sign_password' => Config::get('paysera.sign_password'),
	        'orderid'       => Cash::createPayId($user->get('id')),
	        'p_email'       => $user->get('email'),
	        'amount'        => $amount / 1000,
	        'currency'      => Cash::getCurrency(),
	        'accepturl'     => Http::makeURI('/paysera/accept'),
	        'cancelurl'     => Http::makeURI('/paysera/cancel'),
	        'callbackurl'   => Http::makeURI('/paysera/callback'),
	        'test'          => Config::get('paysera.test'),
	    ]);
	}

	public static function checkPayment(){
		$response = WebToPay::checkResponse($_REQUEST, [
        	'projectid'     => Config::get('paysera.projectid'),
        	'sign_password' => Config::get('paysera.sign_password'),
    	]);

    	if ($response['test'] !== Config::get('paysera.test')) {
        	throw new WebToPayException('Check testing/production mode!');
	    }
	    if ($response['type'] !== 'macro') {
	        throw new WebToPayException('Only macro payment callbacks are accepted');
	    }

	    $orderId = $response['orderid'];
    	$amount = $response['amount'] * 1000;
    	$currency = $response['currency'];

    	$userId = Cash::decodePayId($orderId);
    	$cash = Cash::ofUserId($userId);
    	if(!$cash->isTransactionExists($orderId)){
    		$cash->add($amount, 'Пополнение счёта (через paysera)', $orderId);
    	} else {
    		throw new WebToPayException('Payment #' . $orderId . ' already processed');
    	}

    	return true;
	}
}