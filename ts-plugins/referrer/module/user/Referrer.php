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

	public function __construct(SingleUser $user){
		$this->user = $user;
	}

	/**
	 * Получить пригласившего пользователя
	 * @return SingleUser|null
	 */
	public function getReferrer(){
		$refId = $this->user->getMeta()->get('referrer');
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
		$this->user->getMeta()->set('referrer', $refId);
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

	public function getReferalURI(): string {
		$refUrl = $this->user->getMeta()->get('referrer_url');
		if(is_null($refUrl)){
			$eid = $this->encodeID();
			$refUrl = Http::makeURI('/dashboard/auth?ref=' . $eid);
			Hook::call('referrer.makeURI', [&$refUrl, $this]);
			$this->user->getMeta()->set('referrer_url', $refUrl);
		}

		$shortUrl = $this->user->getMeta()->get('referrer_short_url');
		if(is_null($shortUrl)){
			$bit = new Bitly;
			$shortUrl = $bit->shortUrl($refUrl);
			if(!is_null($shortUrl)){
				$this->user->getMeta()->set('referrer_short_url', $shortUrl);
			}
		}

		return !is_null($shortUrl) ? $shortUrl : $refUrl;
	}

	public function getReferalStatisticURI(){
		$url = $this->user->getMeta()->get('referrer_short_url');
		if(!is_null($url) && strpos($url, 'bit.ly') !== false){
			return $url . '+';
		}

		return null;
	}
}