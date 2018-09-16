<?php
namespace tsframe\exception;

class InputException extends BaseException{
	public function getInvalidKeys() : array {
		return array_keys($this->getDebug()['invalid'] ?? []);
	}
}