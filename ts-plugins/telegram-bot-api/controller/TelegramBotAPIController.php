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
		$this->responseCode = Http::CODE_OK;
		$this->responseBody = "Meow";
	}
}