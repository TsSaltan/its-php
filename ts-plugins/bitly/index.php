<?php
/**
 * Поддежрка сокращённых ссылок bitly
 * @url https://app.bitly.com/
 */
namespace tsframe;

Hook::registerOnce('plugin.install', function(){
    return [
        PluginInstaller::withKey('bitly.accessToken')
                       ->setDescription("Ключ доступа для <a href='https://app.bitly.com/' target='_blank'>app.bitly.com</a>")
                       ->setRequired(true)
    ];
});