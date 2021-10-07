<?php
namespace tsframe\controller;

use tsframe\Config;
use tsframe\Hook;
use tsframe\Http;
use tsframe\Plugins;
use tsframe\exception\BaseException;
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
			(new Logger('telegram-bot'))->debug('Incoming request', $request);
			Hook::call('telegram-bot.query', [$request, $client]);
		} catch (BaseException $e){
			(new Logger('telegram-bot'))->error('Catch exception', [
				'exception_class' => get_class($e),
				'message' => $e->getMessage(),
				'debug' => $e->getDebug(),
				'code' => $e->getCode()
			]);
		} catch (\Exception $e){
			(new Logger('telegram-bot'))->error('Catch exception', [
				'exception_class' => get_class($e),
				'message' => $e->getMessage(),
				'code' => $e->getCode()
			]);
		}

		$this->responseCode = Http::CODE_OK;
		$this->responseBody = "OK";
	}
}