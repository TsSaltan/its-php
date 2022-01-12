<?php
namespace tsframe\controller;

use tsframe\App;
use tsframe\Plugins;
use tsframe\exception\BaseException;
use tsframe\exception\TemplateException;
use tsframe\view\HtmlTemplate;
use tsframe\view\Template;
use tsframe\view\TemplateRoot;

/**
 * Контроллер для ошибок
 */
class ErrorController extends AbstractController {
	
	/**
	 * @var BaseException
	 */
	private $error;

	public function __construct(BaseException $e){
		$this->error = $e;
	}

	public function response(){
		$code = $this->error->getCode();

		$tpl = Template::error();

		$tpl->setHooksUsing(false);
		$tpl->vars(['code' => $code, 'hasDashboard' => Plugins::isEnabled('dashboard')]);

		if(App::isDev()){
			$tpl->vars(['debug' => $this->error->getDump()]);
		}

		return $tpl->render();
	}
}