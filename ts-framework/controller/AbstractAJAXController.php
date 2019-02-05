<?php
namespace tsframe\controller;

use tsframe\Http;

abstract class AbstractAJAXController extends AbstractController{
	protected $responseType = Http::TYPE_JSON;

	public function getResponseBody() : string {
		return json_encode($this->responseBody);
	}	

	public function sendError(string $errorText, int $errorCode = 0, array $additional = []){
		$this->responseBody = array_merge([
			'error' => $errorText,
			'code' => $errorCode
		], $additional);

		$this->responseCode = Http::CODE_BAD_REQUEST;
	}

	public function sendOK(){
		$this->responseBody = "OK";
		$this->responseCode = Http::CODE_OK;
	}

	public function sendMessage(string $message, int $messageCode = 0){
		$this->responseBody = [
			'message' => $message,
			'code' => $messageCode
		];
		$this->responseCode = Http::CODE_OK;
	}

	public function sendData(array $data){
		$this->responseBody = $data;
		$this->responseCode = Http::CODE_OK;
	}

	public function send(){
		Http::acceptCORS();
		parent::send();
	}
}