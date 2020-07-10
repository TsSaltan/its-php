<?php
/**
 */
namespace tsframe;

use tsframe\Hook;
use tsframe\Plugins;
use tsframe\module\Meta;
use tsframe\view\HtmlTemplate;
use tsframe\view\TemplateRoot;
use tsframe\view\Template;

class DashboardTheme {
	/**
	 * Ключи:
	 * theme => текущий css-файл с темой | null
	 * @var Meta
	 */
	protected static $themeConfig;

	public static function install(){
		Plugins::required('dashboard', 'meta');
	}

	public static function load(){
		TemplateRoot::add('dashboard', __DIR__ . DS . 'template' . DS . 'dashboard');	

		self::$themeConfig = new Meta('dashboard');
	}

	public static function config(Template $tpl){
		$tpl->var('current_theme', self::$themeConfig->get('theme'));
		$tpl->var('themes', self::getThemes());
		$tpl->inc('design_config');
	}

	public static function header(Template $tpl){
		$tpl->css('themes/' . self::$themeConfig->get('theme') . '.css');
	}

	protected static function getThemes(): array{
		$files = glob(__DIR__ . DS . 'template' . DS . 'dashboard' . DS . 'themes' . DS . '*.css');
		foreach ($files as $key => $value) {
			$files[$key] = explode('.', basename($value))[0];
		}

		return $files;
	}
}

Hook::registerOnce('plugin.load', [DashboardTheme::class, 'load']);
Hook::registerOnce('plugin.install', [DashboardTheme::class, 'install']);
Hook::register('template.dashboard.config', [DashboardTheme::class, 'config']);
Hook::register('template.dashboard.header', [DashboardTheme::class, 'header']);
