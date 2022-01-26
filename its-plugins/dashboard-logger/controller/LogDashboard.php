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
 * @route POST /dashboard/[logs-delete:action]/
 * @route POST /dashboard/[logs-delete:action]
 */ 
class LogDashboard extends UserDashboard {
	public function __construct(){
		$this->setActionPrefix(null);
	}

	public function getLogs(){
		UserAccess::assertCurrentUser('log');

		$section = $_GET['section'] ?? '*';
		$minLevel = $_GET['level'] ?? -1;


		$tsFrom = isset($_GET['ts_from_date']) 	? \DateTime::createFromFormat('Y-m-d H:i', $_GET['ts_from_date'] . ' ' . ($_GET['ts_from_time'] ?? '00:00'))->format('U') : -1;
		$tsTo 	= isset($_GET['ts_to_date']) 	? \DateTime::createFromFormat('Y-m-d H:i', $_GET['ts_to_date'] . ' ' . ($_GET['ts_to_time'] ?? '00:00'))->format('U') : -1;

		$this->vars['title'] = 'Система логирования';
		$this->vars['logSection'] = $section;
		$this->vars['logMinLevel'] = $minLevel;
		$this->vars['logLevels'] = Logger::getLevels();
		$this->vars['logTotalSize'] = Logger::getSize();
		$this->vars['logTotalCount'] = Logger::getCount();
		$this->vars['logTsFrom'] = $tsFrom;
		$this->vars['logTsTo'] = ($tsTo > 0) ? $tsTo : time() + 24*60*60;
		
		// Sections and removable sections
		$sections = Logger::getSections();
		$this->vars['logSections'] = $sections;
		foreach ($sections as $k => $s) {
			if(Logger::isUnremovableSection($s)){
				unset($sections[$k]);
			}
		}
		$this->vars['logRemovableSections'] = $sections;

		// Count and pages
		$logsCount = Logger::getCount($section, $minLevel, $tsFrom, $tsTo);
		$pages = new Paginator([], 10);
		$pages->setDataSize($logsCount);

		$pages->setTotalDataCallback(function($offset, $limit) use ($section, $minLevel, $tsFrom, $tsTo){
			$logs = Logger::getList($section, $minLevel, $tsFrom, $tsTo, $offset, $limit);
			return Output::of($logs)->specialChars()->getData();
		});

		$this->vars['logs'] = $pages;
	}

	public function postLogsDelete(){
		$input = Input::post()
			 ->name('section')->required()
			 ->name('level')->required()
			 ->name('date')->required()
		->assert();

		$date = max(0, getdate(strtotime($input['date']))[0]);
		Logger::delete($input['section'], $input['level'], $date);
		
		return Http::redirect(Http::makeURI('/dashboard/logs', ['level' => $input['level'], 'section' => $input['section']]));
	}

}