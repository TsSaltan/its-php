<?php 
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
use tsframe\module\io\Input;
use tsframe\module\menu\Menu;
use tsframe\module\menu\MenuItem;
use tsframe\module\user\User;
use tsframe\module\user\UserConfig;
use tsframe\module\user\SingleUser;
use tsframe\module\user\UserAccess;
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

Hook::registerOnce('plugin.install', function(){
	Plugins::required('cache', 'crypto', 'database', 'mailer');
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


Hook::register('template.dashboard.user.profile', function(Template $tpl, SingleUser $user){
	?>
	<p>User ID: <b><?=$user->get('id')?></b></p>
	<?php 
});

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