<?php
/**
 * Базовая система API
 */
namespace tsframe;

use tsframe\controller\BaseApiController;

Hook::register('router', function($method, $uri){
	if(strpos($uri, '/api') === 0){
		return new BaseApiController;
	}
});