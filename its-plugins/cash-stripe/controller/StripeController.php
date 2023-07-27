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

		$stripe = new Stripe;
		$payment = $stripe->checkPayment($input['session_id'], User::current());
		if($payment){
			return Http::redirectURI('/dashboard/user/me/edit', ['balance' => 'true', 'from' => 'stripe', 'result' => 'success'], 'balance');
		} else {
			return Http::redirectURI('/dashboard/user/me/edit', ['balance' => 'true', 'from' => 'stripe', 'result' => 'fail'], 'balance');
		}
	}

	public function defCheckout(){
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
		if(!User::current()->isAuthorized()){
			throw new BaseException('Only authorized users', Http::CODE_UNAUTHORIZED);
		}

		try {
			$this->callActionMethod();
		} catch (ControllerException $e){
			
		}
	}

}
?>