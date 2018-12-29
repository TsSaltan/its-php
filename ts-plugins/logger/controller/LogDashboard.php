<?php
namespace tsframe\controller;

use tsframe\Http;
use tsframe\module\user\User;
use tsframe\module\user\UserAccess;
use tsframe\module\Log;
use tsframe\module\io\Output;
use tsframe\module\io\Input;
use tsframe\module\Paginator;

/**
 * @route GET  /dashboard/[logs:action]
 * @route GET  /dashboard/[logs:action]/[*:type]
 * @route POST /dashboard/[logs-clear:action]/
 * @route POST /dashboard/[logs-clear:action]
 */ 
class LogDashboard extends UserDashboard {

	protected $actionPrefix = '';

	public function getLogs(){
		UserAccess::assertCurrentUser('log');

		$type = $this->params['type'] ?? 'default';

		$this->vars['title'] = 'Системные логи';
		$this->vars['logTypes'] = Log::getTypes();
		$this->vars['logType'] = $type;

		$pages = new Paginator([], 10);
		$pages->setDataSize(Log::getLogsCount($type));
		$pages->setTotalDataCallback(function($offset, $limit) use ($type){
			$logs = Log::getLogs($type, $offset, $limit);
			return Output::of($logs)->specialChars()->getData();
		});

		$this->vars['logs'] = $pages;
	}

	public function postLogsClear(){
		Input::post()
			 ->name('group')->required()
			 ->name('date')->required()
		->assert();

		$date = max(0, getdate(strtotime($_POST['date']))[0]);
		Log::clear($_POST['group'], $date);
		
		return Http::redirect(Http::makeURI('/dashboard/logs' . ($_POST['group'] != '*' ? '/' . urlencode($_POST['group']) : '')));
	}

}