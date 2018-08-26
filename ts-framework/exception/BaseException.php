<?php
namespace tsframe\exception;

use tsframe\App;
use tsframe\Http;
use tsframe\Log;

class BaseException extends \Exception{
	
	private $debugData;
	public function getDebug(){
		return $this->debugData;
	}
		
	public function __construct(string $message = null, int $httpCode = 0, array $debugData = []){
		$this->debugData = $debugData;
		$this->debugData['trace'] = explode("\n", $this->getTraceAsString());
		return parent::__construct($message, $httpCode, null);
	}

	public function dump(bool $return = false){
		switch(Http::getContentType()){
			case 'text/html':
				$eol = '<br/>';
				$pre = '<h2 style="color:#EC429B">';
				$post = '</h2>';
				
				$pre_h = '<b>';
				$post_h = '</b>';
				
				$pre_c = '<pre>';
				$post_c = '</pre>';
			break;
				
			case 'text/plain':
			default:
			
				$eol = PHP_EOL;
				$pre = '*****';
				$post = '*****'.PHP_EOL;
				
				$pre_h = '  ';
				$post_h = '   ';
				
				$pre_c = PHP_EOL . '-----Start----' . PHP_EOL;
				$post_c = PHP_EOL . '-----End----' . PHP_EOL;
		}
		 
		if($return){
			ob_start();
		} 

		?>
			<?=$pre?>Exception dump:<?=$post?>
			<?=$pre_h?>Thrown:<?=$post_h?> <?=get_class($this).$eol?>
			<?=$pre_h?>Message:<?=$post_h?> <?=$this->getMessage().$eol?>
			<?=$pre_h?>Code:<?=$post_h?> <?=$this->getCode().$eol?>
			<?=$pre_h?>Debug:<?=$post_h?> <?=$pre_c.var_export($this->getDebug(), true).$post_c?>
		<?

		if($return){
			return ob_get_clean();
		} 
	}
}