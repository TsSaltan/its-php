<?php
namespace tsframe\module\user\cash;

use tsframe\module\Crypto;
use tsframe\module\database\Database;

class Codes {
	public static function getCodes(): array {
		return Database::prepare('SELECT * FROM `cash-codes`')->exec()->fetch();
	}	

	public static function getCodeBalance(string $code): ?string {
		$res = Database::prepare('SELECT * FROM `cash-codes` WHERE `code` = :code')
				->bind('code', $code)
				->exec()->fetch();

		return $res[0]['balance'] ?? null ;
	}

	public static function deleteCode(string $code) {
		Database::prepare('DELETE FROM `cash-codes` WHERE `code` = :code')
				->bind('code', $code)
				->exec();
	}

	public static function addCode(string $balance): string {
		$code = Crypto::generateString(9) . '-' . Crypto::generateString(10) . '-' . Crypto::generateString(9);
		Database::prepare('INSERT INTO `cash-codes` (`code`, `balance`) VALUES (:code, :balance)')
			->bind('code', $code)
			->bind('balance', $balance)
			->exec();
		return $code;
	}
}