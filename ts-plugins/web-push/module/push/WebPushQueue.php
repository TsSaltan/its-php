<?php 	
namespace tsframe\module\push;

use tsframe\module\Log;
use tsframe\module\database\Database;
use tsframe\module\push\WebPushAPI;
use tsframe\module\push\WebPushClient;

class WebPushQueue {
	/**
	 * Количество запросов, разбираемых из очереди за один сеанс
	 */
	const LIMIT = 50;

	public static function add(array $clients, string $title, string $body, string $link, string $icon): WebPushQueue {
		$q = Database::exec('INSERT INTO `web-push-queue` (`clients`, `title`, `body`, `link`, `icon`) VALUES (:clients, :title, :body, :link, :icon)', [
			'clients' => json_encode($clients), 
			'title' => $title, 
			'body' => $body, 
			'link' => $link, 
			'icon' => $icon
		]);

		return new self($q->lastInsertId(), $clients, $title, $body, $link, $icon);
	}

	public static function getList(): array {
		$query = Database::exec('SELECT * FROM `web-push-queue`');
		$items = [];
		foreach($query as $q){
			$items[] = new self($q['id'], json_decode($q['clients'], true), $q['title'], $q['body'], $q['link'], $q['icon']);
		}

		return $items;
	}

	private $id = -1;
	private $clients = [];
	private $title;
	private $body;
	private $link;
	private $icon;

	public function __construct(int $id, array $clients, string $title, string $body, string $link, string $icon){
		$this->id = $id;
		$this->clients = $clients;
		$this->title = $title;
		$this->body = $body;
		$this->link = $link;
		$this->icon = $icon;
	}

	public function send(){
		$i = 0;
		$webPush = new WebPushAPI;
		$payload = [
			'body' => $this->body, 
			'title' => $this->title, 
			'icon' => $this->icon, 
			'link' => $this->link
		];

		foreach($this->clients as $k=>$id){
			$client = WebPushClient::byId($id);
			$webPush->addPushMessage($client, $payload);

			unset($this->clients[$k]);
			if(++$i >= self::LIMIT)	break;
		}

		$result = $webPush->send();
		foreach ($result as $item) {
			Log::WebPush('WebPush sended', $item);
		}

		if(sizeof($this->clients) == 0){
			Database::exec('DELETE FROM `web-push-queue` WHERE `id` = :id', ['id' => $this->id]);
		} else {
			Database::exec('UPDATE `web-push-queue` SET `clients` = :clients WHERE `id` = :id', ['id' => $this->id, 'clients' => json_encode($this->clients)]);
		}
	}
}