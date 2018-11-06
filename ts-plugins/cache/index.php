<?
/**
 * Кеширование данных
 */

namespace tsframe;

Hook::registerOnce('plugin.install', function(){
	Plugins::required('database');
});
