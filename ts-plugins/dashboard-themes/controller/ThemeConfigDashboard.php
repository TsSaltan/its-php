<?php
namespace tsframe\controller;

use tsframe\Http;
use tsframe\module\Meta;
use tsframe\module\user\UserAccess;
use tsframe\module\io\Input;

/**
 * @route POST /dashboard/config/[theme:action]
 */ 
class ThemeConfigDashboard extends UserDashboard {
	protected $actionPrefix = '';

	public function postTheme(){
		UserAccess::assertCurrentUser('user.editConfig');
		$data = Input::post()
					->name('theme')->required()->minLength(0)
					->name('sitename')->required()->minLength(0)
					->name('siteicon')->required()->minLength(0)
					->name('sitehome')->required()->minLength(0)
					->assert();

		$meta = new Meta('dashboard');
		$meta->set('theme', $data['theme']);
		$meta->set('sitename', $data['sitename']);
		$meta->set('siteicon', $data['siteicon']);
		$meta->set('sitehome', $data['sitehome']);
		Http::redirect(Http::makeURI('/dashboard/config#theme'));
	}
}