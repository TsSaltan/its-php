<?php
namespace tsframe\controller;

use tsframe\Config;
use tsframe\Http;
use tsframe\Plugins;
use tsframe\module\io\Input;
use tsframe\view\HtmlTemplate;

/**
 * Контроллер для установщика фреймворка
 */
class InstallController extends AbstractController{
	protected $fields = [];
	public function setRequiredFields(array $fields){
		$this->fields = $fields;
	}

	/**
	 * Записываем данные в хранилище настроек
	 */
	public function checkPost(){
		if(isset($_POST['param']) && is_array($_POST['param'])){
			if(isset($_POST['param']['plugins'])){
				$_POST['param']['plugins']['disabled'] = array_keys($_POST['param']['plugins']['disabled']) ?? [];
			}

			foreach ($_POST['param'] as $key => $value) {
				Config::set($key, $value);
			}
		}
	}

	public function response(){
		// 3 "шага", 1й - указываем используемые плагины, 2й - заполняем необходимые поля, 3й - приложение утсановлено
		$step = max(1, min(3, $_GET['step'] ?? 1));
		if(sizeof($this->fields) == 0) $step = 3;
		elseif($step == 3)$step = 2;

		$tpl = new HtmlTemplate('default', 'install');
		$tpl->setHooksUsing(false);
		$tpl->var('fields', $this->fields);
		$tpl->var('step', $step);
		$tpl->var('plugins', Plugins::getList());
		$tpl->var('disabled', Plugins::getDisabled());
		return $tpl->render();
	}
}