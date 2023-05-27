<?php
namespace tsframe\controller;

use tsframe\Config;
use tsframe\Http;
use tsframe\exception\ControllerException;
use tsframe\module\Meta;
use tsframe\module\io\Input;
use tsframe\module\io\Output;
use tsframe\module\locale\Lang;
use tsframe\module\menu\Menu;
use tsframe\module\menu\MenuItem;
use tsframe\module\user\SocialLogin;
use tsframe\module\user\User;
use tsframe\module\user\UserAccess;
use tsframe\module\user\UserConfig;
use tsframe\view\DashboardTemplate;
use tsframe\view\HtmlTemplate;

/**
 * @route GET /dashboard/login -> /dashboard/auth#login
 * @route GET /dashboard/register -> /dashboard/auth#register
 * 
 * @route GET /dashboard/
 * @route GET /dashboard
 * 
 * @route GET /dashboard/[auth|logout|config|config:action]
 * @route GET /dashboard/[auth-restore:action]
 * @route GET /dashboard/[auth-restore:action]/[:sessionKey]
 * @route POST /dashboard/[config:action]
 * @route POST /dashboard/config/[theme|siteinfo|setmode|lang:action]
 */
class Dashboard extends AbstractController {
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
		if(!$this->currentUser->isAuthorized() && !($action == 'auth' || $action == 'auth-restore')){
			$currentUrl = $_SERVER['REQUEST_URI'];
			Http::redirect(Http::makeURI('/dashboard/auth', ['redirect' => $currentUrl]));
		} 
		// Если авторизованный открывает страницу логина - перекидываем на redirect или на главную
		elseif($this->currentUser->isAuthorized() && ($action == 'auth' || $action == 'auth-restore')){
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
		return Http::redirectURI('/dashboard/');
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
		$this->vars['langList'] = Lang::getList();

		$this->vars['langData'] = [];
		$this->vars['langEditor'] = false;
		$this->vars['langDataKeys'] = [];
		$this->vars['langDataDelimeter'] = '/';

		if(is_dir(APP_TRANSLATIONS)){
			$this->vars['langEditor'] = true;
			$this->vars['langData'] = $this->loadLangFiles();

			foreach(Lang::getList() as $lang){
				$this->vars['langData'][$lang] = $this->getMultilevelKeys($this->vars['langData'][$lang]);
				$this->vars['langDataKeys'] = array_merge($this->vars['langDataKeys'], array_keys($this->vars['langData'][$lang]));
			}		

			$this->vars['langData'] = Output::of($this->vars['langData'])->quotes()->getData();
			$this->vars['langDataKeys'] = array_unique($this->vars['langDataKeys']);	
			$this->vars['langDataKeys'] = Output::of($this->vars['langDataKeys'])->quotes()->getData();
			sort($this->vars['langDataKeys']);
		}

		if(isset($_GET['save']) && $_GET['save'] == 'success'){
			$this->vars['alert']['success'][] = 'Настройки успешно сохранены';
		}
	}

	public function getAuthRestore(){
		if(isset($this->params['sessionKey'])){
			$user = User::current($this->params['sessionKey']);
			if($user->isAuthorized()){
				$user->closeSession();
				$user->createSession(true);
				return Http::redirectURI('/dashboard/user/me/edit', [], 'password');
			}
		}

		if(isset($_GET['from']) && $_GET['from'] == 'auth'){
			$this->vars['alert']['info'] = __('message/auth-link-sent');
		}
	}

	protected function loadLangFiles(): array {
		$data = [];
		foreach(Lang::getList() as $lang){
			$data[$lang] = [];
			$file = APP_TRANSLATIONS . $lang . '.json';

			if(file_exists($file)){
				$langData = json_decode(file_get_contents($file), true);
				if(is_array($langData)){
					$data[$lang] = $langData;
				}
			}
		}

		return $data;
	}

	protected function getMultilevelKeys(array $array, array $prevKeys = [], string $delimeter = '/'){
		$return = [];
		foreach($array as $k => $v){
			$keys = $prevKeys;
			$keys[] = $k;
			$key = implode($delimeter, $keys);

			if(is_array($v)){
				$return += $this->getMultilevelKeys($v, $keys, $delimeter);				
			} else {
				$return[$key] = $v;				
			}
		}

		return $return;
	}

	/**
	 * Сохранение файла настроек
	 * @uri POST /dashboard/lang
	 * @access user.editConfig
	 **/
	public function postLang(){
		UserAccess::assertCurrentUser('user.editConfig');
		if(is_dir(APP_TRANSLATIONS)){
			$data = Input::post()
						->name('translate')
							->array()
						->assert();

			$langs = Lang::getList();
			$langData = $this->loadLangFiles();


			if(isset($_POST['newkey']) && strlen($_POST['newkey']) > 0 && isset($_POST['new']) && is_array($_POST['new'])){
				foreach($langs as $lang){
					if(!isset($_POST['new'][$lang]) || strlen($_POST['new'][$lang]) == 0) continue;

					$data['translate'][$lang][$_POST['newkey']] = $_POST['new'][$lang];
				}
			}

			foreach($langs as $lang){
				if(!isset($data['translate'][$lang])) continue;

				foreach($data['translate'][$lang] as $lKey => $lVal){
					$keys = explode('/', $lKey);
					$item = &$langData[$lang];

					foreach($keys as $i => $k){
						$isLast = $i == (sizeof($keys)-1);

						if(!isset($item[$k])){
							$item[$k] = ($isLast) ? $lVal : [];
						}

						if($isLast){
							if((is_array($lVal) && sizeof($lVal) == 0) || (strlen($lVal) == 0)){
								unset($item[$k]);
							} else {
								$item[$k] = $lVal;
							}
							break;
						}

						$item = &$item[$k];					
					}
				}
			}

			foreach($langs as $lang){
				$file = APP_TRANSLATIONS . strtolower($lang) . '.json';
				if(isset($langData[$lang])){
					file_put_contents($file, json_encode($langData[$lang], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
				}
			}
		}

		return Http::redirect(Http::makeURI('/dashboard/config', ['save' => 'success'], 'lang'));
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
	 * @uri POST /dashboard/config/setmode
	 * @access user.editConfig
	 **/
	public function postSetmode(){
		UserAccess::assertCurrentUser('user.editConfig');
		$data = Input::post()
			->name('dev_mode')->required()->minLength(0)->numeric()
			->name('install_mode')->required()->minLength(0)->numeric()
		->assert();

		Config::set('dev_mode', $_POST['dev_mode'] == 1);
		Config::set('install_mode', $_POST['install_mode'] == 1);

		return Http::redirect(Http::makeURI('/dashboard/config#setmode'));
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
		// Автоматическое перенаправление на первый пункт меню
		if(strlen($this->getAction('')) == 0){
			Menu::render('dashboard-sidebar', function(){}, function(MenuItem $menu, string $subMenu, int $level){ 
				if(UserAccess::checkCurrentUser($menu->getData('access'))){
					Http::redirect($menu->getData('url'));
				}
			});

			// Если не будет пользовательских пунктов меню, то будет перенаправление на админское меню (если доступно)
			Menu::render('dashboard-admin-sidebar', function(){}, function(MenuItem $menu, string $subMenu, int $level){ 
				if(UserAccess::checkCurrentUser($menu->getData('access'))){
					Http::redirect($menu->getData('url'));
				}
			});
		}

		// Переменные, которые будут доступны всему шаблону
		$this->vars['registerEnabled'] = UserConfig::isRegisterEnabled();
		$this->vars['socialEnabled'] = UserConfig::isSocialEnabled();
		$this->vars['loginEnabled'] = UserConfig::isLoginEnabled();
		$this->vars['passwordEnabled'] = UserConfig::isPasswordEnabled();
		$this->vars['isRestorePassword'] = UserConfig::isRestorePassword();
		$this->vars['socialLoginTemplate'] = (UserConfig::isSocialEnabled()) ? SocialLogin::getWidgetCode() : null;

		$this->vars['dev_mode'] = Config::get('dev_mode');
		$this->vars['install_mode'] = Config::get('install_mode');

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
	protected function getAction(string $default = '') : string {
		return str_replace(['/', '\\', '|', '..'], '_', $this->params['action'] ?? $default);
	}

	public static function getSiteName(): string {
		$meta = new Meta('dashboard');
		$siteName = $meta->get('sitename');
		$siteName = is_null($siteName) ? $_SERVER['SERVER_NAME'] : $siteName;
		Output::of($siteName)->xss()->quotes();
		return $siteName;
	}

	public static function getSiteHome(): string {
		$meta = new Meta('dashboard');
		$siteHome = $meta->get('sitehome');
		$siteHome = is_null($siteHome) ? '/' : Http::makeURI($siteHome);
		Output::of($siteHome)->xss()->quotes();
		return $siteHome;
	}

	public static function getSiteIcon(): string {
		$meta = new Meta('dashboard');
		$siteIcon = $meta->get('siteicon');
		$siteIcon = is_null($siteIcon) ? 'fa-home' : $siteIcon;
		Output::of($siteIcon)->xss()->quotes();
		return $siteIcon;
	}
}