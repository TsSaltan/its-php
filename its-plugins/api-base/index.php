<?php
/**
 * Базовая система API
 * API hooks: api.method.action
 * method = def | get | post | etc ...
 * function(BaseApiController $api){
 * 		$api->sendError('Error ...');
 * 		// or
 * 		$api->sendData(['result' => 'success', ...]);
 * }
 */
namespace tsframe;

use tsframe\controller\BaseApiController;

Hook::register('router', function($method, $uri){
	if(strpos($uri, '/api') === 0){
		return new BaseApiController;
	}
});