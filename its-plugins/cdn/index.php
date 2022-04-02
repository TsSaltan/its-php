<?php
/**
 * @link https://unpkg.com/
 */
namespace tsframe;

use tsframe\Hook;
use tsframe\Plugins;
use tsframe\view\Template;

Hook::register('template.resource', function(Template $tpl, string &$path, array &$files, bool &$break){
	if(strpos($path, 'cdn:') === 0){
		$files[] = str_replace('cdn:', 'https://unpkg.com/', $path);
		$break = true;
	}
});