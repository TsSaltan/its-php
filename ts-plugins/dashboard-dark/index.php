<?
/**
 */
namespace tsframe;

use tsframe\Hook;
use tsframe\Plugins;
use tsframe\view\TemplateRoot;

Hook::registerOnce('plugin.load', function(){
	Plugins::required('dashboard');

	TemplateRoot::addDefault(__DIR__ . DS . 'template');	
	TemplateRoot::add('dashboard', __DIR__ . DS . 'template' . DS . 'dashboard');	
});