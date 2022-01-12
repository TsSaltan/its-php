<?php
namespace tsframe\module\Geo;

abstract class GeoItem{
	protected $id, $name;

	public function __construct(int $id, string $name){
		$this->id = $id;
		$this->name = $name;
	}

	/**
	 * @return int
	 */
	public function getId(): int {
	    return $this->id;
	}

	/**
	 * @return string
	 */
	public function getName(): string {
	    return $this->name;
	}

	/**
	 * @return string
	 */
	public function getCode(): ?string {
	    return $this->alias;
	}

	public function __toString(){
		return $this->getName();
	}
}