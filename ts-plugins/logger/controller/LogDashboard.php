<?php
namespace tsframe\controller;

use tsframe\module\user\User;
use tsframe\module\user\UserAccess;
use tsframe\module\Log;
use tsframe\module\Paginator;

/**
 * @route GET /dashboard/[logs:action]
 * @route GET /dashboard/[logs:action]/[:type]
 */ 
class LogDashboard extends UserDashboard {

	protected $actionPrefix = '';

	public function getLogs(){
		UserAccess::assertCurrentUser('log');

		$type = $this->params['type'] ?? 'default';

		$this->vars['title'] = 'Системные логи';
		$this->vars['logTypes'] = Log::getTypes();
		$this->vars['logType'] = $type;
		$this->vars['logs'] = new Paginator(Log::getLogs($type));
	}

}