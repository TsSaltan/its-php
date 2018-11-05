<?
/**
 * Дебаг 
 */
namespace tsframe;

use tsframe\App;
use tsframe\Config;
use tsframe\module\Debugger;

if(App::isDev()){

	$debug = new Debugger;

	Hook::register('http.send', function(&$body, &$headers) use ($debug){
		$data = $debug->getData();
		foreach ($data as $key => $value) {
			$headers['X-Debug-' . $key] = $value;
		}
	});

	Hook::register('database.query', function($sth) use ($debug){
		$debug->dbQuery(str_replace("\n", '; ', $sth->getDebug()));
		//$debug->addCounter('Database-Query');
	});

}