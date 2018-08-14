<?php
namespace tsframe\controller;

use tsframe\Http;
use tsframe\module\Meta;
use tsframe\module\user\User;
use tsframe\module\user\UserAccess;
use tsframe\module\user\SocialLogin;
use tsframe\module\Paginator;
use tsframe\view\HtmlTemplate;
use tsframe\exception\RouteException;

/**
 * @route GET /dashboard/profile -> /dashboard/user/me
 * @route GET /dashboard/user -> /dashboard/user/list
 * 
 * @route GET /dashboard/[logout:action]
 * @route GET /dashboard/user/[list:action]
 * @route GET /dashboard/user/[me:user_id]
 * @route GET /dashboard/user/[me:user_id]/[profile|edit:action]
 * @route GET /dashboard/user/[i:user_id]
 * @route GET /dashboard/user/[i:user_id]/[profile|edit|delete:action]
 */ 
class UserDashboard extends Dashboard {
	public function response(){
		$action = $this->getAction();
		$user = User::current();

		if(isset($this->params['action']) && $this->params['action'] == 'logout'){
			$user->closeSession(true);
			return Http::redirect('/dashboard/');
		}
		elseif(isset($this->params['action']) && $this->params['action'] == 'list'){
			UserAccess::assert($user, 'user.list');
			$this->params['action'] = 'user_list';
			$this->vars['title'] = 'Список пользователей';
			$this->vars['userList'] = new Paginator(User::get(), 10);	
			return parent::response();

		} elseif(isset($this->params['user_id'])) {
			$self = $this->params['user_id'] == 'me' || $this->params['user_id'] == $user->get('id');

			if(!$self){
				$select = User::get(['id' => $this->params['user_id']]);
				if(!isset($select[0])){
					throw new RouteException('Invalid user id: ' . $this->params['user_id']);
				}
				$selectUser = $select[0];
			} else {
				$selectUser = User::current();
			}

			$this->vars['selectUser'] = $selectUser;	
			$this->vars['self'] = $self;	

			switch ($this->params['action'] ?? 'profile') {
				case 'edit':
					if($self){
						UserAccess::assert($user, 'user.self');
						$this->vars['title'] = 'Редактирование профиля';

						$meta = new Meta('user', $user->get('id'));
						$tempPass = $meta->get('temp_password');
						if(strlen($tempPass) > 1){
							$this->vars['tempPass'] = $tempPass;
						}

						$this->vars['socialLogin'] = SocialLogin::getWidgetCode();

						if(isset($_GET['social'])){
							$this->vars['socialTab'] = true;
							if($_GET['social'] == 'success'){
								$this->vars['alert']['success'] = 'Социальная сеть добавлена';
							} else {
								$this->vars['alert']['danger'] = 'Ошибка при добавлении социальной сети, возможно она уже где-то используется.';
							}
						}
					} else {
						UserAccess::assert($user, 'user.edit');
						$this->vars['title'] = 'Редактирование пользователя №' . $selectUser->get('id');
					}

					$this->vars['social'] = SocialLogin::getUserAccounts($selectUser);
					$this->params['action'] = 'user_edit';
					return parent::response();				

				case 'delete':
					if($self) UserAccess::assert($user, 'user.self');
					else UserAccess::assert($user, 'user.delete');
					$this->params['action'] = 'user_delete';
					$this->vars['title'] = 'Удаление пользователя';
					return parent::response();			

				case 'profile':
					if($self){
						UserAccess::assert($user, 'user.self');
						$this->vars['title'] = 'Ваш профиль';
					}
					else {
						UserAccess::assert($user, 'user.view');
						$this->vars['title'] = 'Профиль пользователя';
					}
					$this->params['action'] = 'user_profile';
					return parent::response();			

			}
		}
		
		throw new RouteException('Invalid user route');
	}
}