<?php 
namespace tsframe\exception;

use tsframe\exception\BaseException;
use tsframe\module\Logger;

class CashException extends BaseException {
    public function __construct(string $message = null, int $code = 0, array $debugData = []){
		Logger::cash()->error($message, ['type' => 'error', 'debug' => $debugData]);
		return parent::__construct($message, $code, $debugData);
	}
}