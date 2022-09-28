<?php
namespace tsframe\controller;

use tsframe\Config;
use tsframe\Hook;
use tsframe\Http;
use tsframe\exception\RouteException;
use tsframe\module\Meta;
use tsframe\module\Paginator;
use tsframe\module\io\Input;
use tsframe\module\user\SocialLogin;
use tsframe\module\user\User;
use tsframe\module\user\UserAccess;
use tsframe\module\user\UserConfig;
use tsframe\view\HtmlTemplate;
use tsframe\view\UI\UIDashboardTabPanel;

/**
 * @route GET /dashboard/profile -> /dashboard/user/me
 * @route GET /dashboard/user -> /dashboard/user/list
 * 
 * @route GET /dashboard/user/[list:action]
 * @route GET /dashboard/user/[me:user_id]
 * @route GET /dashboard/user/[me:user_id]/[profile|edit:action]
 * @route GET /dashboard/user/[i:user_id]
 * @route GET /dashboard/user/[i:user_id]/[profile|edit|delete:action]
 * @route POST /dashboard/[config:action]/user
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

	/**
	 * Отображение списа пользователей
	 */
	public function getUserList(){
		UserAccess::assert($this->currentUser, 'user.list');
		$this->vars['title'] = 'Список пользователей';
		$this->vars['userList'] = new Paginator(User::get(), 10);
	}

	/**
	 * Редактирование своего или чужого профиля
	 */
	public function getUserEdit(){
		if($this->self){
			UserAccess::assert($this->currentUser, 'user.self');
			$this->vars['title'] = 'Редактирование профиля';

			$meta = $this->currentUser->getMeta();

			// Если у пользователя стоит временный пароль, отобразим сообщение
			$tempPass = $meta->get('temp_password');
			if(strlen($tempPass) > 1){
				$this->vars['tempPass'] = $tempPass;
			} else {
				$this->vars['tempPass'] = false;
			}

			// Если пользователь перенаправлен со страницы соц. логина
			if(isset($_GET['social'])){
				if($_GET['social'] == 'success'){
					$this->vars['alert']['success'][] = 'Социальная сеть добавлена';
				} else {
					$this->vars['alert']['danger'][] = 'Ошибка при добавлении социальной сети, возможно она уже где-то используется.';
				}

				// Включим таб со страницой соц сетей
				Hook::register('template.dashboard.user.edit', function($tpl, UIDashboardTabPanel $configTabs){
					$configTabs->setActiveTab('social');
				});
			}
		} else {
			UserAccess::assert($this->currentUser, 'user.edit');
			$this->vars['title'] = 'Редактирование пользователя №' . $this->selectUser->get('id');
		}

		$this->vars['social'] = SocialLogin::getUserAccounts($this->selectUser);
	}

	/**
	 * Удаление пользователя
	 */
	public function getUserDelete(){
		$this->vars['title'] = 'Удаление пользователя';
		UserAccess::assert($this->currentUser, ($this->self ? 'user.self' : 'user.delete'));
	}

	/**
	 * Отображение профиля пользователя
	 */
	public function getUserProfile(){
		if($this->self){
			UserAccess::assert($this->currentUser, 'user.self');
			$this->vars['title'] = 'Ваш профиль';
		} else {
			UserAccess::assert($this->currentUser, 'user.view');
			$this->vars['title'] = 'Профиль пользователя';
		}
	}

	public function postUserConfig(){
		UserAccess::checkCurrentUser('user.editConfig');
		Input::post()
			  ->name('registerEnabled')->required()
			  ->name('socialEnabled')->required()
			  ->name('loginEnabled')->required()
			  ->name('passwordEnabled')->required()
			  ->name('emailOnRegister')->required()
			  ->name('loginOnRegister')->required()
			  ->name('isRestorePassword')->required()
			  ->name('access')->required()->array()
			 ->assert();

		UserConfig::setRegisterEnabled(boolval($_POST['registerEnabled']));
		UserConfig::setSocialEnabled(boolval($_POST['socialEnabled']));
		UserConfig::setLoginEnabled(boolval($_POST['loginEnabled']));
		UserConfig::setPasswordEnabled(boolval($_POST['passwordEnabled']));
		UserConfig::setEmailOnRegister(boolval($_POST['emailOnRegister']));
		UserConfig::setLoginOnRegister(boolval($_POST['loginOnRegister']));
		UserConfig::setRestorePassword(boolval($_POST['isRestorePassword']));

		Config::set('access', $_POST['access']);

		return Http::redirect(Http::makeURI('/dashboard/config', [], 'user'));
	}

	public function response(){
		$action = $this->getAction();

		if(isset($this->params['user_id'])){
			$this->self = $this->params['user_id'] == 'me' || $this->params['user_id'] == $this->currentUser->get('id');

			if(!$this->self){
				$select = array_values(User::get(['id' => $this->params['user_id']]));
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

		return parent::response();
	}

	/**
	 * Префикс для callback-функций
	 * @var string
	 */
	protected $actionPrefix = 'user_';

	/**
	 * Изменить префикс для callback-функций, зависящих от действия (action)
	 * Например, в роутере прописано "GET url/[todo:action]", а префикс = "pref", тогда
	 * имя для callback-функции будет таким: getPrefTodo
	 * @param string|null $prefix
	 */
	protected function setActionPrefix(?string $prefix){
		$this->actionPrefix = $prefix;
	}

	protected function getAction(string $default = 'profile') : string {
		return $this->actionPrefix . parent::getAction($default);
	}
}