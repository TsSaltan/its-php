<?php
namespace tsframe\controller;

use tsframe\Hook;
use tsframe\Http;
use tsframe\exception\BaseException;
use tsframe\exception\InputException;
use tsframe\exception\RouteException;
use tsframe\exception\UserException;
use tsframe\module\Meta;
use tsframe\module\io\Input;
use tsframe\module\user\User;
use tsframe\module\user\UserAccess;
use tsframe\module\user\UserConfig;

/**
 * @route POST /ajax/[user:part]/[:action]
 */
class UserAJAX extends AbstractAJAXController{
	use AccessTrait;

	public function response(){
		$action = $this->params['part'] . '/' . $this->params['action'];
		$input = Input::post();
		$user = User::current();

		try{
			$input->referer();
			if(isset($_REQUEST['id'])){
				$select = array_values(User::get(['id' => $_REQUEST['id']]));
				if(isset($select[0])){
					$selectUser = $select[0];
				} else {
					throw new RouteException('Invalid user id');
				}
			}
		
			// Проверка авторизации
			switch ($action) {
				case 'user/login':
				case 'user/register':
					if($user->isAuthorized()){
						return $this->sendError('You already authorized', 1);
					}
					break;

				default:
					if(!$user->isAuthorized()){
						return $this->sendError('Auth required', 0);
					}
			}

			switch ($action) {
				case 'user/login':
					$data = $input->name('login')->required()
								  ->name('password')->password()
						  	  	  ->assert();

					try{
						$user = User::login($data['login'], $data['password']);
						if($user->isAuthorized() && $user->createSession()){
							$this->sendOK();
							break;
						}
					} catch(UserException $e){
					}

					$this->sendError('User login error', 12);

					break;			


				case 'user/register':
					if(!UserConfig::isRegisterEnabled()){
						$this->sendError('User register error: registration disabled', 18);
						break;
					}

					$input->name('email')->email();

					if(UserConfig::isPasswordEnabled()){
						$input->name('password')->password()->required();
					}

					if(UserConfig::isLoginEnabled()){
						$input->name('login')->login()->required();
					}

					$data = $input->assert();

					if(User::exists(['email' => $data['email']])){
						$this->sendError('Email already used', 10);
						break;
					}

					if(UserConfig::isLoginEnabled() && User::exists(['login' => $data['login']])){
						$this->sendError('Login already used', 9);
						break;
					}

					try{
						Hook::call('user.register.controller', [$data, $input], function($return) use ($data){
							if($return === false) throw new UserException('User register error: cancelled by hook', 0, ['data' => $data]);
						}, function($error) use ($data){
							throw new UserException('User register error: error by hook', 0, ['error' => $error, 'data' => $data]);
						});

						$user = User::register(($data['login'] ?? null), $data['email'], ($data['password'] ?? null));
						if($user->isAuthorized() && $user->createSession()){
							$this->sendOK();
							break;
						}
					} catch(UserException $e){
						$this->sendError('User register error: ' . $e->getMessage(), 11);
						break;
					}

					$this->sendError('User register error', 11);
					break;

				case 'user/edit':
					$input->name('id')->required()->int()
						  ->name('email')->email()
						  ->name('access')->required()->int();

					if(UserConfig::isLoginEnabled()){
						$input->name('login')->login()->required();
					}

					$data = $input->assert();

					if($data['id'] == $user->get('id')){
						// Право на редактирование собственного профиля
						$this->access(UserAccess::getAccess('user.self'));
					}
					else {
						// Право на редактирование профиля другого пользователя
						$this->access(UserAccess::getAccess('user.edit'));
					}

					// Смена email
					if($data['email'] != $selectUser->get('email')){
						if(User::exists(['email' => $data['email']])){
							return $this->sendError('Email already exists', 10, ['fields' => 'email']);
						} else {
							$selectUser->set('email', $data['email']);
						}
					}

					// Смена логина
					if(UserConfig::isLoginEnabled() && $data['login'] != $selectUser->get('login')){
						if(User::exists(['email' => $data['login']])){
							return $this->sendError('Login already exists', 9, ['fields' => 'login']);
						} else {
							$selectUser->set('login', $data['login']);
						}
					}
					
					// Смена прав доступа				
					if(isset($data['access']) && intval($selectUser->get('access')) != intval($data['access'])){
						if(UserAccess::checkUser($user, 'user.editAccess') && in_array($data['access'], UserAccess::getArray())){
							$selectUser->set('access', $data['access']);
						} else {
							return $this->sendError('Can not change user access', 14, ['fields' => 'access']);
						}
					}

					$this->sendMessage('OK', 1);
					break;

				case 'user/changePassword':
					$this->access(UserAccess::getAccess('user.self'));
					$data = $input->name('new_password')
									->required()
									->minLength(1)
					   			  ->name('current_password')
									->required()
						  		  ->assert();

					if(User::exists(['id' => $user->get('id'), 'password' => $data['current_password'] ], 'AND') && $user->set('password', $data['new_password'])){
						$this->sendMessage('Password changed!', 2);
					} else {
						$this->sendError('Can not change password', 15);
					}

					$meta = new Meta('user', $user->get('id'));
					$meta->set('temp_password', null);

					break;

				case 'user/resetPassword':
					$data = $input->name('id')
									->int()
						  		  ->assert();

					if($data['id'] == $user->get('id')) $this->access(UserAccess::getAccess('user.self'));
					else $this->access(UserAccess::getAccess('user.edit'));

					$newPass = uniqid('pass_' . rand(0,100));
					if($selectUser->set('password', $newPass)){
						$this->sendMessage($newPass, 3);
					} else {
						$this->sendError('Can not change password', 15);
					}

					$meta = new Meta('user', $selectUser->get('id'));
					$meta->set('temp_password', null);

					break;

				case 'user/closeSessions':
					$data = $input->name('id')
									->required()
									->int()
						  		  ->assert();

					if($data['id'] == $user->get('id')) $this->access(UserAccess::getAccess('user.self'));
					else $this->access(UserAccess::getAccess('user.edit'));

					
					if($selectUser->closeAllSessions()){
						$this->sendMessage('Sessions are closed', 4);
					} else {
						$this->sendError('Can not close sessions', 16);
					}

					break;

				case 'user/deleteSocial':
					$data = $input->name('id')
									->required()
									->int()
								  ->name('network')
									->required()
						  		  ->assert();

					if($data['id'] == $user->get('id')) $this->access(UserAccess::getAccess('user.self'));
					else $this->access(UserAccess::getAccess('user.edit'));

					
					$meta = new Meta('user', $selectUser->get('id'));
					$meta->set('social_' . $data['network'], null);
					$this->sendMessage('Social account removed', 6);

					break;
				
				case 'user/delete':
					$data = $input->name('id')
									->required()
									->int()
						  		  ->assert();

					if($data['id'] == $user->get('id')) $this->access(UserAccess::getAccess('user.self'));
					else $this->access(UserAccess::getAccess('user.delete'));

					if($selectUser->delete()){
						$this->sendMessage('User deleted', 5);
					} else {
						$this->sendError('Can not delete user', 17);
					}

					break;

				default:
					$this->sendError('Invalid action "'.$action.'"');
			}
		} catch (InputException $e){
			if($e instanceof InputException){
				$this->sendError('Validation error', 13, ['fields' => $e->getInvalidKeys()]);
			} else {
				$this->sendError(get_class($e) . ': ' . $e->getMessage());
			}
		}

	}
}