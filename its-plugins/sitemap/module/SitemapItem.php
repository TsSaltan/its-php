<?php 
namespace tsframe\module;

use tsframe\exception\SitemapException;
use tsframe\module\SitemapGenerator;

class SitemapItem {
	const DATE_FORMAT = 'Y-m-d';

	protected $loc = 'https://google.com';
	protected $lastmod = 0;
	protected $changefreq = 'monthly';
	protected $priority = 0.8;

	public function __construct(string $loc, $lastmod, string $changefreq, float $priority){
		$this->setLoc($loc);
		
		if(is_int($lastmod)){
			$this->setLastmodTs($lastmod);
		} else {
			$this->setLastmod($lastmod);
		}

		$this->setChangefreq($changefreq);
		$this->setPriority($priority);
	}

	public function setLoc(string $loc){
		if(!filter_var($loc, FILTER_VALIDATE_URL)){
			throw new SitemapException('Invalid "loc" value: ' . $loc);
		}

		$this->loc = $loc;
	}

	public function getLoc(): string {
		return $this->loc;	
	}

	public function setLastmod(string $lastmod){
		$dt = \DateTime::createFromFormat(self::DATE_FORMAT, $lastmod);
		$this->lastmod = $dt->format('U');
	}

	public function getLastmod(): string {
		return date(self::DATE_FORMAT, $this->lastmod);
	}

	public function setLastmodTs(int $lastmod){
		if($lastmod < 0){
			throw new SitemapException('Invalid "lastmod" value: ' . $lastmod);
		}

		$this->lastmod = $lastmod;
	}

	public function getLastmodTs(): int {
		return $this->lastmod;
	}

	public function setChangefreq(string $changefreq){
		$changefreq = strtolower($changefreq);
		$freqlist = ['always', 'hourly', 'daily', 'weekly', 'monthly', 'yearly', 'never'];
		if(!in_array($changefreq, $freqlist)){
			throw new SitemapException('Invalid "changefreq" value: ' . $changefreq . '. Avaliable values: ' . $freqlist);
		}

		$this->changefreq = $changefreq;
	}

	public function getChangefreq(): string {
		return $this->changefreq;
	}

	public function setPriority(float $priority){
		if($priority < 0 || $priority > 1){
			throw new SitemapException('Invalid "priority" value: ' . $priority . '. Avaliable values from 0.0 to 1.0');
		}

		$this->priority = $priority;
	}


	public function getPriority(): float {
		return $this->priority;
	}

	public function getData(): array {
		return [
			'loc' => $this->getLoc(),
			'changefreq' => $this->getChangefreq(),
			'priority' => $this->getPriority(),
			'lastmod' => $this->getLastmodTs()
		];
	}

	public function addToGenerator(){
		SitemapGenerator::addItem($this);
	}
}