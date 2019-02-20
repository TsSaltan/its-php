<?php
namespace tsframe\controller;

use tsframe\Config;
use tsframe\Hook;
use tsframe\Http;
use tsframe\module\Log;
use tsframe\module\vk\CallbackAPI;

/**
 * @route GET|POST /vk-callback/
 */ 
class VKCallbackController extends AbstractController {
	public static $log = true;

	public function response(){
		$this->responseType = Http::TYPE_PLAIN;

		$confirmToken = Config::get('vk.confirmToken');
		$gAccessToken = Config::get('vk.groupAccessToken');
		$cbApi = new CallbackAPI($gAccessToken, $confirmToken);

		if($cbApi->hasData()){
			$data = $cbApi->getInputData();
			if($log) Log::VKCallback('Incoming query', $data);

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
		} else {
			if($log) Log::VKCallback('Incoming null query', []);
		}

		return "null";
	}
}