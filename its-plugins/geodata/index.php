<?php
/**
 * База данных стран, регионов и городов
 * + GeoIP запросы
 */
namespace tsframe;

use tsframe\Hook;
use tsframe\Plugins;
use tsframe\module\Geo\GeoIP;
use tsframe\view\TemplateRoot;

Hook::registerOnce('app.init', function(){
    TemplateRoot::add('geodata', __DIR__ . DS . 'template');   
});

Hook::registerOnce('plugin.install', function(){
    Plugins::required('database', 'cache');
});