<?php
namespace tsframe\module\support;

use tsframe\Config;
use tsframe\exception\BaseException;
use tsframe\module\database\Database;
use tsframe\module\io\Output;
use tsframe\module\user\SingleUser;
use tsframe\module\user\UserAccess;

class Message{
	public static function getUnreadCountForUser(SingleUser $owner): int {
		$q = Database::exec('SELECT COUNT(*) c FROM `support-chats` chat
			RIGHT JOIN `support-messages` message ON (
    			message.chat = chat.id
			)
			WHERE chat.`owner` = :owner AND message.date > chat.date', ['owner' => $owner->get('id')])->fetch();

		return $q[0]['c'] ?? 0;
	}

	public static function getUnreadCountForOperator(): int {
		$q = Database::exec('SELECT COUNT(chat.id) c FROM `support-chats` as chat  
			LEFT JOIN `support-messages` as message ON (
			    message.id = (SELECT MAX(`id`) FROM `support-messages` WHERE `support-messages`.`chat` = chat.id)
			)
			LEFT JOIN `users` as users ON (
			    users.id = message.owner
			)
			WHERE chat.status > 0 AND users.access < :access', ['access' => UserAccess::getAccess('support.operator')]
		)->fetch();

		return $q[0]['c'] ?? 0;
	}

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
		$message = Output::of($this->data['message'])->specialChars()->quotes()->getData();
		$message = preg_replace_callback('#(https?\:\/\/[^\s\n\r]+?)#Ui', function(array $matches){
			$text = (strlen($matches[1]) > 60) ? substr($matches[1], 0, 57) . '...' : $matches[1];
			return "<a href='".$matches[1]."' target='_blank'>".$text."</a>";
		}, $message);
		return $message;
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