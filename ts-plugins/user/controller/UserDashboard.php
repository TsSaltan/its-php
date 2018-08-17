<?php
namespace tsframe\controller;

use tsframe\Http;
use tsframe\module\Meta;
use tsframe\Hook;
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
	/**
	 * Selected self-user
	 * @var boolean
	 */
	protected $self;

	/**
	 * Selected user
	 * @var SingleUser
	 */
	protected $selectUser;

	public function getUserList(){
		UserAccess::assert($this->currentUser, 'user.list');
		$this->vars['title'] = 'Список пользователей';
		$this->vars['userList'] = new Paginator(User::get(), 10);
	}

	public function getUserEdit(){
		if($this->self){
			UserAccess::assert($this->currentUser, 'user.self');
			$this->vars['title'] = 'Редактирование профиля';

			$meta = $this->currentUser->getMeta();

			// Если у пользователя стоит временный пароль, отобразим сообщение
			$tempPass = $meta->get('temp_password');
			if(strlen($tempPass) > 1){
				$this->vars['tempPass'] = $tempPass;
			}

			// Если пользователь перенаправлен со страницы соц. логина
			if(isset($_GET['social'])){
				if($_GET['social'] == 'success'){
					$this->vars['alert']['success'][] = 'Социальная сеть добавлена';
				} else {
					$this->vars['alert']['danger'][] = 'Ошибка при добавлении социальной сети, возможно она уже где-то используется.';
				}

				// Включим таб со страницой соц сетей
				Hook::register('template.dashboard.user.edit', function($tpl, &$configTabs, &$activeTab){
					$activeTab = 3;
				});
			}

			$this->vars['socialLogin'] = SocialLogin::getWidgetCode();
		} else {
			UserAccess::assert($this->currentUser, 'user.edit');
			$this->vars['title'] = 'Редактирование пользователя №' . $this->selectUser->get('id');
		}

		$this->vars['social'] = SocialLogin::getUserAccounts($this->selectUser);
	}

	public function getUserDelete(){
		$this->vars['title'] = 'Удаление пользователя';
		UserAccess::assert($this->currentUser, ($this->self ? 'user.self' : 'user.delete'));
	}

	public function getUserProfile(){
		if($this->self){
			UserAccess::assert($this->currentUser, 'user.self');
			$this->vars['title'] = 'Ваш профиль';
		} else {
			UserAccess::assert($this->currentUser, 'user.view');
			$this->vars['title'] = 'Профиль пользователя';
		}
	}


	public function response(){
		$action = $this->getAction();

		if(isset($this->params['user_id'])) {
			$this->self = $this->params['user_id'] == 'me' || $this->params['user_id'] == $this->currentUser->get('id');

			if(!$this->self){
				$select = User::get(['id' => $this->params['user_id']]);
				if(!isset($select[0])){
					throw new RouteException('Invalid user id: ' . $this->params['user_id']);
				}
				$this->selectUser = $select[0];
			} else {
				$this->selectUser = User::current();
			}

			$this->vars['selectUser'] = $this->selectUser;	
			$this->vars['self'] = $this->self;	
		}

		//$this->callActionMethod();

		return parent::response();
	}

	protected function getAction(string $default = 'profile') : string {
		return 'user_' . parent::getAction($default);
	}
}