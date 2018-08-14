<?php
namespace tsframe\controller;

use tsframe\exception\BaseException;
use tsframe\exception\ValidateException;
use tsframe\Http;
use tsframe\module\Meta;
use tsframe\module\user\User;
use tsframe\module\user\UserAccess;
use tsframe\utils\io\Validator;

/**
 * @route POST /ajax/[meta:part]/[:action]
 */
class AJAX extends AbstractAJAXController{
	use AccessTrait;

	protected $responseType = Http::TYPE_JSON;

	public function response(){
		$action = $this->params['part'] . '/' . $this->params['action'];
		$input = Validator::post();
		$user = User::current();
		
		try{
			// Проверка авторизации
			switch ($action) {
				case 'user/login':
				case 'user/register':
					if($user->isAuthorized()){
						return $this->sendError('You already authorized');
					}
					break;

				default:
					if(!$user->isAuthorized()){
						return $this->sendError('Auth required');
					}
			}

			switch ($action) {
				case 'meta/save':
					$this->access(UserAccess::Admin);
					$data = $input->name('parent')
								   ->required()
								    ->int()
								  ->name('meta')
								    ->required()
								    ->array()
								  ->assert();

					$meta = new Meta($data['parent']);
					foreach ($data['meta'] as $key => $value) {
						$meta->set($key, $value);
					}

					$this->sendOK();
				break;

				case 'user/login':
					$data = $input->validateLogin()
								  ->validatePassword()
						  	  	  ->assert();

					$user = User::login($data['login'], $data['password']);
					if($user->isAuthorized() && $user->createSession()){
						$this->sendOK();
					}
					else {
						$this->sendError('User login error', 12);
					}

					break;			


				case 'user/register':
					$data = $input->validateLogin()
								  ->validatePassword()
								  ->validateEmail()
						  		  ->assert();

					if(User::exists(['email' => $data['email'], 'login' => $data['login']])){
						$this->sendError('Login or email already used', 10);
					}

					$user = User::register($data['login'], $data['email'], $data['password']);
					if($user->isAuthorized() && $user->createSession()){
						$this->sendOK();
					}
					else{
						$this->sendError('User register error', 11);
					}

					break;
					

				case 'user/edit':
					$this->access(UserAccess::User);
					$data = $input->validateLogin()
								  ->validateEmail()
						  		  ->assert();

					foreach(['login', 'email'] as $item){
						if($data[$item] != $user->get($item)){
							if(User::exists([$item => $data[$item]])){
								return $this->sendError('Field value already exists', 14, ['fields' => $item]);
							} else {
								$user->set($item, $data[$item]);
							}
						}
					}

					$this->sendMessage('Saved!', 1);
					break;

				case 'user/changePassword':
					$this->access(UserAccess::User);
					$data = $input->validatePassword('new_password')
									->name('current_password')
									->required()
						  		  ->assert();

					if(User::exists([ 'login' => $user->get('login'), 'password' => $data['current_password'] ], 'AND') && $user->set('password', $data['new_password'])){
						$this->sendMessage('Password changed!', 2);
					} else {
						$this->sendError('Can not change password', 15);
					}

					break;

				default:
					$this->sendError('Invalid action "'.$action.'"');
			}
		} catch (BaseException $e){
			if($e instanceof ValidateException){
				$this->sendError('Validation error', 13, ['fields' => $e->getInvalidKeys()]);
			} else {
				$this->sendError(get_class($e) . ': ' . $e->getMessage());
			}
		}

	}
}