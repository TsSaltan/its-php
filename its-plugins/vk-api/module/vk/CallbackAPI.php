<?php 
namespace tsframe\module\vk;

use tsframe\exception\VKException;
use tsframe\module\vk\vkAPI;

class CallbackAPI {
	/**
	 * @var array
	 */
	protected $inputData = [];

	public function getInputData(): array {
		if(sizeof($this->inputData) > 0) return $this->inputData;

		$input = file_get_contents('php://input');
		$this->inputData = json_decode($input, true); 

		if(!is_array($this->inputData) || sizeof($this->inputData) == 0){
			$this->inputData = [];
			throw new VKException('[CallbackAPI] Invalid input data', 0, [
				'input' => $input
			]);
		}

		return $this->inputData;
	}

	public function hasData(): bool {
		try{
			$data = $this->getInputData();
			return sizeof($data) > 0;
		} catch (VKException $e){
			return false;
		}
	}
}