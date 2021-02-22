<?php
namespace tsframe\controller;

use tsframe\Config;
use tsframe\Hook;
use tsframe\Http;
use tsframe\exception\RouteException;
use tsframe\module\Meta;
use tsframe\module\Paginator;
use tsframe\module\io\Input;
use tsframe\module\user\SocialLogin;
use tsframe\module\user\User;
use tsframe\module\user\UserAccess;
use tsframe\module\user\UserConfig;

/**
 * @route POST /dashboard/config/telegram-bot-api
 */ 
class TelegramBotDashboard extends Dashboard {

	public function response(){
		UserAccess::checkCurrentUser('user.editConfig');
		/*Input::post()
			  ->name('registerEnabled')->required()
			 ->assert();*/

		var_dump('tg-config');die;

		return Http::redirect(Http::makeURI('/dashboard/config', [], 'telegrambotapi'));
	}
}