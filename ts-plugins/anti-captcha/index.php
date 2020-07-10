<?php
/**
 * Плагин для работы с https://anti-captcha.com/
 * Github https://github.com/AdminAnticaptcha/anticaptcha-php
 */
namespace tsframe;

Hook::register('plugin.install', function(){
    return [
        PluginInstaller::withKey('anticaptcha.apiKey')
                       ->setDescription('Ключ для <a href="https://anti-captcha.com/" target="blank">AntiCaptcha API</a>')
                       ->setRequired(true)
    ];
});