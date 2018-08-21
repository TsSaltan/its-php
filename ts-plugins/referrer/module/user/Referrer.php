<?php
namespace tsframe\module\user;

use tsframe\module\Meta;
use tsframe\module\Bitly;
use tsframe\Config;
use tsframe\Http;
use tsframe\Hook;
use tsframe\Cache;


class Referrer{
	/**
	 * Текущий пользователь
	 * @var SingleUser
	 */
	protected $user;

	/**
	 * @var Meta
	 */
	protected $userMeta;

	public function __construct(SingleUser $user){
		$this->user = $user;
		$this->userMeta = $user->getMeta();
	}

	/**
	 * Получить пригласившего пользователя
	 * @return SingleUser|null
	 */
	public function getReferrer(){
		$refId = $this->userMeta->get('referrer');
		if(!is_null($refId)){
			$users = User::get(['id' => $refId]);
			foreach ($users as $user) {
				return $user;
			}
		}

		return null;
	}

	/**
	 * Получить приглашенных пользователей
	 * @return SingleUser[]
	 */
	public function getReferrals(): array {
		$refs = [];
		$metas = Meta::find('referrer', $this->user->get('id'));
		foreach ($metas as $meta) {
			$find = explode('_', $meta->getParent())[1];
			$users = User::get(['id' => $find]);
			foreach ($users as $user) {
				$refs[$user->get('id')] = $user;
				break;
			}
		}

		return $refs;
	}

	/**
	 * Установить реферрера текущему пользователю
	 * @param int|SingleUser $referrer
	 */
	public function setReferrer($referrer){
		$refId = ($referrer instanceof SingleUser) ? $referrer->get('id') : $referrer;
		$this->userMeta->set('referrer', $refId);
	}

	/**
	 * Преобразует текущий ID в набор буквенных символов
	 * Необходимо для генерации реферральной ссылки
	 * @return string
	 */
	public function encodeID(): string {
		$id = $this->user->get('id');
		$base = base64_encode($id);
		return str_replace('=', null, $base);
	}

	/**
	 * Расшифровывает зашифрованный ID
	 * @param  string $encoded
	 * @return int ID
	 */
	public function decodeID(string $encoded): int {
		$fromBase = base64_decode($encoded);
		return intval($fromBase);
	}

	public function getRefURI(): string {
		$origUrl = 'http://' . $_SERVER['HTTP_HOST'] . Http::makeURI('/dashboard/login?ref=' . $this->encodeID());

		$url = $this->userMeta->get('referrer_url');
		if(is_null($url)){
			$bit = new Bitly;
			$url = $bit->shortUrl($origUrl);
			$this->userMeta->set('referrer_url', $url);
		}

		return $url;
	}

	public function getRefStatURI(): string {
		$url = $this->userMeta->get('referrer_url');
		if(!is_null($url) && strpos($url, 'bit.ly') !== false){
			return $url . '+';
		}

		return null;
	}
}