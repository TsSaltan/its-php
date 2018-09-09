<?
/**
 * Кеширование данных
 */

namespace tsframe;

Hook::registerOnce('plugin.load', function(){
	Plugins::required('database');
});
