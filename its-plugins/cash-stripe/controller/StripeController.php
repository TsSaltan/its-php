<?php 
namespace tsframe\controller;

use Stripe\StripeClient;
use tsframe\Config;
use tsframe\Http;
use tsframe\controller\AbstractController;
use tsframe\controller\ActionToMethodTrait;
use tsframe\exception\BaseException;
use tsframe\exception\ControllerException;
use tsframe\module\Stripe;
use tsframe\module\io\Input;
use tsframe\module\user\Cash;
use tsframe\module\user\User;

/**
 * @route GET|POST /stripe-payment/[success|cancel|checkout:action]
 */
class StripeController extends AbstractController {
	use ActionToMethodTrait;

	public function defSuccess(){
		$input = Input::request()
					  ->name('session_id')
					  ->required()
				->assert();

		try {
			$stripe = new Stripe;
			$payment = $stripe->checkPayment($input['session_id']);
			var_dump($payment['session']->payment_status);
			var_dump($payment['session']->amount_total);
			var_dump($payment['customer']->metadata->user_id);
			var_dump($payment);
		} catch (\Error $e){
			var_dump($e->getMessage());
		}
	}

	public function defCheckout(){
		if(!User::current()->isAuthorized()){
			throw new BaseException('Authorized users only', Http::CODE_UNAUTHORIZED);
		}

		$input = Input::request()
					  ->name('amount')
					  ->numeric()
					  ->required()
				->assert();

		$stripe = new Stripe;
		$link = $stripe->paymentToUserBalance(User::current(), $input['amount']);

		return Http::redirect($link);
	}

	public function response(){
		try {
			$this->callActionMethod();
		} catch (ControllerException $e){
			
		}
	}

}
?>