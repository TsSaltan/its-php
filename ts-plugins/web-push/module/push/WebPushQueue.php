<?php 	
namespace tsframe\module\push;

use tsframe\exception\BaseException;
use tsframe\module\Logger;
use tsframe\module\database\Database;
use tsframe\module\io\Output;
use tsframe\module\push\WebPushAPI;
use tsframe\module\push\WebPushClient;

/**
 * Очередь пушей
 */
class WebPushQueue {
	/**
	 * Количество запросов, разбираемых из очереди за один сеанс
	 */
	const LIMIT = 50;

	/**
	 * Добавить пуш в очередь
	 * @param array  $clients Массив с id клиентов
	 * @param string $title  
	 * @param string $body   
	 * @param string $link   
	 * @param string $icon   
	 * @return WebPushQueue
	 */
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

	/**
	 * Получить список очереди
	 * @return WebPushQueue[]
	 */
	public static function getList(): array {
		$query = Database::exec('SELECT * FROM `web-push-queue`')->fetch();
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

	public function getId(): int {
		return $this->id;
	}

	public function getClients(): array {
		return $this->clients;
	}

	public function getTitle(): string {
		return Output::of($this->title)->xss()->getData();
	}

	public function getLink(): string {
		return $this->link;
		return Output::of($this->link)->xss()->quotes()->getData();
	}

	/**
	 * Отправить пуш, удалить id клиентов из базы
	 */
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
			try {
				$client = WebPushClient::byId($id);
				$webPush->addPushMessage($client, $payload);
			} catch (BaseException $e){
				
			}

			unset($this->clients[$k]);
			if(++$i >= self::LIMIT)	break;
		}

		$result = $webPush->send();
		foreach ($result as $item) {
			Logger::webpush()->debug('WebPush sended', $item);
		}

		if(sizeof($this->clients) == 0){
			// Удаляем из очереди, если не осталось id
			Database::exec('DELETE FROM `web-push-queue` WHERE `id` = :id', ['id' => $this->id]);
		} else {
			Database::exec('UPDATE `web-push-queue` SET `clients` = :clients WHERE `id` = :id', ['id' => $this->id, 'clients' => json_encode($this->clients)]);
		}
	}
}