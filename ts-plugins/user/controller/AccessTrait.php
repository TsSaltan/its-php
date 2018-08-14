<?php
namespace tsframe\controller;

use tsframe\module\user\User;
use tsframe\module\user\UserAccess;

trait AccessTrait{
	protected function access($required){
		UserAccess::assert(User::current(), $required);
	}
}