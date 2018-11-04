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
	protected $fields;
	public function setRequiredFields(array $fields){
		$this->fields = $fields;
	}

	/**
	 * Записываем данные в хранилище настроек
	 */
	public function checkPost(){
		if(isset($_POST['param']) && is_array($_POST['param'])){
			$_POST['param']['plugins']['disabled'] = array_keys($_POST['param']['plugins']['disabled']) ?? [];
			foreach ($_POST['param'] as $key => $value) {
				Config::set($key, $value);
			}
		}
	}

	public function response(){
		$tpl = new HtmlTemplate('default', 'install');
		$tpl->var('fields', $this->fields);
		$tpl->var('plugins', Plugins::getList());
		$disabled = Config::get('plugins.disabled');
		$tpl->var('disabled', is_array($disabled) ? $disabled : []);
		return $tpl->render();
	}
}