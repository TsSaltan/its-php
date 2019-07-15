<?php
namespace tsframe\controller;

use tsframe\Hook;
use tsframe\Http;
use tsframe\exception\AccessException;
use tsframe\exception\ApiException;
use tsframe\exception\BaseException;
use tsframe\exception\UserException;
use tsframe\module\io\Input;
use tsframe\module\user\SingleUser;
use tsframe\module\user\User;
use tsframe\module\user\UserAccess;
use tsframe\module\user\UserConfig;

/**
 * @route POST /api/[login:action]
 * @route POST /api/[register:action]
 * @route GET|POST /api/[me:action]
 */
class BaseApiController extends AbstractAJAXController{
	use ActionToMethodTrait;

	public function response(){
		try{
			$apiAction = $this->getAction();
			$httpAction = Http::getRequestMethod();
			$method = $this->getActionMethod();
			$hookKey = 'api.' . $method;
			$exec = false;

			if(method_exists($this, $method)){
				$exec = true;
				$this->callActionMethod();
			}

			if(Hook::exists($hookKey)){
				$exec = true;
				Hook::call($hookKey, [$this]);
			}

			if(!$exec){
				throw new ApiException('method not found');
			}
		} catch (InputException $e){
			$this->sendError('Input validation error', 13, ['bad_fields' => $e->getInvalidKeys(), 'result' => 'error']);
		} catch (BaseException $e){
			$this->sendError('[' . basename(get_class($e)) . '] ' . $e->getMessage(), $e->getCode(), ['result' => 'error']);
		}

	}

	public function defLogin(){
		$input = Input::request()
					  ->name('login')->required()->minLength(1)
					  ->name('password')->required()->minLength(1)
					  ->assert();

		$user = User::login($input['login'], $input['password']);
		sleep(1);
		$session = $user->createSession(false);
		$this->sendData(['result' => 'ok', 'session_key' => $session['session_key'], 'expires' => $session['expires']]);
	}

	public function defRegister(){
		$input = Input::request();
		$input->name('password')->password()
			  ->name('email')->email();

		if(UserConfig::isLoginUsed()){
			$input->name('login')->login()->required();
		}
		$data = $input->assert();
		sleep(1);

		if(User::exists(['email' => $data['email']])){
			$this->sendError('Email already used', 10);
			return;	
		}

		if(UserConfig::isLoginUsed() && User::exists(['login' => $data['login']])){
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


	public function defMe(){
		$user = $this->checkAuth();

		$this->sendData(['result' => 'ok', 'user' => $this->dumpUser($user)]);
	}

	public function checkAuth(): SingleUser {
		if(!isset($_REQUEST['session_key'])) throw new AccessException('Invalid access: unauthorized', Http::CODE_UNAUTHORIZED);

		$user = User::current($_REQUEST['session_key']);
		if(!$user->isAuthorized()){
			throw new AccessException('Invalid access: bad session key', Http::CODE_UNAUTHORIZED);
		}

		return $user;
	}

	protected function dumpUser(SingleUser $user): array {
		return [
			'id' => $user->get('id'),
			'login' => $user->get('login'),
			'email' => $user->get('email'),
			'accessLevel' => $user->get('access'),
			'access' => UserAccess::getAccessName($user->get('access')),
		];
	}

	protected function getAction(string $default = 'notfound') : string {
		return $this->params['action'] ?? $default;
	}
}