<?php
namespace tsframe\module\menu;

use tsframe\exception\MenuException;
use tsframe\Hook;

class Menu {
	protected static $menus = [];

	public static function create(string $menuName): MenuItem {
		self::$menus[$menuName] = new MenuItem;
		return self::$menus[$menuName];
	}

	/**
	 * Отрисовка меню
	 * @param  string   $menuName Имя меню
	 * @param  callable $onParent function(string $child, int $level){	return '<ul>' . $child . '</ul>'; }
	 * @param  callable $onItem   function(MenuItem $menu, string $subMenu, int $level){ return '<li><a href="'. $menu->getData('url') .'">'. $menu->getTitle() .'</a>'. $subMenu .'</li>'; }
	 * @return string
	 */
	public static function render(string $menuName, callable $onParent, callable $onItem): string {
		if(!isset(self::$menus[$menuName])){
			throw new MenuException('Menu '.$menuName.' does not exist', 0, [
				'menuName' => $menuName,
				'menus' => self::$menus
			]);
		}
		$menu = self::$menus[$menuName];
		Hook::call('menu.render', [$menuName, $menu]);
		Hook::call('menu.render.' . $menuName, [$menu]);
		return self::recursiveCall($menu->getChildren(), $onParent, $onItem);
	}

	protected static function recursiveCall(array $menus, callable $onParent, callable $onItem, int $level = 0){
		$itemsStr = '';
		foreach($menus as $menu){
			$children = $menu->getChildren();
			$childStr = '';
			if(sizeof($children) > 0){
				$childStr .= self::recursiveCall($children, $onParent, $onItem, $level+1);
			}

			$itemsStr .= call_user_func($onItem, $menu, $childStr, $level);
		}

		return call_user_func($onParent, $itemsStr, $level);
	}
}