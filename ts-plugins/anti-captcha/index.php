<?php
/**
 * Плагин для работы с https://anti-captcha.com/
 * Github https://github.com/AdminAnticaptcha/anticaptcha-php
 */
namespace tsframe;

Hook::register('plugin.install', function(){
    return [
        PluginInstaller::withKey('anticaptcha.apiKey')
                       ->setDescription('Ключ для <a href="https://anti-captcha.com/" target="blank">AntiCaptcha API</a>')
                       ->setRequired(true)
    ];
});

/**
 * @example
 */
if(false){
	$api = new ImageToText();
	//$api->setVerboseMode(true);
	        
	//your anti-captcha.com account key
	//$api->setKey(readline("You API key: "));

	//setting file
	$api->setFile("capcha.jpg");

	if (!$api->createTask()) {
	    $api->debout("API v2 send failed - ".$api->getErrorMessage(), "red");
	    return false;
	}

	$taskId = $api->getTaskId();


	if (!$api->waitForResult()) {
	    $api->debout("could not solve captcha", "red");
	    $api->debout($api->getErrorMessage());
	} else {
	    $captchaText    =   $api->getTaskSolution();
	    echo "\nresult: $captchaText\n\n";
	}
}