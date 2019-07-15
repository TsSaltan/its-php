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
		if(!is_null($methodName)){
			return call_user_func([$this, $methodName]);
		}

		$action = (method_exists($this, 'getAction')) ? $this->getAction() : $this->params['action'];
		throw new ControllerException('Method for action' . $action . ' not found', 404, ['controller' => get_class($this)]);
	}

	function getActionMethod(): ?string {
		$request = Http::getRequestMethod();
		$action = (method_exists($this, 'getAction')) ? $this->getAction() : $this->params['action'];
		$method = ucfirst(str_replace(['-','_'], '', $action));
		
		$methodName = strtolower($request) . $method;
		if(method_exists($this, $methodName)) return $methodName;

		$methodName = 'def' . $method;
		if(method_exists($this, $methodName)) return $methodName;

		return null;
	}
}