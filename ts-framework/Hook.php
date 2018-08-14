<?php
namespace tsframe;

class Hook{
	/**
	 * Hooks:
	 * plugin.load (string $pluginName, string pluginPath) : void // загрузка плагина
	 * plugin.install (string $pluginName, string pluginPath) : void // загрузка плагина
	 * template.render (Template $tpl) // отрисовка шаблона
	 * template.include (string $name, Template $tpl) // импорт файла в шаблон
	 * app.start
	 * app.finish
	 * app.install
	 * menu.render (string $menuName, MenuItem $menu): void
	 * menu.render.$menuName (MenuItem $menu): void
	 */
	protected static $hooks = [];

	public static function registerOnce(string $name, callable $function){
		return self::register($name, $function, true);
	}

	public static function register(string $name, callable $function, bool $once = false){
		self::$hooks[$name][] = ['function' => $function, 'once' => $once];
	}

	public static function call(string $name, array $params = [], ?callable $return = null){
		if(!isset(self::$hooks[$name])) return;
		foreach (self::$hooks[$name] as $key => $hook) {
			$func = $hook['function'];
			$result = call_user_func_array($func, $params);
			if(is_callable($return)){
				call_user_func($return, $result);
			}

			if($hook['once'] ?? true){
				unset(self::$hooks[$name][$key]);
			}

			// А нужны ли логи ???
			// Log::add('Hook['.$name.']', ['params' => $params], ['return' => $result]);
		}
	}
}