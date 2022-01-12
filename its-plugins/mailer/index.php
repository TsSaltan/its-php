<?php
/**
 * Плагин для отправки электронной почты, используя STMP
 */
namespace tsframe;

use tsframe\Hook;
use tsframe\PluginInstaller;

Hook::registerOnce('plugin.install', function(){
	return [
		PluginInstaller::withKey('mailer.sender')
					->setType('select')
					->setDescription("Отправка почты: Метод отправки почты")
					->setValues(['mailer' => 'PHP Mail', 'smtp' => 'SMTP']),

		PluginInstaller::withKey('mailer.from.email')
					->setType('text')
					->setDescription("Отправка почты: Почтовый ящик отправителя")
					->setPlaceholder("user@example.com"),

		PluginInstaller::withKey('mailer.from.name')
					->setType('text')
					->setDescription("Отправка почты: Имя отправителя")
					->setPlaceholder("Site Admin"),

		PluginInstaller::withKey('mailer.smtp.host')
					->setType('text')
					->setDescription("Отправка почты [SMTP-only]: Имя хоста")
					->setPlaceholder("smtp1.example.com;smtp2.example.com"),

		PluginInstaller::withKey('mailer.smtp.port')
					->setType('numeric')
					->setDescription("Отправка почты [SMTP-only]: Порт")
					->setPlaceholder("443"),


		PluginInstaller::withKey('mailer.smtp.password')
					->setType('text')
					->setDescription("Отправка почты [SMTP-only]: Пароль")
					->setPlaceholder("***"),

		PluginInstaller::withKey('mailer.smtp.secure')
					->setType('select')
					->setDescription("Отправка почты [SMTP-only]: Метод шифрования")
					->setValues(['ssl' => 'SSL', 'tls' => 'TLS', 'none' => 'Без шифрования']),
	];
});