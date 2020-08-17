<?php
namespace tsframe\controller;

use tsframe\Hook;
use tsframe\Http;
use tsframe\exception\AccessException;
use tsframe\exception\CashException;
use tsframe\module\Log;
use tsframe\module\Paysera;
use tsframe\module\PayssionModule;
use tsframe\module\interkassa\API;
use tsframe\module\interkassa\Payment;
use tsframe\module\io\Input;
use tsframe\module\user\Cash;
use tsframe\module\user\User;

/**
 * @route GET|POST /payssion/[pay|return|notify:action]
 */
class PayssionProcessor extends AbstractController {
	public function response(){
		$action = $this->params['action'] ?? null ;
		try {
			switch ($action) {
				// Создание платежя, перенаправление на url для отплаты
				case 'pay':
					$input = Input::post()
						->name('user_id')->required()
						->name('amount')->required()
						->name('payment_type')->required()
					->assert();

					$user = User::getById($input['user_id']);
					$payUrl = PayssionModule::createPayment($input['amount'], $input['payment_type'], $user);
					return Http::redirect($payUrl);		

				// возвращение пользователя после оплаты
				case 'return':
					$input = Input::request()
						->name('order_id')->required()
					->assert();

					$details = PayssionModule::getPaymentDetails($input['order_id']);
					if(isset($details['transaction'])){
						$result = PayssionModule::acceptPayment($details['transaction']);
						return Http::redirect(Http::makeURI('/dashboard/user/me/edit', ['balance' => $result], 'balance'));
					}
				break;

				// уведомление, отправляемое платёжным сервером payssion
				case 'notify':
					PayssionModule::getInputPaymentData();
					return Http::sendBody('OK', Http::CODE_OK, Http::TYPE_PLAIN);
			}

		} catch (CashException $e){

		} 

		// Иногда запрос от сервера возвращается раньше пользователя, возможно стоит всегшда перенаправлять на страницу без ошибки
		return Http::redirect(Http::makeURI('/dashboard/user/me/edit', ['balance' => 'fail'], 'balance'));
	}

	/**
	 * Создать новый платёж и перенаправить полльщователя на ссылку для оплаты
	 */
	protected function createPayment(){

	}
}