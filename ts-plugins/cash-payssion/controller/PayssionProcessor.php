<?php
namespace tsframe\controller;

use tsframe\Hook;
use tsframe\Http;
use tsframe\exception\AccessException;
use tsframe\module\Log;
use tsframe\module\Paysera;
use tsframe\module\interkassa\API;
use tsframe\module\interkassa\Payment;
use tsframe\module\io\Input;
use tsframe\module\user\Cash;
use tsframe\module\user\User;

/**
 * @route GET|POST /payssion/[:pay]
 * @route GET|POST /payssion/[:notify]
 * @route GET|POST /payssion/[:return]
 */
class Payssion extends AbstractController {
	public function response(){
		$action = $this->params['action'] ?? null ;
		switch ($action) {

			case 'pay':
				return $this->createPayment();

			case 'notify':
				try{
					Paysera::checkPayment();
					Log::cash("Успешный запрос от платёжного сервера", ['data' => $_REQUEST]);
					return "OK";
				} catch(\Exception $e){
					Log::cash("Ошибка при обработке запроса от платёжного сервера", ['data' => $_REQUEST, 'error' => $e->getMessage(), 'error_type' => get_class($e)]);
					return "Payment error: " . $e->getMessage();
				}
		}

		return Http::redirect(Http::makeURI('/dashboard/user/me/edit', ['balance'=>'fail'], 'balance'));
	}

	/**
	 * Создать новый платёж и перенаправить полльщователя на ссылку для оплаты
	 */
	protected function createPayment(){

	}
}