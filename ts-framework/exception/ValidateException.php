<?php
namespace tsframe\exception;

class ValidateException extends BaseException{
	public function getInvalidKeys() : array {
		return array_keys($this->getDebug()['invalidKeys'] ?? []);
	}
}