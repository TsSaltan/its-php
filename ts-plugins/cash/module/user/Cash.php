<?php
namespace tsframe\module\user;

use tsframe\module\Crypto;
use tsframe\module\database\Database;
use tsframe\module\database\Query;
use tsframe\module\interkassa\Payment;
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

	/**
	 * Получить историю всех платежей
	 * @param  int|integer $offset 
	 * @param  int|integer $count  
	 * @return array
	 */
	public static function getGlobalHistory(int $offset = 0, int $count = 0): array {
		return Log::getLogs('Cash', $offset, $count);
	}

	/**
	 * Получить текущую валюту
	 * @return string
	 */
	public static function getCurrency(): string {
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
	 * Получить баланс текущего пользователя
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

		// Убираем нули, если их много после точки
		// Но минимум 1 нуль должен быть
		$balance = rtrim($this->balance, '0');
		return $balance . (substr($balance, -1, 1) == '0' ? '' : '0');
	}

	/**
	 * Проверить, поступал ли платёж с таким ID
	 * @param  string  $trId ID транзакции
	 * @return boolean      
	 */
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

	/**
	 * Получить историю платежей текущего пользователя
	 * @return array ([user=>, balance=>, date=>, message=>, pay_id=>], ... )
	 */
	public function getHistory(): array {
		$data = [];

		// Только недавно mysql научился работать с json, поэтому для кроссплатформенности использую LIKE
		$history = Database::prepare('SELECT * FROM `log` WHERE `type` = :type AND (`data` LIKE :userId OR `data` LIKE :userId2) ORDER BY `date` DESC')
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
				'pay_id' => $iData['pay_id'] ?? null ,
			];
		}

		return $data;
	}

	/**
	 * Обновить баланс из базы данных
	 */
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
	 * @param string $sum 			Cумма операции
	 * @param string $description 	Описание платежа
	 * @param string $payId 		Уникальный идентификатор транзакции
	 */
	public function add(string $sum, string $description = null, string $payId = null){
		$this->balance = bcadd($this->balance, $sum, self::ACCURACY);
		Log::Cash($description, [
			'user' => $this->user->get('id'),
			'balance' => '+' . $sum,
			'pay_id' => !is_null($payId) ? $payId : Payment::createPayId($this->user->get('id'))
		]);
		$this->setBalance();
	}

	/**
	 * Вычесть сумму	 
	 * @param string $sum 			Cумма операции
	 * @param string $description 	Описание платежа
	 * @param string $payId 		Уникальный идентификатор транзакции
	 */
	public function sub(string $sum, string $description = null, string $payId = null){
		$this->balance = bcsub($this->balance, $sum, self::ACCURACY);
		Log::Cash($description, [
			'user' => $this->user->get('id'),
			'balance' => '-' . $sum,
			'pay_id' => !is_null($payId) ? $payId : Payment::createPayId($this->user->get('id'))
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
	 * Сравнить сумму с текущим балансом
	 * @param string $sum
	 * @return int 0, если числа равны; 1, если $sum больше; -1, если меньше.
	 */
	public function compare(string $sum): int {
		return bccomp($sum, $this->balance, self::ACCURACY);
	}
}