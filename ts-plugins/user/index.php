<?php 
/**
 * Система пользователей, их регистрация и авторизация
 *
 * @hooks https://github.com/TsSaltan/ts-framework/wiki/Hooks#user
 * @hooks https://github.com/TsSaltan/ts-framework/wiki/Hooks#dashboarduser
 * 
 */
namespace tsframe;

use PHPMailer\PHPMailer\PHPMailer;
use tsframe\Config;
use tsframe\Http;
use tsframe\controller\Dashboard;
use tsframe\module\Logger;
use tsframe\module\Mailer;
use tsframe\module\io\Input;
use tsframe\module\menu\Menu;
use tsframe\module\menu\MenuItem;
use tsframe\module\user\SingleUser;
use tsframe\module\user\User;
use tsframe\module\user\UserAccess;
use tsframe\module\user\UserConfig;
use tsframe\view\Template;
use tsframe\view\TemplateRoot;

/**
 * Загрузка плагина
 */
Hook::registerOnce('plugin.load', function(){
	TemplateRoot::addDefault(__DIR__ . DS . 'template');	
	TemplateRoot::add('dashboard', __DIR__ . DS . 'template' . DS . 'dashboard');

	Input::addFilter('login', function($input){
		$input->required();
		$input->regexp('#[A-Za-z0-9-_\.]+#Ui');
		return true;
	});

	Input::addFilter('password', function($input){
		$input->required();
		$input->minLength(1);
		return true;
	});
});

/**
 * Менюшка вверху
 */
Hook::register('menu.render.dashboard-top', function(MenuItem $menu){
	$menu->add(new MenuItem('Мой профиль', ['url' => Http::makeURI('/dashboard/user/me'), 'fa' => 'user', 'access' => UserAccess::getAccess('user.self')]), 0);
	$menu->add(new MenuItem('Настройки профиля', ['url' => Http::makeURI('/dashboard/user/me/edit'), 'fa' => 'gear', 'access' => UserAccess::getAccess('user.self')]), 1);
});

/**
 * Меню сбоку
 */
Hook::register('menu.render.dashboard-admin-sidebar', function(MenuItem $menu){
	$menu->add(new MenuItem('Список пользователей', ['url' => Http::makeURI('/dashboard/user/list'), 'fa' => 'users', 'access' => UserAccess::getAccess('user.list')]), 1);
});

/**
 * В шаблон будут добавлены переменные с инфо о пользователе
 */
Hook::register('template.render', function($tpl){
	$user = User::current();
	$tpl->vars([
		'user' => $user,
		'login' => $user->get('login'),
		'email' => $user->get('email'),
		'access' => $user->get('access'),
		'accessList' => UserAccess::getArray(),
	]);
});

/**
 * Сохраняем права для пользователей после установки скрипта
 */
Hook::registerOnce('plugin.install', function(){
	Plugins::required('cache', 'database', 'mailer');
	return [
		PluginInstaller::withKey('access.user.onRegister')
					->setType('select')
					->setDescription("Права доступа: права, назначаемые пользователю при регистрации")
					->setDefaultValue(1)
					->setValues(array_flip(UserAccess::getArray())),

		PluginInstaller::withKey('access.user.self')
					->setType('select')
					->setDescription("Права доступа: редактирование собственного профиля")
					->setDefaultValue(1)
					->setValues(array_flip(UserAccess::getArray())),

		PluginInstaller::withKey('access.user.view')
					->setType('select')
					->setDescription("Права доступа: просмотр профилей пользователей")
					->setDefaultValue(1)
					->setValues(array_flip(UserAccess::getArray())),

		PluginInstaller::withKey('access.user.list')
					->setType('select')
					->setDescription("Права доступа: просмотр списка всех пользователей")
					->setDefaultValue(2)
					->setValues(array_flip(UserAccess::getArray())),

		PluginInstaller::withKey('access.user.edit')
					->setType('select')
					->setDescription("Права доступа: редактирование пользователей")
					->setDefaultValue(2)
					->setValues(array_flip(UserAccess::getArray())),

		PluginInstaller::withKey('access.user.delete')
					->setType('select')
					->setDescription("Права доступа: удаление пользователей")
					->setDefaultValue(4)
					->setValues(array_flip(UserAccess::getArray())),

		PluginInstaller::withKey('access.user.editAccess')
					->setType('select')
					->setDescription("Права доступа: изменение уровня доступа пользователей")
					->setDefaultValue(4)
					->setValues(array_flip(UserAccess::getArray())),

		PluginInstaller::withKey('access.user.editConfig')
					->setType('select')
					->setDescription("Права доступа: редактирование системных настроек")
					->setDefaultValue(4)
					->setValues(array_flip(UserAccess::getArray())),
	];
});

/**
 * После установки приложения создадим учётку администратора
 */
Hook::registerOnce('app.install', function(){
	if(!User::exists(['access' => UserAccess::Admin])){
		$password = $login = 'admin';
		$mail = 'change@admin.mail';
		User::register($login, $mail, $password, UserAccess::Admin);
	}
});

/**
 * Информация о пользователе на странице профиля в админ-панели
 */
Hook::register('template.dashboard.user.profile', function(Template $tpl, SingleUser $user){
	?>
	<p>User ID: <b><?=$user->get('id')?></b></p>
	<?php 
});

/**
 * Настройки регистрации на странице конфигов
 */
Hook::register('template.dashboard.config', function(Template $tpl){
	$tpl->var('registerEnabled', UserConfig::isRegisterEnabled());
	$tpl->var('socialEnabled', UserConfig::isSocialEnabled());
	$tpl->var('passwordEnabled', UserConfig::isPasswordEnabled());
	$tpl->var('loginEnabled', UserConfig::isLoginEnabled());
	$tpl->var('emailOnRegister', UserConfig::isEmailOnRegister());
	$tpl->var('loginOnRegister', UserConfig::isLoginOnRegister());
	$tpl->var('accesses', Config::get('access'));
	$tpl->inc('user_config');
});

/**
 * Отправка e-mail зарегистрированным пользователям
 */
Hook::register('user.register', function(SingleUser $user){
	if(UserConfig::isEmailOnRegister()){
		$mail = new Mailer;
		$mail->addAddress($user->get('email'));
		$mail->isHTML(true);  // Set email format to HTML
    	$mail->Subject = "Данные авторизации " . $_SERVER['HTTP_HOST'];
    	$link = Http::makeURI('/dashboard/login');
    	$message = "<p>Ссылка для авторизации: <a href='$link'>$link</a></p>";

    	if(UserConfig::isLoginEnabled()){
			$message .= "<p>Имя пользователя: <b>" . $user->get('login') . "</b></p>";
		}
		else $message .= "<p>E-mail: <b>" . $user->get('email') . "</b></p>";

    	if(UserConfig::isPasswordEnabled()){
			$message .= "<p>Пароль: <i>указанный вами пароль при регистрации</i></p>";
		} else {
			$message .= "<p>Пароль: <b>" . $user->get('password') . "</b></p>";
		}

    	$mail->Body = $message;
    	$mail->send();
	}
}, Hook::MIN_PRIORITY);


/**
 * Логирование регистрации пользователей
 */
Hook::register('user.register', function(SingleUser $user){
	(new Logger('user-registration'))->info('User "'. $user->get('login') .'" was registered', [
		'id' => $user->get('id'), 
		'email' => $user->get('email'),
	]);
});