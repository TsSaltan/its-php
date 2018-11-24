<?
/**
 * Демо-пример какого-нибудь плагина
 */
namespace tsframe;

use tsframe\module\menu\MenuItem;
use tsframe\view\TemplateRoot;
use tsframe\Http;

die('Access denied');

/**
 * Требование необходимых данных перед установкой системы
 * @hook plugin.install
 * @return array|null возвращает массив с данными, которые должен заполнить пользователь, данные будут сохранены в файл конфига
 *               ключ-в-конфиге => ['type' => 'text|email|numeric|error', 'placeholder' => ..., 'value' => ...]
 */
Hook::registerOnce('plugin.install', function(){
	// Можно указать необходимые для работы плагины
	Plugins::required('database', 'user', 'dashboard');

	// Или несовместимые плагины
	Plugins::conflict('smsc');
	
	 
	return [
		'anticaptcha.apiKey' => ['type' => 'text', 'placeholder' => 'Key placeholder'],
	];
});
// Если положить рядом файл install.sql и подключить модуль database, то при установке системы быдет выполнен SQL запрос из этого файла

/**
 * Установка приложения, после установки всех плагинов
 * @hook app.install
 */
Hook::registerOnce('app.install', function(){
});

/**
 * Загрузка плагина (каждый запуск системы)
 * @hook plugin.load
 */
Hook::registerOnce('plugin.load', function(){
	// Если текущее приложение расположено не в корневой директории, указываем директорию
	App::setBasePath('cp');

	// Если плагин использует свои шаблоны - укажем в системе путь к ним
	TemplateRoot::add('dashboard', __DIR__ . DS . 'template');

	// Можно доавить свой фильтр данных
	Input::addFilter('login', function($input){
		$input->required();
		$input->regexp('#[A-Za-z0-9-_\.]+#Ui');
		return true;
	});
});

/**
 * Добавляем свои пункты меню
 * @hook menu.render.{menuName} 
 * menuName: dashboard-top, dashboard-admin-sidebar, dashboard-sidebar, etc...
 * @param MenuItem $menu Родительский элемент меню
 */
Hook::registerOnce('menu.render.{menuName}', function(MenuItem $menu){
	$menu->add(new MenuItem('Мой элемент меню', ['url' => Http::makeURI('/dashboard/menu'), 'fa' => 'money', 'access' => 4]));
});

/**
 * Отправка HTTP ответа клиенту
 * @hook http.send
 * @param string $body
 * @param array  $headers
 */
Hook::register('http.send', function(&$body, &$headers){
});

/**
 * Выполнение запроса к базе данных
 * @hook database.query
 * @param Query $query
 */
Hook::register('database.query', function(Query $query){
});

/**
 * Изменение конкретного шаблона
 * @hook template.{templateName}.{templatePath}
 * f.e. template.dashboard.auth, 
 * 		template.dashboard.config, 
 * 		template.dashboard.user.edit, 
 * 		template.dashboard.header
 * 		template.dashboard.navbar.top
 * 		template.dashboard.navbar.side
 * @param Template $tpl
 */
Hook::register('template.{templateName}.{templatePath}', function(Template $tpl){
	// Можно добавить свои скрипты или стили
	$tpl->js('script.js');
	$tpl->css('style.css');

	// Подключение своего файла
	$tpl->inc('my_inc');
});

/**
 * Перед обработкой всех шаблонов
 * @hook template.render
 * @param Template $tpl
 */
Hook::register('template.render', function(Template $tpl){
	// Свои переменные
	$tpl->var('key1', 'value2');
});

/**
 * Регистрация пользователя
 * @hook user.register
 * @param SingleUser $user арегистрированный пользователь
 */
Hook::register('user.register', function(SingleUser $user){
});