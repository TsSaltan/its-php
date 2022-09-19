<?php 
namespace tsframe\module;

use tsframe\exception\SitemapException;
use tsframe\module\SitemapItem;

class SitemapGenerator {
	const LIST_FILE = APP_STORAGE . 'sitemap-urls.json';
	protected static $items = [];

	public static function loadData(): array {
		if(sizeof(self::$items) > 0){
			return self::$items;
		}

		if(!file_exists(self::LIST_FILE)){
			return [];
		}

		$data = file_get_contents(self::LIST_FILE);
		$items = json_decode($data, true);
		foreach($items as $item){
			try {
				$i = new SitemapItem($item['loc'], $item['lastmod'], $item['changefreq'], $item['priority']);
			} catch (SitemapException $e){
				continue;
			}

			self::$items[$item['loc']] = $i;
		}

		return self::$items;
	}

	public static function saveData(){
		$data = [];
		foreach(self::$items as $i){
			$data[] = $i->getData();
		}

		file_put_contents(self::LIST_FILE, json_encode($data, JSON_PRETTY_PRINT));
	}

	public static function addItem(SitemapItem $item){
		self::loadData();
		self::$items[$item->getLoc()] = $item;
		self::saveData();
	}

	public static function removeItem(SitemapItem $item){
		self::loadData();
		unset(self::$items[$item->getLoc()]);
		self::saveData();
	}

	public static function isItemExists(SitemapItem $item): bool {
		return isset(self::$items[$item->getLoc()]);
	}
}