<?php
namespace tsframe\module\user;

use tsframe\module\database\Database;
use tsframe\module\database\Query;
use tsframe\exception\AccessException;
use tsframe\module\Meta;
use tsframe\Http;

/**
 * @link https://ulogin.ru
 */
class SocialLogin{
	protected static $callbackURI = '/dashboard/social-login';

	/**
	 * @link https://ulogin.ru/help.php#fields
	 * @var array
	 */
	protected static $fields = ['email', 'first_name', 'last_name', 'nickname'];

	protected static $providers = ['vkontakte','facebook','odnoklassniki'];

	public static function getWidgetCode() : string {
		$url = urlencode(Http::makeURI(self::$callbackURI));
		$fields = implode(',', self::$fields);
		$providers = implode(',', self::$providers);

		return <<<HTML
		<script src="//ulogin.ru/js/ulogin.js"></script>
		<div id="uLogin" data-ulogin="display=panel;theme=classic;fields=$fields;providers=$providers;hidden=other;redirect_uri=$url;mobilebuttons=0;"></div>
HTML;
	}

	public static function getUserAccounts(SingleUser $user): array {
		$meta = new Meta('user', $user->get('id'));
		$accs = [];
		foreach($meta->getData() as $key => $value){
			if(substr($key, 0, 7) == 'social_' && strlen($value) > 1){
				$accs[substr($key, 7)] = $value;
			}
		}

		return $accs;
	}

	protected $token;
	protected $data = [];

	public function __construct(string $token){
		$this->token = $token;
		$this->loadData();
	}

	protected function loadData(){
		$url = 'http://ulogin.ru/token.php?token=' . $this->token . '&host=' . $_SERVER['HTTP_HOST'];
		$data = file_get_contents($url);
		return $this->data = json_decode($data, true);
	}

	public function getData(): array {
		return $this->data;
	}

	public function getUser(): SingleUser {
		// 1. Поиск по сохранённому ранее meta
		$meta = Meta::find('social_' . $this->data['network'], $this->data['identity']);
		foreach ($meta as $m) {
			$parent = $m->getParent();
			$users = User::get(['id' => str_replace('user_', '', $parent)]);
			foreach ($users as $user){
				return $user;
			}
		}

		// 2. Поиск по e-mail
		/*if(isset($this->data['email'])){
			$users = User::get(['email' => $this->data['email']]);
			if(isset($users[0])){
				//$this->saveUserMeta($users[0]);
				return $users[0];
			}
		}*/

		// 3. Создание нового профиля
		$nicknames = [];
		if(isset($this->data['nickname'])){
			$nicknames[] = $this->data['nickname'];
		}

		if(isset($this->data['first_name']) && isset($this->data['last_name'])){
			$nicknames[] = $this->data['first_name'] . '_' . $this->data['last_name'];
		}

		if(isset($this->data['email'])){
			$nicknames[] = explode('@', $this->data['email'])[0];
		}

		$nicknames[] = uniqid($this->data['network'].'_');

		$exists = true;
		$nickname = null;
		
		while($exists){
			$nickname = current($nicknames);
			next($nicknames);

			$exists = User::exists(['login' => $nickname]);
		}

		$email = $this->data['email'] ?? $this->data['identity'] . '@' . $this->data['network'];
		$password = uniqid('pass_');
		$user = User::register($nickname, $email, $password);
		$meta = $this->saveUserMeta($user);
		$meta->set('temp_password', $password);

		return $user;
	}

	/**
	 * Format: social_*network* => *id*
	 */
	public function saveUserMeta(SingleUser $user): Meta {
		$meta = new Meta('user', $user->get('id'));
		$find = Meta::find('social_' . $this->data['network'], $this->data['identity']);

		if(sizeof($find) > 0){
			throw new AccessException('Social provider already use', 403, [
				'data' => $this->data
			]);
		}

		$meta->set('social_' . $this->data['network'], $this->data['identity']);
		return $meta;
	}
}