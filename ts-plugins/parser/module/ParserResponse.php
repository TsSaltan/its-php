<?php
namespace tsframe\module;

class ParserResponse{
	/**
	 * Curl resourse
	 */
	protected $ch;

	/**
	 * @var string|null|bool
	 */
	protected $result;

	/**
	 * Query info
	 * @var array
	 */
	protected $info;

	public function __construct($ch){
		$this->ch = $ch;
		$this->result = curl_exec($this->ch);
		$this->info = curl_getinfo($this->ch);
	}

	public function getError(): string {
		return curl_error($this->ch);
	}

	public function hasError(): bool {
		return ($this->result !== false) && ($this->getResponseCode() >= 500);
	}

	public function getInfo(): array {
		return $this->info;
	}

	public function isRedirected(): bool {
		return ($this->info['redirect_count'] ?? 0) > 0;
	}

	public function getRedirectedURI(): string {
		return curl_getinfo($this->ch, CURLINFO_EFFECTIVE_URL);
	}

	public function getRequestHeader(): string {
		return curl_getinfo($this->ch, CURLINFO_HEADER_OUT);
	}

	public function getRequestMethod(): string {
		$headers = $this->getRequestHeader();
		if(preg_match('#([A-Z]{3,5})\s#U', $headers, $match)){
			return $match[1];
		}

		return 'GET';
	}

	public function getResponseBody(){
		return $this->result;
	}

	public function getResponseCode(): int {
		return $this->info['http_code'];
	}

	public function getResponseLength(): int {
		return $this->info['download_content_length'] ?? 0;
	}

	public function getResponseContentType(){
		return $this->info['content_type'];
	}

	public function getLogEntry(){

	}
}