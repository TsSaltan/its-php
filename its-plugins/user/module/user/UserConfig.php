<?php
namespace tsframe\module\user;
use tsframe\Config;

class UserConfig {
	/**
	 * Разрешена ли регистрация
	 * @return bool
	 */
	public static function isRegisterEnabled(): bool {
		$reg = Config::get('user.auth.register');
		return is_null($reg) ? true : boolval($reg);
	}

	public static function setRegisterEnabled(bool $reg){
		Config::set('user.auth.register', $reg);
	}

	/**
	 * Разрешена ли авторизация через соц. сети
	 * @return bool
	 */
	public static function isSocialEnabled(): bool {
		$soc = Config::get('user.auth.social');
		return is_null($soc) ? true : boolval($soc);
	}

	public static function setSocialEnabled(bool $soc){
		Config::set('user.auth.social', $soc);
	}

	/**
	 * Используется ли логин во время авторизации/регистрации
	 * @return boolean (default: true)
	 */
	public static function isLoginEnabled(): bool {
		$login = Config::get('user.auth.login');
		return is_null($login) ? true : boolval($login);
	}

	public static function setLoginEnabled(bool $used) {
		Config::set('user.auth.login', $used);
	}

	/**
	 * Используется ли пароль во время регистрации
	 * Если false, то пароль будет автоматически генерироваться
	 * @return boolean (default: true)
	 */
	public static function isPasswordEnabled(): bool {
		$password = Config::get('user.auth.password');
		return is_null($password) ? true : boolval($password);
	}

	public static function setPasswordEnabled(bool $enabled) {
		Config::set('user.auth.password', $enabled);
	}

	/**
	 * Отправлять e-mail после регистрации
	 * @return boolean (default: false)
	 */
	public static function isEmailOnRegister(): bool {
		$email = Config::get('user.auth.emailOnRegister');
		return is_null($email) ? false : boolval($email);
	}

	public static function setEmailOnRegister(bool $value) {
		Config::set('user.auth.emailOnRegister', $value);
	}

	/**
	 * Автоматически авторизовывать после регистрации
	 * @return boolean (default: true)
	 */
	public static function isLoginOnRegister(): bool {
		$k = Config::get('user.auth.loginOnRegister');
		return is_null($k) ? true : boolval($k);
	}

	public static function setLoginOnRegister(bool $value) {
		Config::set('user.auth.loginOnRegister', $value);
	}

	/**
	 * Включить возможность восстанавливать пароль
	 * @return boolean (default: false)
	 */
	public static function isRestorePassword(): bool {
		$restore = Config::get('user.auth.restorePassword');
		return is_null($restore) ? false : boolval($restore);
	}

	
	public static function setRestorePassword(bool $value) {
		Config::set('user.auth.restorePassword', $value);
	}
}