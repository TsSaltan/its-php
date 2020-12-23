<?php
namespace tsframe\controller;

use tsframe\Http;
use tsframe\module\user\User;
use tsframe\module\user\UserAccess;
use tsframe\module\Logger;
use tsframe\module\io\Output;
use tsframe\module\io\Input;
use tsframe\module\Paginator;

/**
 * @route GET  /dashboard/[logs:action]
 * @route POST /dashboard/[logs-clear:action]/
 * @route POST /dashboard/[logs-clear:action]
 */ 
class LogDashboard extends UserDashboard {
	public function __construct(){
		$this->setActionPrefix(null);
	}

	public function getLogs(){
		UserAccess::assertCurrentUser('log');

		$section = $_GET['section'] ?? '*';
		$minLevel = $_GET['level'] ?? -1;

		$this->vars['title'] = 'Система логирования';
		$this->vars['logSection'] = $section;
		$this->vars['logMinLevel'] = $minLevel;
		$this->vars['logLevels'] = Logger::getLevels();
		$this->vars['logSections'] = Logger::getSections();
		$this->vars['logTotalSize'] = Logger::getSize();
		$this->vars['logTotalCount'] = Logger::getCount();
		
		$logsCount = Logger::getCount($section, $minLevel);

		$pages = new Paginator([], 10);
		$pages->setDataSize($logsCount);

		$pages->setTotalDataCallback(function($offset, $limit) use ($section, $minLevel){
			$logs = Logger::getList($section, $minLevel, $offset, $limit);
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