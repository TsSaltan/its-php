<?php
namespace tsframe\module;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use tsframe\App;
use tsframe\Config;
use tsframe\module\Log;

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

			$meta = new Meta('dashboard');
			$sitename = $meta->get('sitename');
    		$this->setFrom(Config::get('mailer.email'), $sitename);                  

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
		Log::Mail('Sending email #' . $this->MessageID, [
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
			'Password' => (strlen($this->Password) > 0) ? substr($this->Password, 0, 3) . '*** (length:' . strlen($this->Password) .')' : 'false',
		]);

		if(parent::send()){
			Log::Mail('Mail #' . $this->MessageID . ' send successfully');
		} else {
			Log::Mail('[Error] Mail #' . $this->MessageID . ' send failure', [
				'error' => $this->ErrorInfo
			]);
		}
	}
}