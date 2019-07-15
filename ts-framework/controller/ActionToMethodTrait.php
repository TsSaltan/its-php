<?php
namespace tsframe\controller;

use tsframe\Http;
use tsframe\exception\ControllerException;
use tsframe\module\Meta;
use tsframe\module\testing\TestEngine;
use tsframe\module\user\User;
use tsframe\module\user\UserAccess;
use tsframe\view\HtmlTemplate;

trait ActionToMethodTrait{
	function callActionMethod(){
		$methodName = $this->getActionMethod();
		if(method_exists($this, $methodName)){
			return call_user_func([$this, $methodName]);
		}

		throw new ControllerException('Method ' . $methodName . ' not found', 404, ['controller' => get_class($this)]);
	}

	function getActionMethod(): string {
		$request = $this->getRequestMethod();
		$action = (method_exists($this, 'getAction')) ? $this->getAction() : $this->params['action'];
		return strtolower($request) . ucfirst(str_replace(['-','_'], '', $action));
	}
}