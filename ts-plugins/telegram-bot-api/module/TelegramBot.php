<?php
namespace tsframe\module;

use TelegramBot\Api\Client;
use tsframe\Config;
use tsframe\exception\BaseException;

class TelegramBot {
	public static function getDefaultToken(): ?string {
		return Config::get('telegram-bot.token');
	}

	public static function setDefaultToken(string $token) {
		return Config::set('telegram-bot.token', $token);
	}

	public static function getDefaultBot(): TelegramBot {
		$token = self::getDefaultToken();
		if(strlen($token) == 0) throw new BaseException('Empty telegram bot API token from configs');

		return new self($token);
	}

	/**
	 * @var string
	 */
	private $token;

	/**
	 * @var client
	 */
	private $client;
	public function __construct(string $token){
		$this->token = $token;
		$this->client = new Client($token);
	}

	public function getClient(): Client {
		return $this->client;
	}

	public function getRequest(): ?array {
		$raw = $this->client->getRawBody();
		return json_decode($raw, true);
	}

	public function setWebhookURI(string $uri){
		$this->client->setWebhook($uri);
	}
}