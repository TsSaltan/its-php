<?php
namespace tsframe\controller;

use tsframe\Http;
use tsframe\controller\UserDashboard;
use tsframe\module\Logger;
use tsframe\module\Meta;
use tsframe\module\user\User;
use tsframe\module\user\UserAccess;

/**
 * @route GET  /dashboard/[summary:action]
 */ 
class SummaryDashboard extends UserDashboard {
	public function __construct(){
		$this->setActionPrefix(null);
	}

	public function getSummary(){
		UserAccess::assertCurrentUser('summary');

		switch ($_GET['action'] ?? null) {
			case 'reset-errors':
				$this->resetErrorTs();
				return Http::redirect(Http::makeURI('/dashboard/summary'));
				break;
		}

		$this->vars['title'] = 'Сводка и статистика';

		// Критические ошибки
		$fromTs = $this->getErrorTs();
		$this->vars['summary_critical_total'] = Logger::getCount('*', Logger::LEVEL_CRITICAL, $fromTs);
		
		// Пользователи
		$users = User::get();
		$this->vars['summary_users_total'] = sizeof($users);
		$this->vars['summary_users_today'] = Logger::getCount('user-registration', -1, (time()-24*60*60));
		$this->vars['summary_users_tomonth'] = Logger::getCount('user-registration', -1, (time()-24*60*60*date('j')));
	}

	protected function getErrorTs(): int {
		$meta = new Meta('dashboard');
		return intval($meta->get('summary-error-ts'));
	}

	protected function resetErrorTs(){
		$meta = new Meta('dashboard');
		$meta->set('summary-error-ts', time());
	}
}