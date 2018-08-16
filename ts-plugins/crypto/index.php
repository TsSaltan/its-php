<?
/**
 * Добавление криптографических функций
 */
namespace tsframe;

use tsframe\module\Crypto;

Hook::registerOnce('app.install', function(){
	if(Config::get('appId') == null){
		Config::set('appId', Crypto::generateString(64));
	}
});