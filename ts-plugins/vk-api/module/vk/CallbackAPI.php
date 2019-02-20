<?php 
namespace tsframe\module\vk;

use tsframe\exception\VKException;
use tsframe\module\vk\vkAPI;

class CallbackAPI {
	/**
	 * Токен для подтверждения callback_url
	 * @var string
	 */
	protected $confirmToken;

	/**
	 * Токен для совершения запросов к API
	 * @var string
	 */
	protected $accessToken;

	/**
	 * @var array
	 */
	protected $inputData = [];

	public function __construct(?string $accessToken = null, ?string $confirmToken = null){
		if(!is_null($accessToken)) $this->setAccessToken($accessToken);
		if(!is_null($confirmToken)) $this->setConfirmToken($confirmToken);
	}

	/**
	 * @param string $confirmToken
	 */
	public function setConfirmToken(string $confirmToken){
	    $this->confirmToken = $confirmToken;
	    return $this;
	}

	/**
	 * @param string $accessToken
	 */
	public function setAccessToken(string $accessToken){
	    $this->accessToken = $accessToken;
	    return $this;
	}

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

	/**
	 * @return type
	 */
	public function getConfirmToken(): string {
	    return $this->confirmToken;
	}

	public function getApi(): vkAPI {
		return new vkAPI($this->accessToken);
	}
}