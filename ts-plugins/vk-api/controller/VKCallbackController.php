<?php
namespace tsframe\controller;

use tsframe\Config;
use tsframe\Hook;
use tsframe\Http;
use tsframe\module\vk\CallbackAPI;

/**
 * @route GET|POST /vk-callback/
 */ 
class VKCallbackController extends AbstractController {
	public function response(){
		$this->responseType = Http::TYPE_PLAIN;

		$confirmToken = Config::get('vk.confirmToken');
		$gAccessToken = Config::get('vk.groupAccessToken');
		$cbApi = new CallbackAPI($gAccessToken, $confirmToken);
		
		if($cbApi->hasData()){
			$data = $cbApi->getInputData();
			switch($data['type']){
				case 'confirmation':
					return $cbApi->getConfirmToken();

				default:
					/**
					 * @hook vk.%event% (vkAPI $api, array $data);
					 */
					Hook::call('vk.' . $data['type'], [$cbApi->getApi(), $data]);
			}
			return "ok";
		}

		return "null";
	}
}