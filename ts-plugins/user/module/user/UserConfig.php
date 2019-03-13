<?php
namespace tsframe\module\user;
use tsframe\Config;

class UserConfig{
	/**
	 * Разрешена ли регистрация
	 * @return bool
	 */
	public static function canRegister(): bool {
		$reg = Config::get('user.canRegister');
		return is_null($reg) ? true : boolval($reg);
	}

	public static function setRegister(bool $reg){
		Config::set('user.canRegister', $reg);
	}

	/**
	 * Разрешена ли авторизация через соц. сети
	 * @return bool
	 */
	public static function canSocial(): bool {
		$soc = Config::get('user.canSocial');
		return is_null($soc) ? true : boolval($soc);
	}

	public static function setSocial(bool $soc){
		Config::set('user.canSocial', $soc);
	}

	/**
	 * Используется ли логин во время авторизации/регистрации
	 * @return boolean По умолчанию true
	 */
	public static function isLoginUsed(): bool {
		$loginUsed = Config::get('user.loginUsed');
		return is_null($loginUsed) ? true : boolval($loginUsed);
	}

	public static function setLoginUsed(bool $used) {
		Config::set('user.loginUsed', $used);
	}
}