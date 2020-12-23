<?php
namespace tsframe\controller;

use tsframe\Config;
use tsframe\Hook;
use tsframe\Http;
use tsframe\module\Logger;
use tsframe\module\vk\CallbackAPI;

/**
 * @route GET|POST /vk-callback
 * @route GET|POST /vk-callback/
 * @route GET|POST /vk-callback/[:group_id]
 */ 
class VKCallbackController extends AbstractController {
	public static $log = true;

	public function response(){
		$this->responseType = Http::TYPE_PLAIN;
		$this->responseBody = 'ok';

		$groupId = $this->params['group_id'] ?? -1;
		$groupData = Config::get('vk.groups.'.$groupId);
		$data = [];
		$cbApi = new CallbackAPI;

		if(is_null($groupData)){
			$this->responseBody = 'Error: Invalid callback URI.';
		}
		elseif($cbApi->hasData()){
			$data = $cbApi->getInputData();
			$secretKey = $groupData['secret'] ?? null;
			$confirmCode = $groupData['confirm'] ?? null;

			if(!isset($data['secret']) || $data['secret'] != $secretKey){
				$this->responseBody = 'Error: Invalid secret key.';
			} 
			elseif($data['group_id'] != $groupId){
				$this->responseBody = 'Error: Invalid group id.';
			} 
			else {
				switch($data['type']){
					case 'confirmation':
						$this->responseBody = $confirmCode;
						break;

					default:
						/**
						 * @hook vk.%event% (array $data);
						 */
						Hook::call('vk.' . $data['type'], [$data], function($response){
							if(is_string($response) && strlen($response) > 0){
								$this->responseBody = $response;
							}
						}, function($error){
							$this->responseBody = 'Error: ' . $error->getMessage();
						});
				}
			}


		} else {
			$this->responseBody = 'null query';
		}

		if(self::$log) Logger::vkcallback()->debug('Incoming query from group_id=' . $groupId . '. Response: ' . $this->responseBody , $data);
	}
}