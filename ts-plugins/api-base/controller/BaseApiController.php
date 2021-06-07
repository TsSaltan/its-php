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
		try{
			try{
				$this->callActionMethod();
			} catch (ControllerException $e){
				// Если метод или контроллер не найдены, поищем в хуках
				$hookKey1 = 'api.' . $httpAction . '.' . $apiAction;
				$hookKey2 = 'api.def.' . $apiAction;

				if(Hook::exists($hookKey1)){
					Hook::call($hookKey1, [$this]);
					return;
				}
				
				if(Hook::exists($hookKey2)){
					Hook::call($hookKey2, [$this]);
					return;
				}
				
				throw new ApiException('Api method \'' . $httpAction . ' ' . $apiAction . '\' not found', Http::CODE_NOT_FOUND);
			} 
		} catch (InputException $e){
			$this->sendError('Input data validation error: ' . $e->getMessage(), Http::CODE_BAD_REQUEST, ['bad_fields' => $e->getInvalidKeys(), 'result' => 'error']);
		} catch (BaseException $e){
			$this->sendError($e->getMessage(), $e->getCode(), ['result' => 'error']);
		}
	}

	/**
	 * @deprecated
	 */
	public function defLogin(){
		return $this->sendError('API method deprecated', -1);

		$input = $this->getInput()
					  ->name('login')->required()->minLength(1)
					  ->name('password')->required()->minLength(1)
					  ->assert();

		$user = User::login($input['login'], $input['password']);
		$session = $user->createSession(false);
		$this->sendData(['result' => 'ok', 'session_key' => $session['session_key'], 'expires' => $session['expires']]);
	}

	/**
	 * @deprecated
	 */
	public function defRegister(){
		return $this->sendError('API method deprecated', -1);

		if(!UserConfig::isRegisterEnabled()){
			$this->sendError('User register error: registration disabled', 18);
			return;
		}

		$input = $this->getInput();
		$input->name('email')->email();


		if(UserConfig::isLoginEnabled()){
			$input->name('login')->login()->required();
		}
		
		if(UserConfig::isPasswordEnabled()){
			$input->name('password')->password();
		}

		$data = $input->assert();

		if(User::exists(['email' => $data['email']])){
			$this->sendError('Email already used', 10);
			return;	
		}

		if(UserConfig::isLoginEnabled() && User::exists(['login' => $data['login']])){
			$this->sendError('Login already used', 9);
			return;
		}

		Hook::call(
			'user.register.controller', 
			[$data, $input], 
			function($return) use ($data){
				if($return === false) throw new UserException('User register error: cancelled by hook', 0, ['data' => $data]);
			}, 

			function($error) use ($data){
				throw new UserException('User register error: error by hook', 0, ['error' => $error, 'data' => $data]);
			}
		);

		$user = User::register(($data['login'] ?? null), $data['email'], $data['password']);
		$session = $user->createSession(false);
		$this->sendData(['result' => 'ok', 'session_key' => $session['session_key'], 'expires' => $session['expires']]);
	}

	/**
	 * @deprecated
	 */
	public function defMe(){
		return $this->sendError('API method deprecated', -1);
		$user = $this->checkAuth();
		$this->sendData(['result' => 'ok', 'user' => $this->dumpUser($user)]);
	}

	public function checkAuth(): SingleUser {
		try {
			$input = $this->getInput()->name('session_key')->required()->assert();
			$user = User::current($input['session_key']);

			if(!$user->isAuthorized()){
				throw new AccessException('Invalid session_key', Http::CODE_UNAUTHORIZED);
			}

			return $user;

		} catch (InputException $e){
			throw new AccessException('Session_key required', Http::CODE_UNAUTHORIZED);
		}
	}

	public function dumpUser(SingleUser $user): array {
		$data = [
			'id' => $user->get('id'),
			'login' => $user->get('login'),
			'email' => $user->get('email'),
			'accessLevel' => $user->get('access'),
			'access' => UserAccess::getAccessName($user->get('access')),
		];

		Hook::call('api.user.data', [$user, &$data], function($res) use (&$data){
			if(is_array($res) && sizeof($res) > 0){
				$data = array_merge($data, $res);
			}
		}, function(){
			// error callback. nope.
		});

		return $data;
	}

	protected function getAction() : string {
		return $this->params['action'] ?? (
			str_replace(['/api/', '/api'], '', explode('?', $_SERVER['REQUEST_URI'])[0] )
		);
	}

	public function getInput(): Input {
		return Input::stdin('json');
	}
}