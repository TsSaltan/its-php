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
 * @route GET|POST /paysera/[:action]
 * @route POST /[dashboard:action]/paysera
 */
class PayseraProcessor extends AbstractController{
	public function response(){
		$action = $this->params['action'] ?? null ;
		switch ($action) {
			// Перенаправление пользователя на страницу оплаты
			case 'dashboard':
				if(User::current()->isAuthorized()){
					Input::post()->referer()->name('amount')->numeric()->required();
					$amount = $_POST['amount'];
					Paysera::createPayment($amount);
					return;
				}
				throw new AccessException("Unauthorized user", Http::CODE_UNAUTHORIZED);
				return;
			
			case 'accept':
				// @todo наверное здесь нужно перенаправлять юзера на страницу с уведомлением, что всё оплачено
				$this->htmlTemplate('Thank you for buying');
				return;

			case 'cancel':
				$this->htmlTemplate('Payment canceled');
				return;

			case 'callback':
				try{
					Paysera::checkPayment();
					Log::cash("Успешный запрос от платёжного сервера", ['data' => $_REQUEST]);
					return "OK";
				} catch(\Exception $e){
					Log::cash("Ошибка при обработке запроса от платёжного сервера", ['data' => $_REQUEST, 'error' => $e->getMessage(), 'error_type' => get_class($e)]);
					return "Payment error: " . $e->getMessage();
				}
				return;
		}

		// return Http::redirect(Http::makeURI('/dashboard/user/me/edit', ['balance'=>'frompay'], 'balance'));
	}

	protected function htmlTemplate(string $message){
		$this->responseType = Http::TYPE_HTML;
		$this->responseBody = <<<HTML
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8" />
    <title></title>
</head>
<body>
    $message
</body>
</html>
HTML;
	}
}