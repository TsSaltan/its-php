<?php
/**
 * Демо-пример какого-нибудь плагина
 */
namespace tsframe;

use tsframe\Http;
use tsframe\module\io\Input;
use tsframe\module\menu\MenuItem;
use tsframe\view\TemplateRoot;

die('Access denied');


/**
 * API Возвращает данные о пользователе
 * @hook api.user.data
 * @param SingleUser $user Пользователь, чьи данные нужно вернуть
 * @param array $data Массив данных, которые будут возвращены
 * @return array|null Дополнительные поля, которые необходимо вернуть
 */
Hook::registerOnce('api.user.data', function(SingleUser $user, array &$data){
	// Можно вернуть массив с дополнительными полями
	return ['balance' => $user->getBalance()];

	// Или добавить поле в мессив $data
	$data['balance'] = $user->getBalance();
});

/**
 * Если среди определенных контроллеров не найден подходящий
 * @hook router
 * @param string $method HTTP-метод GET|POST|etc...
 * @param string $uri Путь от роутера
 * @return AbstractController|mixed Если вернуть контроллер, то ошибки не будет
 */
Hook::registerOnce('router', function(string $method, string $uri){

});

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
 * @hook app.installed
 */
Hook::registerOnce('app.installed', function(){
});

/**
 * Начало работы приложения (после инициализации плагинов)
 * @hook app.init
 */
Hook::registerOnce('app.init', function(){
});

/**
 * Завершение работы приложения
 * @hook app.finish
 */
Hook::registerOnce('app.finish', function(){
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
 *
 * Шаблон Dashboard
 *   template.dashboard.config (Template $tpl) 
 *   template.dashboard.header (Template $tpl)
 *   template.dashboard.navbar.top (Template $tpl)
 *   template.dashboard.navbar.side (Template $tpl)
 *   
 *   Страница авторизации
 *   - Вкладки авторизация/регистрация
 *     template.dashboard.auth (Template $tpl, array $authTabs [login =>..., register => ...]), 
 *     
 *   - Внутри вкладки авторизация (поля)
 *     template.dashboard.auth.login (Template $tpl) 
 *     
 *   - Внутри вкладки регистрация (поля)
 *     template.dashboard.auth.register (Template $tpl) 
 *
 *   Страницы User
 *   - Редактирование пользователя
 *     template.dashboard.user.edit (Template $tpl, UIDashboardTabPanel $configTabs)
 *     
 *   - Редактирование пользователя: вкладка с балансом
 *     template.dashboard.user.edit.balance (Template $tpl, SingleUser $selectUser)
 *     
 *   - Профиль пользователя
 *     template.dashboard.user.profile (Template $tpl, SingleUser $user)
 *
 *   - Список пользователей в админке
 *   - - Столбцы в таблице (внутри <tr>):
 *       template.dashboard.user.list.column (Template $tpl) 					  
 *   - - Строка с пользователем (внутри <tr>)
 *       template.dashboard.user.list.item (Template $tpl, SingleUser $user) 	
 * 		
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
 * Импорт файла в шаблон
 * @hook template.include
 * @param Template $tpl
 * @param string $name Имя файла
 */
Hook::register('template.include', function(string $name, Template $tpl){
});

/**
 * Перед регистрацией пользователя (dвызывается из контроллера UserAJAX)
 * @hook user.register.controller
 * @param array $data Данные, отправленные пользователем во время регистрации
 * @param Input $input Обработчик входящих данных
 * @return bool Если хук возвращает false (или во время выполнения произошла ошибка), регистрация пользователя будет отменена
 */
Hook::register('user.register.controller', function(array $data, Input $input){
});

/**
 * Регистрация пользователя
 * @hook user.register
 * @param SingleUser $user арегистрированный пользователь
 */
Hook::register('user.register', function(SingleUser $user){
});


/**
 * Запуск планировщика
 * @param Task[] $tasks
 */
Hook::register('scheduler.start', function(array $tasks){
	
});

/**
 * Выполнение задачи планировщика
 * @param Task $task
 * @return false, если задача не выполнеина
 */
Hook::register('scheduler.task.%taskName%', function(Task $task){

});