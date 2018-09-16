<?php
namespace tsframe\module\user;

use tsframe\Config;
use tsframe\exception\AccessException;
use tsframe\Reflect;


class UserAccess{
	const Guest = 0,
		  User = 1,
		  Moderator = 2,
		  Admin = 4;

	public static function default() : int {
		return self::User;
	}

	public static function getArray() : array {
		return Reflect::getConstants(__CLASS__);
    }

    public static function setAccess(string $rule, int $access){
    	Config::set('access.' . $rule, $access);
    }

    /**
     * Получить уровень доступа для определенного действия
     * @param  string $rule [description]
     * @return int
     */
    public static function getAccess(string $rule): int {
    	$access = Config::get('access.' . $rule);
    	return is_int($access) ? $access : self::Admin;
    }

    /**
     * Проверить, есть ли у пользователя права доступа
     * @param  SingleUser $user
     * @param  string|int     $rule Значение уровня доступа или имя правила
     * @return bool
     */
    public static function checkUser(SingleUser $user, $rule): bool {
    	$required = is_int($rule) ? $rule : self::getAccess($rule);
    	return $user->get('access') >= $required;
    }   

    /**
     * Проверить, есть ли у текущего пользователя права доступа
     * @param  string     $rule
     * @return bool
     */
    public static function checkCurrentUser($rule): bool {
    	return self::checkUser(User::current(), $rule);
    }

    public static function assert(SingleUser $user, $rule){
        if(!self::checkUser($user, $rule)){
            throw new AccessException('Access denied for action "' . $rule . '"');
        }
    }

    public static function assertCurrentUser($rule){
    	return self::assert(User::current(), $rule);
    }
}