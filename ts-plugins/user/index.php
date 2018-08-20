<?
/**
 * Система пользователей + авторизация
 *
 * @hook 'template.dashboard.user.edit' (Template $tpl, array &$configTabs = ['tabName' => 'tabContent'], int &$activeTab)
 * @hook 'template.dashboard.user.profile' (Template $tpl, SingleUser $user)
 * @hook 'user.login' (SingleUser $user)
 * @hook 'user.register' (SingleUser $user)
 */
namespace tsframe;

use tsframe\Config;
use tsframe\module\menu\Menu;
use tsframe\module\menu\MenuItem;
use tsframe\module\user\User;
use tsframe\module\user\SingleUser;
use tsframe\module\user\UserAccess;
use tsframe\module\user\SocialLogin;
use tsframe\view\Template;
use tsframe\view\TemplateRoot;

/**
 * Загрузка плагина
 */
Hook::registerOnce('plugin.load', function(){
	Plugins::required('database', 'crypto');

	TemplateRoot::addDefault(__DIR__ . DS . 'template');	
	TemplateRoot::add('dashboard', __DIR__ . DS . 'template' . DS . 'dashboard');
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
Hook::register('menu.render.dashboard-sidebar', function(MenuItem $menu){
	$menu->add(new MenuItem('Список пользователей', ['url' => Http::makeURI('/dashboard/user/list'), 'fa' => 'users', 'access' => UserAccess::getAccess('user.list')]), -2);
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
		'socialLogin' => SocialLogin::getWidgetCode(),
		'accessList' => UserAccess::getArray(),
	]);
});

/**
 * После установки приложения создадим учётку администратора
 */
Hook::registerOnce('app.install', function(){
	if(!User::exists(['access' => UserAccess::Admin])){
		$login = 'admin';
		$mail = 'change@admin.mail';
		$password = uniqid('pwd');
		User::register($login, $mail, $password, UserAccess::Admin);
		Log::add('New admin profile:', [
			'login' => $login,
			'mail' => $mail,
			'password' => $password,
		]);
	}

	if(Config::get('access') == null){
		Config::set('access.user.onRegister', 1); 	// Права доступа при регистрации
		Config::set('access.user.self', 1); 		// Изменение собственного профиля
		Config::set('access.user.view', 1); 		// Просмотр профиля пользователей
		Config::set('access.user.list', 2); 		// Просмотр списка пользователей
		Config::set('access.user.edit', 2); 		// Редактирование пользователей
		Config::set('access.user.delete', 4); 		// Редактирование пользователей
		Config::set('access.user.editAccess', 4);	// Редактирование уровня доступа
		Config::set('access.user.editConfig', 4);	// Редактирование системных настроек
	}
});


Hook::register('template.dashboard.user.profile', function(Template $tpl, SingleUser $user){
	?>
	<p>User ID: <b><?=$user->get('id')?></b></p>
	<?
});