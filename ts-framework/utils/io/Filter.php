<?php
namespace tsframe\utils\io;

use tsframe\exception\ValidateException;

/**
 * Фильтр данных
 */
class Filter {
	protected $data;
	protected $recursive = true;

	public static function of($data, bool $recursive = false) {
		$class = new self($data);
		$class->setRecursive($recursive);
		return $class;
	}

	public function __construct($data){
		$this->data = $data;
	}

	public function setRecursive(bool $recursive){
		$this->recursive = $recursive;
	}

	public function getData(){
		return $this->data;
	}

	/**
	 * Убирает все HTML теги
	 * @param  string  $avaliableTags Разрешенные теги
	 */
	public function withoutTags(string $avaliableTags = null){
		$this->processData(function($var) use ($avaliableTags){
			return strip_tags($var, $avaliableTags);
		}, $this->data);

		return $this;
	}

	/**
	 * Сохраняет только теги, используемые в форматировании текста - i, b, a, pre, code, img
	 */
	public function saveFormatTags(){
		$this->withoutTags('<i><b><a><pre><code><img>');
		return $this;
	}

	/**
	 * Убирает одинарные и двойные кавычки
	 */
	public function withoutQuotes(){
		$this->processData(function($var){
			return str_replace(['"', "'"], ['&quot;', '&apos;'], $var);
		}, $this->data);

		return $this;
	}

	/**
	 * Заменяет все небуквенные символы на иъ коды (кроме кавычек)
	 */
	public function specialChars(){
		$this->processData(function($var){
			return htmlspecialchars($var, ENT_NOQUOTES);
		}, $this->data);

		return $this;
	}	

	/**
	 * Очищает HTML код от возможных XSS инъекций
	 * @link https://gist.github.com/mbijon/1098477/4e245ab3844ddbd0e7c6f9fdf360501517e121cf
	 */
	public function withoutXSS(){
		$this->processData(function($data){
			// Fix &entity\n;
	        $data = str_replace(array('&amp;','&lt;','&gt;'), array('&amp;amp;','&amp;lt;','&amp;gt;'), $data);
	        $data = preg_replace('/(&#*\w+)[\x00-\x20]+;/u', '$1;', $data);
	        $data = preg_replace('/(&#x*[0-9A-F]+);*/iu', '$1;', $data);
	        $data = html_entity_decode($data, ENT_COMPAT, 'UTF-8');

	        // Remove any attribute starting with "on" or xmlns
	        $data = preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+>#iu', '$1>', $data);

	        // Remove javascript: and vbscript: protocols
	        $data = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2nojavascript...', $data);
	        $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2novbscript...', $data);
	        $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u', '$1=$2nomozbinding...', $data);

	        // Only works in IE: <span style="width: expression(alert('Ping!'));"></span>
	        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
	        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?behaviour[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
	        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#iu', '$1>', $data);

	        // fix
	        $data = preg_replace('#([\w]+=[\W]?)javascript[:|&]+#Ui', '$1nojavascript...', $data);

	        // Remove namespaced elements (we do not need them)
	        $data = preg_replace('#</*\w+:\w[^>]*+>#i', '', $data);

	        do
	        {
	            // Remove really unwanted tags
	            $old_data = $data;
	            $data = preg_replace('#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i', '', $data);
	        }
	        while ($old_data !== $data);

	        return $data;
	        
		}, $this->data);

		return $this;
	}

	protected function processData(callable $callback, &$data){
		if(is_array($data)){
			foreach ($data as $key => $value) {
				if(is_array($value) && $this->recursive){
					$data[$key] = $this->processData($callback, $value);
				}
				else $data[$key] = $this->callFilter($callback, $value);
			}
		} else {
			$data = $this->callFilter($callback, $data);
		}

		return $data;
	}

	protected function callFilter(callable $callback, $data){
		if(is_string($data)){
			return call_user_func($callback, $data);
		}

		return $data;
	}
}