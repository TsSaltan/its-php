<?php
namespace tsframe\exception;

class PluginException extends BaseException{
	public function getPluginName(): string {
		return $this->debugData['pluginName'] ?? '';
	}
}