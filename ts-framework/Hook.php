<?php
namespace tsframe;

class Hook{
	protected static $hooks = [];

	/**
	 * Добавить фукнцию на каждый вызов хука
	 * @param  string   $name     Имя хука
	 * @param  callable $function Функция
	 * @param  int 		$priority Приоритет: чем меньше значение, тем раньше будет вызван коллбэк
	 */
	public static function register(string $name, callable $function, int $priority = 10){
		self::$hooks[$name][] = ['function' => $function, 'once' => false, 'priority' => $priority];
	}

	/**
	 * Добавить функцию на однократный вызов хука
	 * см. Hook::register
	 */
	public static function registerOnce(string $name, callable $function, int $priority = 10){
		self::$hooks[$name][] = ['function' => $function, 'once' => true, 'priority' => $priority];
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
		$hooks = self::$hooks[$name];
		usort($hooks, function($a, $b){
			if ($a['priority'] == $b['priority']) {
		        return 0;
		    }
		    return ($a['priority'] < $b['priority']) ? -1 : 1;
		});

		foreach (self::$hooks[$name] as $key => $hook) {
			$func = $hook['function'];
			try{
				$result = call_user_func_array($func, $params);

				if(is_callable($return)){
					call_user_func($return, $result);
				}
			} catch(\Exception|\Error $e){
				if(is_callable($error)){
					call_user_func($error, $e);
				} else {
					throw $e;
				}
			}

			if(($hook['once'] ?? true) || $once){
				unset(self::$hooks[$name][$key]);
			}
		}
	}
}