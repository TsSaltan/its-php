<?php
namespace tsframe\controller;

use tsframe\Hook;
use tsframe\Http;
use tsframe\exception\AccessException;
use tsframe\exception\ApiException;
use tsframe\exception\BaseException;
use tsframe\exception\ControllerException;
use tsframe\exception\InputException;
use tsframe\exception\UserException;
use tsframe\module\io\Input;
use tsframe\module\user\SingleUser;
use tsframe\module\user\User;
use tsframe\module\user\UserAccess;
use tsframe\module\user\UserConfig;

/**
 * routes @deprecated 
 *
 * f.e.
 * route POST /api/[login:action]
 * route POST /api/[register:action]
 * route GET|POST /api/[me:action]
 */
class BaseApiController extends AbstractAJAXController {
	use ActionToMethodTrait;

	public function getResponseBody() : string {
		return json_encode($this->responseBody, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
	}	

	public function response(){
		$apiAction = $this->getAction();
		$httpAction = Http::getRequestMethod();
		try {
			// Этот хук для всех апи запросов (например для логирования)
			Hook::call('api.*', [$this, $httpAction, $apiAction]);

			try {
				$this->callActionMethod();
			} 
			catch (ControllerException $e){
				// Если метод или контроллер не найдены, поищем в хуках
				$hookKey1 = 'api.' . $httpAction . '.' . $apiAction;
				$hookKey2 = 'api.def.' . $apiAction;


				if(Hook::exists($hookKey1)){
					Hook::call($hookKey1, [$this, $httpAction, $apiAction]);
					return;
				}
				
				if(Hook::exists($hookKey2)){
					Hook::call($hookKey2, [$this, $httpAction, $apiAction]);
					return;
				}
				
				throw new ApiException('Api method \'' . $httpAction . ' ' . $apiAction . '\' not found', Http::CODE_NOT_FOUND);
			} 
		} catch (InputException $e){
			$this->sendError('Input data validation error: ' . $e->getMessage(), Http::CODE_BAD_REQUEST, ['invalid_keys' => $e->getInvalidKeys(), 'result' => 'error']);
		} catch (BaseException $e){
			$this->sendError($e->getMessage(), $e->getCode(), ['result' => 'error']);
		}
	}

	protected function getAction() : string {
		return $this->params['action'] ?? (
			str_replace(['/api/', '/api'], '', explode('?', $_SERVER['REQUEST_URI'])[0] )
		);
	}

	public function getInput(): Input {
		return Input::stdin('json');
	}

	public function getInputData(){
		try {
			return $this->getInput()->assert();
		}
		catch (BaseException $e){
			return null;
		}
	}
}