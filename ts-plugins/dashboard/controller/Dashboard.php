<?php
namespace tsframe\controller;

use tsframe\Config;
use tsframe\Http;
use tsframe\module\io\Output;
use tsframe\module\io\Input;
use tsframe\module\Meta;
use tsframe\module\user\User;
use tsframe\module\user\UserConfig;
use tsframe\module\user\UserAccess;
use tsframe\module\user\SocialLogin;
use tsframe\view\HtmlTemplate;

/**
 * @route GET /dashboard -> /dashboard/index
 * @route GET /dashboard/ -> /dashboard/index
 * @route GET /dashboard/login -> /dashboard/auth#login
 * @route GET /dashboard/register -> /dashboard/auth#register
 * 
 * @route GET /dashboard/[auth|index|logout|config:action]
 * @route POST /dashboard/[config:action]
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

	public function setParams(array $params){
		parent::setParams($params);

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

	public function getAuth(){	
		if(isset($_GET['error']) && $_GET['error'] == 'social'){
			$this->vars['alert']['danger'][] = 'Невозможно войти через данный аккаунт, привязанный к нему e-mail уже зарегистрирован.';
		}
	}

	public function getLogout(){
		$this->currentUser->closeSession();
		return Http::redirect(Http::makeURI('/dashboard/'));
	}

	public function getConfig(){
		UserAccess::assertCurrentUser('user.editConfig');
		$this->vars['title'] = 'Редактирование системных настроек';
		$this->vars['systemConfigs'] = Config::get('*');

		if(isset($_GET['save']) && $_GET['save'] == 'success'){
			$this->vars['alert']['success'][] = 'Настройки успешно сохранены';
		}
	}

	public function postConfig(){
		UserAccess::assertCurrentUser('user.editConfig');
		$data = Input::post()
					->name('config')
						->json()
					->assert();

		Config::set('*', json_decode($data['config'], true));

		return Http::redirect(Http::makeURI('/dashboard/config?save=success'));
	}

	public function response(){
		// Переменные, которые будут доступны всему шаблону
		$this->vars['canRegister'] = UserConfig::canRegister();
		$this->vars['canSocial'] = UserConfig::canSocial();
		$this->vars['loginUsed'] = UserConfig::isLoginUsed();
		$this->vars['socialLoginTemplate'] = (UserConfig::canSocial()) ? SocialLogin::getWidgetCode() : null;

		$this->callActionMethod();

		$action = $this->getAction();

		$tpl = new HtmlTemplate('dashboard', $action);
		$tpl->vars($this->vars);

		$this->responseBody = $tpl->render();
		$this->responseType = 'text/html';
	}

	// f.e. action tests/new -> template tests_new
	protected function getAction(string $default = 'index') : string {
		return str_replace(['/', '\\', '|', '..'], '_', $this->params['action'] ?? $default);
	}
}