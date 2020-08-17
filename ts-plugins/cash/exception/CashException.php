<?php 
namespace tsframe\exception;

use tsframe\exception\BaseException;
use tsframe\module\Log;

class CashException extends BaseException {
    public function __construct(string $message = null, int $code = 0, array $debugData = []){
		Log::cash('[Error] ' . $message, ['type' => 'error', 'debug' => $debugData]);
		return parent::__construct($message, $code, $debugData);
	}
}