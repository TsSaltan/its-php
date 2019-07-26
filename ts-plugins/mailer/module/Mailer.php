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
		$this->SMTPDebug = App::isDev() ? 2 : 0;                                       // Enable verbose debug output

		if(Config::isset('mailer.host')){
			$this->Host = Config::get('mailer.host');
			var_dump(['Host' => Config::get('mailer.host')]);
		}

		if(Config::isset('mailer.port')){
			$this->Port = Config::get('mailer.port');
			var_dump(['Port' => Config::get('mailer.port')]);
		}

		if(Config::isset('mailer.email') && Config::isset('mailer.password')){
    		$this->isSMTP();                                            // Set mailer to use SMTP
    		$this->SMTPAuth   = true;                                   // Enable SMTP authentication

    		$this->Username   = Config::get('mailer.email');                  
    		$this->setFrom(Config::get('mailer.email'));                  

			var_dump(['Username' => Config::get('mailer.email')]);

    		$this->Password   = Config::get('mailer.password');
			var_dump(['Password' => Config::get('mailer.password')]);
		}

		if(Config::isset('mailer.secure') && (Config::get('mailer.secure') != 'none')){
    		$this->SMTPSecure = Config::get('mailer.secure');
			var_dump(['SMTPSecure' => Config::get('mailer.secure')]);
		}
	}
}