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
use tsframe\module\io\Input;
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
	Plugins::required('cache', 'crypto', 'database');

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
		'socialLogin' => SocialLogin::getWidgetCode(),
		'accessList' => UserAccess::getArray(),
	]);
});

Hook::registerOnce('plugin.install.required', function(){
	return [
		'access.user.onRegister' => ['type' => 'numeric', 'value' => 1, 'title' => 'Права доступа при регистрации'],
		'access.user.self' => ['type' => 'numeric', 'value' => 1, 'title' => 'Изменение собственного профиля'],
		'access.user.view' => ['type' => 'numeric', 'value' => 1, 'title' => 'Просмотр профиля пользователей'],
		'access.user.list' => ['type' => 'numeric', 'value' => 2, 'title' => 'Просмотр списка пользователей'],
		'access.user.edit' => ['type' => 'numeric', 'value' => 2, 'title' => 'Редактирование пользователей'],
		'access.user.delete' => ['type' => 'numeric', 'value' => 4, 'title' => 'Редактирование пользователей'],
		'access.user.editAccess' => ['type' => 'numeric', 'value' => 4, 'title' => 'Редактирование уровня доступа'],
		'access.user.editConfig' => ['type' => 'numeric', 'value' => 4, 'title' => 'Редактирование системных настроек'],
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
	<?
});