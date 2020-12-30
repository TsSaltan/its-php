<?php
namespace tsframe\module;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use tsframe\App;
use tsframe\Config;
use tsframe\module\Logger;

/**
 * Класс для отправки почты
 * @example $mail = new Mailer;
 *			$mail->addAddress('email', 'name');
 *			$mail->isHTML(true);  // Set email format to HTML
 *    		$mail->Subject = $subl;
 *    		$mail->Body = $message;
 *    		$mail->send();
 */
class Mailer extends PHPMailer {
	/**
	 * Автоматическая установка параметров для отправки из конфигурационного файла
	 * @override
	 */
	public function __construct($exceptions = null){
		parent::__construct($exceptions);

		$this->Timelimit = 30;
		$this->CharSet = 'utf-8';
		$this->SMTPDebug = 0;  

		if(Config::isset('mailer.host')){
			$this->Host = Config::get('mailer.host');
		}

		if(Config::isset('mailer.port')){
			$this->Port = Config::get('mailer.port');
		}


		$meta = new Meta('dashboard');
		$sitename = $meta->get('sitename');
		if(Config::isset('mailer.from')){
			$this->setFrom(Config::get('mailer.from'), $sitename);   	
		}
		elseif(Config::isset('mailer.email')){
			$this->setFrom(Config::get('mailer.email'), $sitename);   	
		}else{
			$this->setFrom('admin@'.$_SERVER['SERVER_NAME'], $sitename);  
		}

		if(Config::isset('mailer.email') && Config::isset('mailer.password')){
    		$this->isSMTP();
    		$this->SMTPAuth = true;
    		$this->Username   = Config::get('mailer.email');                                 
    		$this->Password   = Config::get('mailer.password');
		}

		if(Config::isset('mailer.secure') && (Config::get('mailer.secure') != 'none')){
    		$this->SMTPSecure = Config::get('mailer.secure');
		}
	}

	/**
	 * Логирование отправляемых писем
	 * @override
	 */
	public function send(){
		Logger::mail()->debug('Sending email #' . $this->MessageID, [
			'From' => $this->From . " (" . $this->FromName . ")",
			'To' => $this->all_recipients,
			'Subject' => $this->Subject,
			'Body' => $this->Body,
			'AltBody' => $this->AltBody,
			'Mailer_method' => $this->Mailer,
			'Host' => $this->Host,
			'Port' => $this->Port,
			'SMTPAuth' => $this->SMTPAuth ? 'true' : 'false',
			'Username' => $this->Username,
			'Password_length' => strlen($this->Password),
		]);

		if(parent::send()){
			Logger::mail()->info('Mail #' . $this->MessageID . ' successfully send');
		} else {
			Logger::mail()->error('Error on sending #' . $this->MessageID, [
				'error' => $this->ErrorInfo
			]);
		}
	}
}