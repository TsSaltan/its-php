<?php
namespace tsframe\controller;

use tsframe\Config;
use tsframe\Http;
use tsframe\exception\ControllerException;
use tsframe\module\Meta;
use tsframe\module\io\Input;
use tsframe\module\io\Output;
use tsframe\module\user\SocialLogin;
use tsframe\module\user\User;
use tsframe\module\user\UserAccess;
use tsframe\module\user\UserConfig;
use tsframe\view\DashboardTemplate;
use tsframe\view\HtmlTemplate;

/**
 * @route GET /dashboard -> /dashboard/index
 * @route GET /dashboard/ -> /dashboard/index
 * @route GET /dashboard/login -> /dashboard/auth#login
 * @route GET /dashboard/register -> /dashboard/auth#register
 * 
 * @route GET /dashboard/[auth|index|logout|config|config:action]
 * @route POST /dashboard/[config:action]
 * @route POST /dashboard/config/[theme|siteinfo:action]
 */
class Dashboard extends AbstractController{
	use ActionToMethodTrait;

	/**
	 * Vars for template
	 * @var array
	 */
	protected $vars = [];

	/**
	 * @var UserSingle
	 */
	protected $currentUser;

	/**
	 * @var DashboardTemplate
	 */
	protected $tpl;

	protected function apply(){
		$action = $this->getAction();
		$this->currentUser = User::current();

		// Неавторизованных на авторизацию
		if(!$this->currentUser->isAuthorized() && $action != 'auth'){
			$currentUrl = $_SERVER['REQUEST_URI'];
			Http::redirect(Http::makeURI('/dashboard/auth', ['redirect' => $currentUrl]));
		} 
		// Если авторизованный открывает страницу логина - перекидываем на redirect или на главную
		elseif($this->currentUser->isAuthorized() && $action == 'auth'){
			// Редирект только внутри домена
			if(isset($_GET['redirect']) && strpos($_GET['redirect'], '://') === false){
				$url = $_GET['redirect'];
			} else {
				$url = Http::makeURI('/dashboard/');
			}

			Http::redirect($url);
		}
	}

	/**
	 * Страница авторизации
	 * @uri GET /dashboard/auth
	 * @access *
	 **/
	public function getAuth(){	
		if(isset($_GET['error']) && $_GET['error'] == 'social'){
			$this->vars['alert']['danger'][] = 'Невозможно войти через данный аккаунт, привязанный к нему e-mail уже зарегистрирован.';
		}
		
		if(isset($_GET['from']) && $_GET['from'] == 'auth' && !UserConfig::isLoginOnRegister()){
			if(UserConfig::isEmailOnRegister()){
				$this->vars['alert']['info'][] = 'Данные для авторизации отправлены на e-mail';
			} else {
				$this->vars['alert']['info'][] = 'Введите данные указанные при регистрации';
			}
		}
	}

	/**
	 * Выход из аккаунта 
	 * @uri GET /dashboard/logout
	 * @access *
	 **/
	public function getLogout(){
		$this->currentUser->closeSession();
		return Http::redirect(Http::makeURI('/dashboard/'));
	}

	/**
	 * Отображение файла настроек
	 * @uri GET /dashboard/config
	 * @access user.editConfig
	 **/
	public function getConfig(){
		UserAccess::assertCurrentUser('user.editConfig');
		$this->vars['title'] = 'Редактирование системных настроек';
		$this->vars['systemConfigs'] = Config::get('*');

		if(isset($_GET['save']) && $_GET['save'] == 'success'){
			$this->vars['alert']['success'][] = 'Настройки успешно сохранены';
		}
	}

	/**
	 * Сохранение файла настроек
	 * @uri POST /dashboard/config
	 * @access user.editConfig
	 **/
	public function postConfig(){
		UserAccess::assertCurrentUser('user.editConfig');
		$data = Input::post()
					->name('config')
						->json()
					->assert();

		Config::set('*', json_decode($data['config'], true));

		return Http::redirect(Http::makeURI('/dashboard/config', ['save' => 'success']));
	}

	/**
	 * Сохранение файла настроек
	 * @uri POST /dashboard/config/theme
	 * @access user.editConfig
	 **/
	public function postTheme(){
		UserAccess::assertCurrentUser('user.editConfig');
		$data = Input::post()
			->name('theme')->required()->minLength(0)
			->assert();
		
		$this->tpl->getDesigner()->setCurrentTheme($data['theme']);	
		return Http::redirect(Http::makeURI('/dashboard/config#theme'));
	}

	/**
	 * Сохранение файла настроек
	 * @uri POST /dashboard/config/siteinfo
	 * @access user.editConfig
	 **/
	public function postSiteinfo(){
		UserAccess::assertCurrentUser('user.editConfig');
		$data = Input::post()
			->name('sitename')->required()->minLength(0)
			->name('sitehome')->required()->minLength(0)
			->name('siteicon')->required()->minLength(0)
		->assert();
		
		$this->tpl->getDesigner()->setSitename($data['sitename']);	
		$this->tpl->getDesigner()->setSitehome($data['sitehome']);	
		$this->tpl->getDesigner()->setSiteicon($data['siteicon']);	

		return Http::redirect(Http::makeURI('/dashboard/config#siteinfo'));
	}

	public function response(){
		// Переменные, которые будут доступны всему шаблону
		$this->vars['registerEnabled'] = UserConfig::isRegisterEnabled();
		$this->vars['socialEnabled'] = UserConfig::isSocialEnabled();
		$this->vars['loginEnabled'] = UserConfig::isLoginEnabled();
		$this->vars['passwordEnabled'] = UserConfig::isPasswordEnabled();
		$this->vars['socialLoginTemplate'] = (UserConfig::isSocialEnabled()) ? SocialLogin::getWidgetCode() : null;

		$action = $this->getAction();

		$this->tpl = new DashboardTemplate('dashboard', $action);

		try {
			$this->callActionMethod();
		} catch (ControllerException $e){
			
		}
		
		$this->tpl->vars($this->vars);
		$this->responseBody = $this->tpl->render();
		$this->responseType = 'text/html';
	}

	// f.e. action tests/new -> template tests_new
	protected function getAction(string $default = 'index') : string {
		return str_replace(['/', '\\', '|', '..'], '_', $this->params['action'] ?? $default);
	}
}