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

	protected function hasErrorsInFields(): bool {
		foreach ($this->fields as $field) {
			if($field->getType() == 'error'){
				return true;
			}
		}	

		return false;
	}

	protected $errors = [];
	public function setErrors(array $errors){
		$this->errors = $errors;
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

		// Ошибки плагинов решаем на 1 шаге
		if(sizeof($this->errors) > 0) $step = 1;

		// Ошибки конфигурации плагинов решаем на 2 шаге
		if($step > 1 && $this->hasErrorsInFields()) $step = 2;


		$tpl = new HtmlTemplate('default', 'install');
		$tpl->setHooksUsing(false);
		$tpl->var('fields', $this->fields);
		$tpl->var('errors', $this->errors);
		$tpl->var('step', $step);
		$tpl->var('plugins', Plugins::getList());
		$tpl->var('disabled', Plugins::getDisabled());
		return $tpl->render();
	}
}