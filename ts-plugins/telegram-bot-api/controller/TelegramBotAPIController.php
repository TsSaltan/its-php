<?php
namespace tsframe\controller;

use tsframe\Config;
use tsframe\Hook;
use tsframe\Http;
use tsframe\Plugins;
use tsframe\module\Logger;
use tsframe\module\scheduler\Scheduler;

/**
 * @route GET|POST /telegram-bot-api/
 * @route GET|POST /telegram-bot-api
 */
class TelegramBotAPIController extends AbstractController {
	public function response(){
		try {
			$bot = TelegramBot::getDefaultBot();
			$request = $bot->getRequest();
			$client = $bot->getClient();
			Hook::call('telegram-bot.query', [$request, $client]);
		} catch (\Exception $e){

		}

		$this->responseCode = Http::CODE_OK;
		$this->responseBody = "OK";
	}
}