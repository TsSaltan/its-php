<?php
namespace tsframe\controller;

use tsframe\Http;
use tsframe\module\Meta;
use tsframe\module\user\User;
use tsframe\module\user\UserAccess;
use tsframe\view\HtmlTemplate;
use tsframe\module\testing\TestEngine;

trait ActionToMethodTrait{
	function callActionMethod(bool $repeat = false){
		$request = $this->getRequestMethod();
		$action = (method_exists($this, 'getAction')) ? $this->getAction() : $this->params['action'];
		//$action = $this->params['action'];

		$methodName = strtolower($request) . str_replace(['-','_'], '', $action);
		if(method_exists($this, $methodName)){
			return call_user_func([$this, $methodName]);
		}

		return false;
	}
}