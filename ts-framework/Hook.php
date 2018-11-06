<?php
namespace tsframe;

class Hook{
	/**
	 * Hooks:
	 * plugin.load (string $pluginName, string $pluginPath) : void - загрузка плагина
	 * plugin.install.required: array(path.to.param => ['type' => string|int|bool, 'description' => '...') - перед установкой плагина, необходимые полня
	 * plugin.install (string $pluginName, string $pluginPath) : void - установка плагина
	 * template.render (Template $tpl): void - отрисовка шаблона
	 * template.include (string $name, Template $tpl): void - импорт файла в шаблон
	 * app.start
	 * app.finish
	 * app.install // @deprecated
	 * menu.render (string $menuName, MenuItem $menu): void
	 * menu.render.$menuName (MenuItem $menu): void
	 * http.send (string &$body, array &$headers): void
	 * database.query (Query $dbQuery)
	 */
	protected static $hooks = [];

	public static function registerOnce(string $name, callable $function){
		return self::register($name, $function, true);
	}

	public static function register(string $name, callable $function, bool $once = false){
		self::$hooks[$name][] = ['function' => $function, 'once' => $once];
	}

	/**
	 * Вывоз хука
	 * @param  string        $name   
	 * @param  array         $params Параметры, которые будут переданы в коллбэк
	 * @param  callable|null $return Функция для возвращения результата
	 * @param  callable|null $error  Функция для возвращения исключения
	 * @param  bool|boolean  $once   Если true, то любой хук считается как "одноразовый"
	 */
	public static function call(string $name, array $params = [], ?callable $return = null, ?callable $error = null, bool $once = false){
		if(!isset(self::$hooks[$name])) return;
		foreach (self::$hooks[$name] as $key => $hook) {
			$func = $hook['function'];
			try{
				$result = call_user_func_array($func, $params);
			} catch(\Exception|\Error $e){
				if(is_callable($error)){
					call_user_func($error, $e);
				} else {
					throw $e;
				}
			}

			if(is_callable($return)){
				call_user_func($return, $result);
			}

			if(($hook['once'] ?? true) || $once){
				unset(self::$hooks[$name][$key]);
			}
		}
	}
}