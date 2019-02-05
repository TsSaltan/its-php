<?php
namespace tsframe\module\support;

use tsframe\Config;
use tsframe\exception\BaseException;
use tsframe\module\database\Database;
use tsframe\module\io\Output;
use tsframe\module\user\SingleUser;

class Message{
	public static function create(SingleUser $owner, int $chat, string $message): Message {
		$query = Database::exec("INSERT INTO `support-messages` (`owner`, `chat`, `message`) VALUES (:owner, :chat, :message)", [
			'owner' => $owner->get('id'),
			'chat' => $chat,
			'message' => $message
		]);
		$id = $query->lastInsertId();
		return new self($id);
	}

	public function __construct(int $id, ?array $data = []){
		$this->id = $id;
		$this->data = (is_array($data) && sizeof($data) > 0) ? $data : (Database::exec('SELECT *, UNIX_TIMESTAMP(`date`) date_ts FROM `support-messages` WHERE `id` = :id', ['id' => $this->id])->fetch()[0] ?? []);

		if(sizeof($this->data) == 0){
			throw new BaseException('Invalid chat_id: ' . $this->id);
		}
	}

	public function getMessage(): ?string {
		return Output::of($this->data['message'])->specialChars()->getData();
	}
	
	public function getId(): int {
		return $this->id;
	}

	/**
	 * @return int
	 */
	public function getDate(): int {
	    return $this->data['date_ts'] ?? 0;
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
}