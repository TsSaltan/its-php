<?php
namespace tsframe\controller;

use tsframe\Http;
use tsframe\module\Meta;
use tsframe\module\user\User;
use tsframe\module\user\UserAccess;
use tsframe\view\HtmlTemplate;
use tsframe\module\testing\TestEngine;

/**
 * @route GET /dashboard -> /dashboard/
 * 
 * @route GET /dashboard/
 * @route GET /dashboard/[login:action]
 */
class Dashboard extends AbstractController{
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
			Http::redirect('/dashboard/login');
		} elseif($this->currentUser->isAuthorized() && $action == 'login'){
			Http::redirect('/dashboard/');
		}

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

	public function response(){
		$action = $this->getAction();



		switch ($action) {


			/*case 'meta':
				$this->vars['meta'] = new Meta(0);
				break;
				*/

			default:
				if(!isset($this->vars['title'])){
					$this->vars['title'] = 'Панель управления';
				}
				break;
		}
			

		$tpl = new HtmlTemplate('dashboard', $action);
		$tpl->vars($this->vars);

		$this->responseBody = $tpl->render();
		$this->responseType = 'text/html';
	}

	// f.e. action tests/new -> template tests_new
	protected function getAction() : string {
		return str_replace(['/', '\\', '|', '..'], '_', $this->params['action'] ?? 'index');
	}
}