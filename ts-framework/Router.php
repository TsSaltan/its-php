<?php
namespace tsframe;

use AltoRouter;
use tsframe\exception\RouteException;
use tsframe\controller\AbstractController;
use tsframe\Http;
use tsframe\utils\Reflect;

/**
 * Роутер парсит список контроллеров, извлекает из phpDoc параметра @route URL-маску
 * если маска совпала, вызывается этот контроллер
 *
 * Формат для @route
 * Перенаправление: @route METHOD PATH_MASK -> REDIRECT_PATH
 * Регистрация пути: @route METHOD PATH_MASK
 * 
 * @link https://github.com/dannyvankooten/AltoRouter
 * 
 */
class Router{

	/**
	 * @var AltoRouter
	 */
	protected static $router;

	/**
	 * Поиск подходящих контроллеров
	 * @return object Возвращает объект, наследник AbstractController
	 * @throws RouteException
	 */
	public static function findController() : AbstractController {
		$routerBase = substr(App::getBasePath(), 1);
		self::$router = new AltoRouter([], $routerBase);
		
		$routers = Reflect::getClasses(__NAMESPACE__ . '\\controller');

		//var_dump($routers);die;

		foreach ($routers as $classPath){
			$routes = self::getRouteDoc($classPath);

			if(sizeof($routes->redirects) > 0){
				foreach ($routes->redirects as $route) {
					self::$router->map($route[0], $route[1], function() use ($route){
						Http::redirect(Http::makeURI($route[2]));
					});
				}
			}

			if(sizeof($routes->routes) > 0){
				foreach ($routes->routes as $route) {
					self::$router->map($route[0], $route[1], $classPath);
				}
			}

			if($match = self::$router->match()){			
				return self::callController($match);
			}

		}

		throw new RouteException('Router does not found', 404);
	}

	/**
	 * Инициализирует контроллер
	 * @param  array  $controller Массив данных из AltoRouter->match;
	 * @return object instanceof AbstractController
	 * @throws RouteException
	 */
	protected static function callController(array $controller) : AbstractController {
		if(is_callable($controller['target'])){
			call_user_func($controller['target']);
		} else {
			$class = new $controller['target'];

			if(! $class instanceof AbstractController){
				throw new RouteException('Controller ' . get_class($class) . ' does not instanceof AbstractController');
			}

			$class->setParams($controller['params']);

			return $class;
		}
	}

	/**
	 * Получает пути из phpDoc комментариев
	 * @return object
	 */
	protected static function getRouteDoc($class){
		$docs = Reflect::getDoc($class, 'route');
		$routes = [];
		$redirects = [];
		
		foreach ($docs as $doc) {
			if(preg_match_all('#([A-Z\|]+?)\s+([^\n\s]+?)#Ui', $doc, $matches)){
				foreach ($matches[0] as $key => $value) {
					$routes[] = [ $matches[1][$key], $matches[2][$key] ];
				}
			}		

			if(preg_match_all('#([A-Z\|]+?)\s+([^\s]+?)\s*-\>\s*([^\n\s]+?)#Ui', $doc, $matches)){
				foreach ($matches[0] as $key => $value) {
					$redirects[] = [ $matches[1][$key], $matches[2][$key], $matches[3][$key] ];
				}
			}
		}

		return (object) [
			'redirects' => $redirects,
			'routes' => $routes
		];
	}
}