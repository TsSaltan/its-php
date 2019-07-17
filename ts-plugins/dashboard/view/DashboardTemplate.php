<?php
namespace tsframe\view;

use tsframe\module\menu\Menu;

class DashboardTemplate extends HtmlTemplate {
	/**
	 * Добавить уведомление
	 * @param  string $message [description]
	 * @param  string $type    info|danger|warning|error|success
	 */
	public function alert(string $message, string $type = 'info'){
		$this->vars['alert'][$type][] = $message;
	}
}