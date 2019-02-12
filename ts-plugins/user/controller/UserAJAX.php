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
					$data = $input->name('login')->login()
								  ->name('password')->password()
								  ->name('email')->email()
						  		  ->assert();

					if(User::exists(['email' => $data['email']]) || User::exists(['login' => $data['login']])){
						$this->sendError('Login or email already used', 10);
						break;
					}

					try{
						Hook::call('user.register.controller', [$data, $input], function($return) use ($data){
							if($return === false) throw new UserException('User register error: cancelled by hook', 0, ['data' => $data]);
						}, function($error){
							throw new UserException('User register error: error by hook', 0, ['error' => $error, 'data' => $data]);
						});

						$user = User::register($data['login'], $data['email'], $data['password']);
						if($user->isAuthorized() && $user->createSession()){
							$this->sendOK();
							break;
						}
					} catch(UserException $e){
						
					}

					$this->sendError('User register error', 11);
					break;

				case 'user/edit':
					$data = $input->name('id')
									->required()
									->int()
								  ->name('login')
								  	->login()
								  ->name('email')
								  	->email()
								  ->name('access')
								  	->required()
								  	->int()
						  		  ->assert();

					if($data['id'] == $user->get('id')) $this->access(UserAccess::getAccess('user.self'));
					else $this->access(UserAccess::getAccess('user.edit'));

					foreach(['login', 'email'] as $item){
						if($data[$item] != $selectUser->get($item)){
							// Проверяем, есть ли такие логин и мэйл
							if(User::exists([$item => $data[$item]])){
								return $this->sendError('Field value already exists', 14, ['fields' => $item]);
							} else {
								$selectUser->set($item, $data[$item]);
							}
						}
					}

				
					if(isset($data['access']) && UserAccess::checkUser($user, 'user.editAccess') && in_array($data['access'], UserAccess::getArray())){
						$selectUser->set('access', $data['access']);
					}

					$this->sendMessage('Saved!', 1);
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

					
					if($selectUser->closeSession()){
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