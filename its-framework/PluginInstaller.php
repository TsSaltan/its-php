<?php
namespace tsframe;

use tsframe\exception\BaseException;
use tsframe\exception\PluginException;
use tsframe\module\io\Output;

class PluginInstaller {
	private $key,
			$type = "text",
			$description,
			$required = false,
			$currentValue,
			$defaultValue,
			$values = [],
			$params = [],
			$placeholder;

	public static function error(string $text): PluginInstaller {
		$self = new self('error');
		$self->setType('error');
		$self->setDescription($text);
		return $self;
	}

	public static function withKey(string $key): PluginInstaller {
		return new self($key);
	}

	public static function fromArray(string $key, array $params): PluginInstaller {
		$self = new self($key);
		if(isset($params['type'])) $self->setType($params['type']);
		if(isset($params['placeholder'])) $self->setPlaceholder($params['placeholder']);
		if(isset($params['value'])) $self->setDefaultValue($params['value']);
		if(isset($params['title'])) $self->setDescription($params['title']);
		return $self;
	}

	/**
	 * В конструкторе указываем ключ из файла конфигурации, куда будут сохранены данные
	 */
	public function __construct(string $key){
		$this->key = $key;
		$this->setDescription(ucfirst(str_replace('.', ' ', $key)));
	}

	/**
	 * Установить тип поля
	 * @param string $type = error|text|numeric|email|select|helper-text
	 */
	public function setType(string $type): PluginInstaller {
		$this->type = $type;
		return $this;
	}

	/**
	 * Указать подсказку
	 */
	public function setPlaceholder(string $text): PluginInstaller {
		$this->placeholder = $text;
		return $this;
	}

	/**
	 * Указать описание
	 */
	public function setDescription(string $text): PluginInstaller {
		$this->description = $text;
		return $this;
	}

	/**
	 * Указать значение по умолчанию
	 */
	public function setDefaultValue(string $value): PluginInstaller {
		$this->defaultValue = $value;
		return $this;
	}

	/**
	 * Указать текущее значение
	 */
	public function setCurrentValue(string $value): PluginInstaller {
		$this->currentValue = $value;
		return $this;
	}

	/**
	 * Указать возможные значения (для типа select)
	 * @param  array $values [значение => текст]
	 */
	public function setValues(array $values): PluginInstaller {
		$this->values = $values;
		return $this;
	}

	/**
	 * @param bool $required
	 */
	public function setRequired(bool $required): PluginInstaller {
	    $this->required = $required;
	    return $this;
	}

	/**
	 * Указать параметры для поля ввода
	 */
	public function setParams(array $params): PluginInstaller {
		$this->params = $params;
		return $this;
	}

	public function getId(){
	    return 'param_' . md5($this->key);
	}

	public function getConfigPart(){
	    return explode('.', $this->key)[0];
	}

	public function getKey(){
	    return Output::of($this->key)->quotes()->getData();
	}

	public function getType(){
	    return Output::of($this->type)->quotes()->getData();
	}

	public function getDescription(){
	    return Output::of($this->description)->xss()->getData();
	}

	public function getDefaultValue(){
	    return Output::of($this->defaultValue)->quotes()->getData();
	}

	public function getValue(){
		$value = strlen($this->currentValue) > 0 ? $this->currentValue : $this->defaultValue;
	    return Output::of($value)->quotes()->getData();
	}

	public function getValues(){
	    return Output::of($this->values)->quotes()->getData();
	}

	public function getParams(){
	    return Output::of($this->params)->quotes()->getData();
	}

	public function getPlaceholder(){
	    return Output::of($this->placeholder)->quotes()->getData();
	}

	/**
	 * @return bool
	 */
	public function getRequired(): bool	{
	    return $this->required;
	}
}