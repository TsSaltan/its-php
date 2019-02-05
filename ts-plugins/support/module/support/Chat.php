<?php
namespace tsframe\module\support;

use tsframe\Config;
use tsframe\exception\BaseException;
use tsframe\module\database\Database;
use tsframe\module\io\Output;
use tsframe\module\user\SingleUser;
use tsframe\module\user\UserAccess;

class Chat{
	/**
	 * Chat id
	 * @var int
	 */
	protected $id;

	/**
	 * Chat data
	 * @var array
	 */
	protected $data = [];


	public static function getChatCount(): int {
		$query = Database::exec("SELECT COUNT(*) c FROM `support-chats`")->fetch();
		return $query[0]['c'];
	}

	/**
	 * Получить все чаты
	 * @return Chat[]
	 */
	public static function getChats(int $offset = 0, int $count = 0): array {
		$query = Database::exec("SELECT *, UNIX_TIMESTAMP(`date`) date_ts FROM `support-chats` ORDER BY `status` DESC, `date` DESC" . ($count > 0 ? ' LIMIT ' . $count : '') . ($offset > 0 ? ' OFFSET ' . $offset : ''))->fetch();
		$chats = [];
		foreach ($query as $chat) {
			$chats[] = new self($chat['id'], $chat);
		}		
		
		return $chats;
	}

	public static function getUserChatCount(SingleUser $user): int {
		$query = Database::exec("SELECT COUNT(*) c FROM `support-chats` WHERE `owner` = :owner", [
			'owner' => $user->get('id')
		])->fetch();
		return $query[0]['c'];
	}

	public static function getUserChats(SingleUser $user, int $offset = 0, int $count = 0): array {
		$query = Database::exec("SELECT *, UNIX_TIMESTAMP(`date`) date_ts FROM `support-chats` WHERE `owner` = :owner ORDER BY `date` DESC" . ($count > 0 ? ' LIMIT ' . $count : '') . ($offset > 0 ? ' OFFSET ' . $offset : ''), [
			'owner' => $user->get('id')
		])->fetch();

		$chats = [];
		foreach ($query as $chat) {
			$chats[] = new self($chat['id'], $chat);
		}		
		
		return $chats;
	}

	public static function create(SingleUser $owner, string $title): Chat {
		$query = Database::exec("INSERT INTO `support-chats` (`owner`, `title`, `status`) VALUES (:owner, :title, :status)", [
			'owner' => $owner->get('id'),
			'title' => $title,
			'status' => 1
		]);
		$id = $query->lastInsertId();
		return new self($id);
	}

	public function __construct(int $id, ?array $data = []){
		$this->id = $id;
		$this->data = (is_array($data) && sizeof($data) > 0) ? $data : (Database::exec('SELECT *, UNIX_TIMESTAMP(`date`) date_ts FROM `support-chats` WHERE `id` = :id', ['id' => $this->id])->fetch()[0] ?? []);

		if(sizeof($this->data) == 0){
			throw new BaseException('Invalid chat_id: ' . $this->id);
		}
	}

	public function getTitle(): string {
		$title = $this->data['title'] ?? 'Диалог #' . $this->id;
		return Output::of($title)->specialChars()->getData();
	}
	
	public function getId(): int {
		return $this->id;
	}

	/**
	 * 0 - чат закрыт
	 * 1 - чат активен
	 * @return int
	 */
	public function getStatus(): int {
	    return $this->data['status'] ?? 0;
	}

	public function setStatus(int $status){
		Database::exec('UPDATE `support-chats` SET `status` = :status WHERE `id` = :id', [
			'status' => $status,
			'id' => $this->id
		]);
		$this->data['status'] = $status;
	}

	/**
	 * Здесь дата - время последнего прочтения
	 * На этой метке будем основываться на том, есть ли новые сообщения
	 * @return int
	 */
	public function getDate(): int {
	    return $this->data['date_ts'] ?? 0;
	}

	public function setCurrentDate(){
		Database::exec('UPDATE `support-chats` SET `date` = CURRENT_TIMESTAMP() WHERE `id` = :id', [
			'id' => $this->id
		]);
		$this->data['date_ts'] = time();
	}

	public function hasNewMessages(int $fromId = -1): bool {
		if($fromId > 0){
			$last = $this->getLastMessage()->getId();
			return $last > $fromId;
		} else {
			$last = $this->getLastMessage()->getDate();
			$date = $this->getDate();
			return $last > $date;
		}
	}

	/**
	 * @return int
	 */
	public function getOwnerId(): int {
	    return $this->data['owner'] ?? 0;
	}

	/**
	 * @return SingleUser
	 */
	public function getOwner(): SingleUser {
	    return new SingleUser($this->getOwnerId());
	}

	public function getLastMessage(): Message {
		$query = Database::exec('SELECT *, UNIX_TIMESTAMP(`date`) date_ts FROM `support-messages` WHERE `chat` = :chat ORDER BY `date` DESC LIMIT 1', [
			'chat' => $this->id
		])->fetch();
		return new Message($query[0]['id'], $query[0]);
	}

	public function getNewMessages(int $fromMessageId = -1): array {
		if($fromMessageId > 0){
			$query = Database::exec('SELECT *, UNIX_TIMESTAMP(`date`) date_ts FROM `support-messages` WHERE `chat` = :chat AND `id` > :id ORDER BY `date` ASC', [
				'chat' => $this->id,
				'id' => $fromMessageId,
			])->fetch();
		} else {
			$query = Database::exec('SELECT *, UNIX_TIMESTAMP(`date`) date_ts FROM `support-messages` WHERE `chat` = :chat AND UNIX_TIMESTAMP(`date`) > :date ORDER BY `date` ASC', [
				'chat' => $this->id,
				'date' => $this->getDate(),
			])->fetch();
		}
		$messages = [];
		foreach ($query as $m) {
			$messages[] = new Message($m['id'], $m);
		}

		return $messages;
	}

	public function getMessages(int $offset = 0, int $count = 0): array {
		$query = Database::exec('SELECT *, UNIX_TIMESTAMP(`date`) date_ts FROM `support-messages` WHERE `chat` = :chat ORDER BY `date` ASC'  . ($count > 0 ? ' LIMIT ' . $count : '') . ($offset > 0 ? ' OFFSET ' . $offset : ''), [
			'chat' => $this->id
		])->fetch();
		$messages = [];
		foreach ($query as $m) {
			$messages[] = new Message($m['id'], $m);
		}

		return $messages;
	}

	public function addMessage(string $text, ?SingleUser $owner = null): Message {
		return Message::create(
			(is_null($owner) ? $this->getOwner() : $owner),
			$this->id,
			$text
		);
	}

	public function isAnswered(): bool {
		$lastMes = $this->getLastMessage()->getOwner();
		return UserAccess::checkUser($lastMes, 'support.operator');
	}

	public function close(){
		$this->setStatus(0);
	}

	public function delete(){
		Database::exec('DELETE FROM `support-chats` WHERE `id` = :id', ['id' => $this->id]);
		Database::exec('DELETE FROM `support-messages` WHERE `chat` = :id', ['id' => $this->id]);
	}
}