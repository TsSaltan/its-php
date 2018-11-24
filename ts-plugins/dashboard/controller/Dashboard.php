<?php
namespace tsframe\controller;

use tsframe\Config;
use tsframe\Http;
use tsframe\module\io\Output;
use tsframe\module\io\Input;
use tsframe\module\Meta;
use tsframe\module\user\User;
use tsframe\module\user\UserAccess;
use tsframe\view\HtmlTemplate;

/**
 * @route GET /dashboard -> /dashboard/index
 * @route GET /dashboard/ -> /dashboard/index
 * 
 * @route GET /dashboard/[login|index|logout|config:action]
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
		if(!$this->currentUser->isAuthorized() && $action != 'login'){
			Http::redirect(Http::makeURI('/dashboard/login'));
		} elseif($this->currentUser->isAuthorized() && $action == 'login'){
			Http::redirect(Http::makeURI('/dashboard/'));
		}
 		
 		// @todo redirects
			/*if(isset($_GET['redirect'])){
				setcookie('RE', $_GET['redirect'], time() + 60*60, '/');
				$this->vars['alert']['info'] = 'Необходимо авторизоваться!';
			}
			$this->param['action'] = 'login';
			return false;
		} else {
			if(isset($_COOKIE['RE'])){
				setcookie('RE', null, -1, '/');
				Http::redirect($_COOKIE['RE']);
			}

			return true;
		}*/
	}

	public function getLogout(){
		$this->currentUser->closeSession(true);
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
		$this->callActionMethod();

		$action = $this->getAction();
		
		$meta = new Meta('dashboard');
		$siteName = $meta->get('sitename');
		$this->vars['siteName'] = is_null($siteName) ? $_SERVER['SERVER_NAME'] : $siteName;
		Output::of($this->vars['siteName'])->xss()->quotes();
		
		$siteHome = $meta->get('sitehome');
		$this->vars['siteHome'] = is_null($siteHome) ? '/' : Http::makeURI($siteHome);
		Output::of($this->vars['siteHome'])->xss()->quotes();
		
		$siteIcon = $meta->get('siteicon');
		$this->vars['siteIcon'] = is_null($siteIcon) ? 'fa-home' : $siteIcon;
		Output::of($this->vars['siteIcon'])->xss()->quotes();

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