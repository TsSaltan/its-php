<?php
namespace tsframe\controller;

use tsframe\Http;

/**
 * Абстрактный класс контроллера
 * Порядок возова методов таков:
 * 1. Парсится phpDoc комметнарий @"route METHOD PATH"
 * 2. Если route совпал, создается экземпляр класса (__construct)
 * 3. Вызывается метод setParams для установки параметров из URI
 * 4. Для генерации тела ответа вызывается метод response, если он что-то возвращает, данные заносятся в ->responseBody
 * 5. Вызывается метод send для отправки http ответа,
 * 6. Для генерации ответа берутся данные из getResponseType, getResponseCode, getResponseBody
 */
abstract class AbstractController {
	public function response(){
	}

	protected $params = [];
	public function setParams(array $params){
		$this->params = $params;
		$this->apply();
	}

	/**
	 * Метод вызывается после применения параметров через метод setParams
	 * Необходим как метод "конструктор", который вызывается в контроллере,
	 * после применения параметров через setParams()
	 **/
	protected function apply(){
		// any code from child class 
	}

	public function getRequestMethod() : string {
		return Http::getRequestMethod();
	}

	protected $responseBody = '';
	public function getResponseBody() : string {
		return $this->responseBody;
	}

	protected $responseType = Http::TYPE_HTML;
	public function getResponseType() : string {
		return $this->responseType;
	}

	protected $responseCode = Http::CODE_OK;
	public function getResponseCode() : int {
		return $this->responseCode;
	}

	public function send(){
		$resp = $this->response();
		if(!is_null($resp)){
			$this->responseBody = $resp;
		}

		$body = $this->getResponseBody(); 
		$code = $this->getResponseCode(); 
		$type = $this->getResponseType(); 

		Http::sendBody($body, $code, $type);
	}
}