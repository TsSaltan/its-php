<?php
namespace tsframe\exception;
use tsframe\module\user\User;

class AccessException extends BaseException{
	public function __construct(string $message = 'Invalid Access', int $httpCode = 403, array $debugData = []){
		$debugData['currentUser'] = User::current();
		return parent::__construct($message, $httpCode = 403, $debugData);
	}
}