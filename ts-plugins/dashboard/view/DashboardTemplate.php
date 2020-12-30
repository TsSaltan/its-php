<?php
namespace tsframe\view;

use tsframe\module\DashboardDesigner;
use tsframe\module\Meta;
use tsframe\module\menu\Menu;
use tsframe\view\UI\UIDashboardPanel;
use tsframe\view\UI\UIDashboardTabPanel;

class DashboardTemplate extends HtmlTemplate {
	/**
	 * @var DashboardDesigner
	 */
	protected $designer;

	public function __construct(string $part, string $name){
		parent::__construct($part, $name);
		$this->designer = new DashboardDesigner;
	}

	/**
	 * Добавить уведомление
	 * @param  string $message [description]
	 * @param  string $type    info|danger|warning|error|success
	 */
	public function alert(string $message, string $type = 'info'){
		$this->vars['alert'][$type][] = $message;
	}

	public function getDesigner(): DashboardDesigner {
		return $this->designer;
	}

	public function themeCSS(){
		$theme = $this->designer->getCurrentTheme();
		if(!is_null($theme)){
			$this->css("themes/" . $theme . ".css");
		}
	}

	// UI elements
	
	/**
	 * Отображение навигационного меню
	 * @param  bool|boolean $top  Показать верхний бар
	 * @param  bool|boolean $side Показать боковой бар
	 */
	public function uiNavbar(bool $top = true, bool $side = true){
	    ?>
	    <!-- Navigation -->
	    <nav class="navbar navbar-inverse navbar-fixed-top" id="navbar-top" role="navigation">
	        <?php if($top)  $this->incNavtop()?>
	        <?php if($side) $this->incNavside()?>
	    </nav>
	    <?php
	}

	/**
	 * Показать бар с уведомлением
	 * @param string $message Текст сообщения
	 * @param string $type=info|danger|warning|error|success
	 */
	public function uiAlert(string $message = null, string $type = 'info', bool $closable = true){
	    $view = is_null($message) ? 'hidden' : '';
	    ?>
	    <div class="alert alert-<?=$type?> <?=$view?>">
	        <?php if($closable): ?><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><?php endif ?>
	        <p class='text'><?=$message?></p>
	    </div>
	    <?php
	}


	/**
	 * Показать уведомления, установленные контроллером ($this->alert)
	 * или массив уведомлений
	 * @param  array|null $alerts [type=>[message1, message2], type2=>message3...]
	 */
	public function uiAlerts(array $alerts = []){
	    if(sizeof($alerts) == 0){
	        if(!is_array($this->alert)) return;
	        $alerts = $this->alert;
	    }

	    foreach($alerts as $type => $messages){
	        $messages = is_array($messages) ? $messages : [$messages];
	        foreach ($messages as $message) {
	            $this->uiAlert($message, $type);
	        }
	    }
	}

	/**
	 * Отобразить json редактор
	 * @param  array       $data      Массив с данными
	 * @param  string      $fieldName Имя поля в редакторе
	 * @param  int|integer $rows      Максимальное количество строк
	 */
	public function uiJsonEditor(array $data, string $fieldName, int $rows = 10){
	    $fieldID = 'json-' . $fieldName;
	    ?>
	    <!-- JSON Editor -->
	    <ul class="nav nav-tabs">
	        <li class="active"><a href="#<?=$fieldID?>-visual" data-toggle="tab">Визуальный редактор</a></li>
	        <li><a href="#<?=$fieldID?>-plain" data-toggle="tab">Исходный код</a></li>

	    </ul>

	    <div class="tab-content">
	        <div class="tab-pane fade in active" id="<?=$fieldID?>-visual">
	            <div id="<?=$fieldID?>-editor" class='json-editor'></div>
	        </div>
	        <div class="tab-pane fade" id="<?=$fieldID?>-plain">
	            <div class="form-group" id="<?=$fieldID?>-form-group">
	                <textarea name="<?=$fieldName?>" class="form-control" id="<?=$fieldID?>-textarea" rows="<?=$rows?>"><?=json_encode($data, JSON_PRETTY_PRINT)?></textarea>
	            </div>
	        </div>
	    </div>

	    <script type="text/javascript">   
	        $(function(){
	            var $formGroup = $(<?=json_encode('#' . $fieldID . '-form-group')?>);
	            var $textarea = $(<?=json_encode('#' . $fieldID . '-textarea')?>);
	            var jeditor, data = <?=json_encode($data)?>;
	            var container = document.getElementById(<?=json_encode($fieldID . '-editor')?>);
	            var options = {
	                onChange: function(){
	                    var sourceJson = jeditor.get();
	                    $textarea.val(JSON.stringify(sourceJson, null, 2));
	                }
	            };
	            
	            jeditor = new JSONEditor(container, options);
	            jeditor.set(data);

	            // Обработчик редактора исходного JSON кода
	            $textarea.change(function() {
	                var val = $textarea.val();

	                if (val) {
	                    try { 
	                        json = JSON.parse(val); 
	                        $formGroup.removeClass('has-error');
	                        jeditor.set(json);
	                    }
	                    catch (e) { 
	                        console.log('[Parse Error] Incorrect JSON syntax: ' + e); 
	                        $formGroup.addClass('has-error');
	                    }
	                } else {
	                    jeditor.set({});
	                }
	            });
	        });
	    </script>
	    <?php
	}

	/**
	 * Отобразить страницы пагинатора
	 * @param  Paginator  $paginator    
	 * @param  string $btnClass
	 */
	public function uiPaginatorNav($paginator, string $btnClass = "btn-primary"){
	    foreach($paginator->getPages() as $page){
	        ?><a class="btn <?=$btnClass?> <?=($page['current'] ? "disabled" : "btn-outline")?>" href="<?=$page['url']?>"><?=$page['title']?></a> <?php
	    }
	}

	/**
	 * Отобразить колчество элементов на странице
	 * @param  Paginator $paginator 
	 * @return null      Сразу выводит HTML-контент  
	 */
	public function uiPaginatorCount($paginator){
	    ?>
	    <form action="" method="GET">
	        <div class="input-group">
	            <span class="input-group-addon">Элементов на странице</span>
	            <select class="form-control" name='count' onchange="this.parentElement.parentElement.submit()">
	                <option value="<?=$paginator->getItemsNum()?>" selected style="display: none"><?=$paginator->getItemsNum()?></option>
	                <option value="5">5</option>
	                <option value="10">10</option>
	                <option value="20">20</option>
	                <option value="50">50</option>
	                <option value="100">100</option>
	            </select>
	        </div>
	    </form>
	    <?php
	}

	/**
	 * Отобразить футер для пагинатора (ссылки на страницы + выбор колчества элементов)
	 * @param  Paginator $paginator 
	 * @return null      Сразу выводит HTML-контент  
	 */
	public function uiPaginatorFooter($paginator){
	    ?><div class="row">
	        <div class="col-lg-6">
	            <?php $this->uiPaginatorNav($paginator)?>
	        </div>

	        <div class="col-lg-6 pull-right">
	            <?php $this->uiPaginatorCount($paginator)?>
	        </div>
	    </div><?php
	}

	/**
	 * Отобразить панель
	 * @param  string $panelClass
	 * @return UIDashboardPanel
	 */
	public function uiPanel(?string $panelClass = 'panel-default'): UIDashboardPanel {
		return new UIDashboardPanel($panelClass);
	}

	/**
	 * Отобразить панель с табами
	 * @param  string $panelClass
	 * @return UIDashboardPanel
	 */
	public function uiTabPanel(?string $panelClass = 'panel-default'): UIDashboardTabPanel {
		return new UIDashboardTabPanel($panelClass);
	}
}