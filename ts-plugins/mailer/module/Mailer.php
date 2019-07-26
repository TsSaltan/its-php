<?php
namespace tsframe\module;

use PHPMailer\PHPMailer\PHPMailer;
use tsframe\App;
use tsframe\Config;

class Mailer extends PHPMailer {
	/**
	 * @override
	 */
	public function __construct($exceptions = null){
		parent::__construct($exceptions);

		$this->Timelimit = 30;
		$this->SMTPDebug = /*App::isDev() ? 2 :*/ 0;  

		if(Config::isset('mailer.host')){
			$this->Host = Config::get('mailer.host');
		}

		if(Config::isset('mailer.port')){
			$this->Port = Config::get('mailer.port');
		}

		if(Config::isset('mailer.email') && Config::isset('mailer.password')){
    		$this->isSMTP();
    		$this->SMTPAuth = true;

    		$this->Username   = Config::get('mailer.email');                  
    		$this->setFrom(Config::get('mailer.email'));                  

    		$this->Password   = Config::get('mailer.password');
		}

		if(Config::isset('mailer.secure') && (Config::get('mailer.secure') != 'none')){
    		$this->SMTPSecure = Config::get('mailer.secure');
		}
	}
}