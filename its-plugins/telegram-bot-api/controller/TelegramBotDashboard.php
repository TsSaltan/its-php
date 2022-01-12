<?php
namespace tsframe\controller;

use tsframe\Config;
use tsframe\Hook;
use tsframe\Http;
use tsframe\exception\RouteException;
use tsframe\module\Meta;
use tsframe\module\Paginator;
use tsframe\module\TelegramBot;
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
		Input::post()
			  ->name('tgapi-token')->required()
			  ->name('tgapi-uri')->required()
		->assert();

		TelegramBot::setDefaultToken($_POST['tgapi-token']);

		try {
			$bot = TelegramBot::getDefaultBot();
			$bot->setWebhookURI($_POST['tgapi-uri']);
		} catch (\Exception $e){
			return Http::redirect(Http::makeURI('/dashboard/config', ['result' => 'error'], 'telegrambotapi'));
		}

		return Http::redirect(Http::makeURI('/dashboard/config', ['result' => 'ok'], 'telegrambotapi'));
	}
}