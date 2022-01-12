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

		// Params for SMTP connection
		if(Config::isset('mailer.sender') && strtolower(Config::get('mailer.sender')) == 'smtp'){
			$this->isSMTP();
	   		$this->SMTPAuth = true;
	    	$this->Username = Config::get('mailer.from.email');                                 
	    	$this->Password = Config::get('mailer.smtp.password');

	    	if(Config::isset('mailer.smtp.secure') && (Config::get('mailer.smtp.secure') != 'none')){
    			$this->SMTPSecure = Config::get('mailer.smtp.secure');
			}
		}

		$fromEmail = 'admin@'.$_SERVER['SERVER_NAME'];
		$fromName = $sitename;
		
		if(Config::isset('mailer.from.email') && strlen(Config::get('mailer.from.email')) > 0) {
			$fromEmail = Config::get('mailer.from.email');
		}
				
		if(Config::isset('mailer.from.name') && strlen(Config::get('mailer.from.name')) > 0) {
			$fromName = Config::get('mailer.from.name');
		}

		$this->setFrom($fromEmail, $fromName);  
	}

	/**
	 * Логирование отправляемых писем
	 * @override
	 */
	public function send(){
		Logger::mail()->debug('Sending email #' . $this->uniqueid, [
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
			Logger::mail()->info('Mail #' . $this->uniqueid . ' successfully send');
		} else {
			Logger::mail()->error('Mail sending error #' . $this->uniqueid, [
				'error' => $this->ErrorInfo
			]);
		}
	}
}