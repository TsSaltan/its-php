<?php
namespace tsframe\module\io;

use tsframe\exception\InputException;

/**
 * Валидатор исходящих данных
 */
class Output extends Filter {
	/**
	 * Ссылка на обрабатываемые данные 
	 * @var &mixed
	 */
	protected $data;

	/**
	 * Конструкторы
	 */
	public function __construct(&$data){
		$this->data = &$data;
	}

	public static function of(&$data){
		return new self($data);
	}
	
	/**
	 * Обращение к конкретному фильтру
	 * @param  string $method Имя фильтры
	 * @param  array  $params Дополнительные аргументы
	 */
	public function __call(string $method, array $params = []){
		$this->data = $this->filterData($this->data, $method, $params);
		return $this;
	}

	public function getData(){
		return $this->data;
	}

	protected function filterData($data, string $filter, array $args = []){
		if(is_array($data)){
			foreach ($data as $key => $value) {
				if(!is_numeric($key)){
					unset($data[$key]);
					$key = $this->filterData($key, $filter, $args);
				}

				$data[$key] = $this->filterData($value, $filter, $args);
			}
		} else {
			$data = $this->callFilter($filter, array_merge([$data], $args));
		}

		return $data;
	}
}

/**
 * Добавление фильтров
 */
Output::addFilter('tags', function($data, string $avaliableTags = null){
	return strip_tags($data, $avaliableTags);
});

Output::addFilter('quotes', function($data){
	return str_replace(['"', "'"], ['&quot;', '&apos;'], $data);
});

Output::addFilter('specialChars', function($data){
	return htmlspecialchars($data, ENT_NOQUOTES);
});

/**
 * Очищает HTML код от возможных XSS инъекций
 * @link https://gist.github.com/mbijon/1098477/4e245ab3844ddbd0e7c6f9fdf360501517e121cf
 */
Output::addFilter('xss', function($data){
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
});