<?php
namespace tsframe\controller;

use tsframe\Http;
use tsframe\exception\ControllerException;
use tsframe\module\Meta;
use tsframe\module\testing\TestEngine;
use tsframe\module\user\User;
use tsframe\module\user\UserAccess;
use tsframe\view\HtmlTemplate;

/**
 * Автоматический вызов метода, если был передан параметр action
 * f.e. Контроллер:
 * @ route GET /mypath/[:action]
 * Запрос HTTP: GET /mypath/test
 * Будет выызван метод getTest
 * (или defTest - универсальный для всех методов GET|POST ...)
 */
trait ActionToMethodTrait{
	function callActionMethod(){
		$methodName = $this->getActionMethod();
		return call_user_func([$this, $methodName]);
	}

	function getActionMethod(): ?string {
		$request = Http::getRequestMethod();
		$action = (method_exists($this, 'getAction')) ? $this->getAction() : $this->params['action'];
		$method = ucfirst(preg_replace('#([^\w\d]|[_])#Ui', '', $action));
		
		$methodName1 = strtolower($request) . $method;
		if(method_exists($this, $methodName1)) return $methodName1;

		$methodName2 = 'def' . $method;
		if(method_exists($this, $methodName2)) return $methodName2;

		throw new ControllerException('Method for action ' . $action . ' not found', 404, ['controller' => get_class($this), 'findMethods' => [
			$methodName1, $methodName2
		]]);
	}
}