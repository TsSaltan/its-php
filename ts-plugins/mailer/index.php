<?php
/**
 * Плагин для отправки электронной почты, используя STMP
 */
namespace tsframe;

use tsframe\Hook;
use tsframe\PluginInstaller;

Hook::registerOnce('plugin.install', function(){
	return [
		PluginInstaller::withKey('mailer.host')
					->setType('text')
					->setDescription("Отправка почты: Имя хоста")
					->setPlaceholder("smtp1.example.com;smtp2.example.com"),

		PluginInstaller::withKey('mailer.port')
					->setType('numeric')
					->setDescription("Отправка почты: Порт")
					->setPlaceholder("443"),

		PluginInstaller::withKey('mailer.email')
					->setType('text')
					->setDescription("Отправка почты: Почтовый ящик отправителя")
					->setPlaceholder("user@example.com"),

		PluginInstaller::withKey('mailer.password')
					->setType('text')
					->setDescription("Отправка почты: Пароль")
					->setPlaceholder("***"),

		PluginInstaller::withKey('mailer.secure')
					->setType('select')
					->setDescription("Отправка почты: Шифрование")
					->setValues(['ssl' => 'SSL', 'tls' => 'TLS', 'none' => 'Без шифрования']),

	];
});