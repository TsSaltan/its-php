<?php
namespace tsframe\module\io;

/**
 * Базовый фильтр для данных
 */
abstract class Filter{
	/**
	 * Фильтры
	 * callable($currentValue, $currentKey)
	 * @var array [filterName => callable, ... ]
	 */
	protected static $filters = [];

	/**
	 * Добавить новый фильтр
	 * @param array|string   $name     [description]
	 * @param callable $callback [description]
	 */
	public static function addFilter($name, callable $callback){
		$names = is_array($name) ? $name : [$name];
		foreach ($names as $name) {
			self::$filters[strtolower($name)] = $callback;
		}
	}

	protected function callFilter(string $filterName, array $filterArgs = []){
		$filterName = strtolower($filterName);

		if(isset(self::$filters[$filterName])){
			return call_user_func_array(self::$filters[$filterName], $filterArgs);
		}

		return null;
	}
}