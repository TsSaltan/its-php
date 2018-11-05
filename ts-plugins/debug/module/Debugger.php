<?php
namespace tsframe\module;

class Debugger{

	protected $startTime;
	protected $counters = [];

	public function __construct(){
		$this->startTime = microtime(true);
	}

	public function dbQuery(string $data){
		$num = $this->addCounter('Database-Query-Total');
		$this->counters['Database-Query-' . $num] = $data;
	}

	public function addCounter(string $name, int $value = 1): int {
		return $this->counters[$name] = ($this->counters[$name] ?? 0) + $value;
	}

	public function getExecuteTime(): float {
		return (microtime(true) - $this->startTime) * 1000;
	}

	/**
	 * @return float MiB
	 */
	public function getUsedMemory(): float {
		return round(memory_get_usage() / 1024 / 1024, 2);
	}

	public function getData(): array {
		$this->counters['Execute-Time'] = $this->getExecuteTime() . ' ms';
		$this->counters['Used-Memory'] = $this->getUsedMemory() . ' MiB';
		return $this->counters;
	}
}