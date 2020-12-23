<?php
namespace tsframe\exception;

use tsframe\App;
use tsframe\Http;

class BaseException extends \Exception{

	/**
	 * Данные для дебага
	 * @var array
	 */
	protected $debugData;
		
	public static function create(string $message = null, int $code = 0){
		return new self($message, $code);
	}

	public function __construct(string $message = null, int $code = 0, array $debugData = []){
		$this->setDebug($debugData);

		return parent::__construct($message, $code, null);
	}

	public function setDebug(array $debugData){
		$this->debugData = $debugData;
		$this->debugData['trace'] = explode("\n", $this->getTraceAsString());
		return $this;
	}

	public function getDebug(): array {
		return $this->debugData;
	}

	public function getDump(): string {
		ob_start();
		$this->dump();
		return ob_get_clean();
	}

	public function dump(){
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

		?>
			<?=$pre?>Exception dump:<?=$post?>
			<?=$pre_h?>Thrown:<?=$post_h?> <?=get_class($this).$eol?>
			<?=$pre_h?>Message:<?=$post_h?> <?=$this->getMessage().$eol?>
			<?=$pre_h?>Code:<?=$post_h?> <?=$this->getCode().$eol?>
			<?=$pre_h?>Debug:<?=$post_h?> <?=$pre_c.var_export($this->getDebug(), true).$post_c?>
		<?php
	}

	public function throw(){
		throw $this;
	}
}