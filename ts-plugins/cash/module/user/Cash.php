<?php
namespace tsframe\module\user;

use tsframe\module\database\Database;
use tsframe\module\database\Query;
use tsframe\module\Crypto;
use tsframe\module\Log;
use tsframe\Config;
use tsframe\Hook;

/**
 * Используется тип string, т.к. нужны функции повышенной точности bc
 */
class Cash{
	/**
	 * Точность: количество знаков после запятой
	 */
	const ACCURACY = 4;

	/**
	 * @var SingleUser
	 */
	protected $user;

	/**
	 * @var string
	 */
	protected $balance = '0';

	public static function getGlobalHistory(int $offset = 0, int $count = 0){
		return Log::getLogs('Cash', $offset, $count);
	}

	public static function getCurrency(){
		$cur = Config::get('interkassa.currency');
		return is_null($cur) ? 'USD' : $cur;
	}

	public static function currentUser(){
		return new self(User::current());
	}

	public static function ofUserId(int $userId){
		$user = new SingleUser($userId);
		return new self($user);
	}

	public function __construct(SingleUser $user){
		$this->user = $user;
		$this->getBalance(true);
	}

	/**
	 * @return string
	 */
	public function getBalance(bool $update = false): string {
		if(!$this->user->isAuthorized()) return '-0';
		
		if($this->balance === '0' || $update){
			$data = Database::prepare('SELECT * FROM `cash` WHERE `owner` = :userId')
					->bind(':userId', $this->user->get('id'))
					->exec()
					->fetch();
			if(isset($data[0])){
				$this->balance = strval($data[0]['balance']);
			} else {
				Database::prepare('INSERT INTO `cash` (`owner`, `balance`) VALUES (:userId, 0)')
						->bind(':userId', $this->user->get('id'))
						->exec();

				$this->balance = '0';
			}
		}

		return $this->balance;
	}

	public function isTransactionExists(string $trId): bool {
		$logs = Database::prepare('SELECT * FROM `log` WHERE `type` = :type AND `data` LIKE :trId')
					->bind(':type', 'Cash')
					->bind(':trId', '%' . $trId . '%')
					->exec()
					->fetch();

		foreach($logs as $log){
			$data = json_decode($log['data'], true);
			if(isset($data['pay_id']) && $data['pay_id'] == $trId && isset($data['user']) && $data['user'] == $this->user->get('id')){
				return true;
			}
		}

		return false;
	}

	public function getHistory(){
		//return Database::prepare('SELECT * FROM `cash_log` WHERE `owner` = :userId')
		// SELECT * FROM `log` WHERE `data` LIKE "%\"isExpired\":true%" LIMIT 10
		$data = [];
		$history = Database::prepare('SELECT * FROM `log` WHERE `type` = :type AND (`data` LIKE :userId OR `data` LIKE :userId2)')
					->bind(':type', 'Cash')
					->bind(':userId', '%"user":' . $this->user->get('id') . '%')
					->bind(':userId2', '%"user":"' . $this->user->get('id') . '"%')
					->exec()
					->fetch();

		foreach($history as $item){
			$iData = json_decode($item['data'], true);
			$data[] = [
				'user' => $iData['user'],
				'balance' => $iData['balance'],
				'date' => $item['date'],
				'message' => $iData['message'],
			];
		}

		return $data;
	}

	private function setBalance(): bool {
		if(!$this->user->isAuthorized()) return false;
		return Database::prepare('UPDATE `cash` SET `balance` = :balance WHERE `owner` = :userId')
					->bind(':userId', $this->user->get('id'))
					->bind(':balance', $this->balance)
					->exec()
					->affectedRows() > 0;	
	}

	/**
	 * Добавить сумму
	 * @param string $sum
	 */
	public function add(string $sum, string $description = null){
		$this->balance = bcadd($this->balance, $sum, self::ACCURACY);
		Log::Cash($description, [
			'user' => $this->user->get('id'),
			'balance' => '+' . $sum
		]);
		$this->setBalance();
	}

	/**
	 * Вычесть сумму
	 * @param string $sum
	 */
	public function sub(string $sum, string $description = null){
		$this->balance = bcsub($this->balance, $sum, self::ACCURACY);
		Log::Cash($description, [
			'user' => $this->user->get('id'),
			'balance' => '-' . $sum
		]);
		$this->setBalance();
	}

	/**
	 * Получить разницу
	 * @param string $sum
	 */
	public function diff(string $sum): string {
		return bcsub($sum, $this->balance, self::ACCURACY);
	}

	/**
	 * Вычесть сумму
	 * @param string $sum
	 * @return int 0, если числа равны; 1, если $sum больше; -1, если меньше.
	 */
	public function compare(string $sum): int {
		return bccomp($sum, $this->balance, self::ACCURACY);
	}
}