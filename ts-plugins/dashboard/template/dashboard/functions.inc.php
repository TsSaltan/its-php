<?php
global $that;
$that = $this;

/**
 * Отображение навигационного меню
 * @param  bool|boolean $top  Показать верхний бар
 * @param  bool|boolean $side Показать боковой бар
 */
function uiNavbar(bool $top = true, bool $side = true){
    global $that;
    ?>
    <!-- Navigation -->
    <nav class="navbar navbar-inverse navbar-fixed-top" id="navbar-top" role="navigation">
        <?if($top)  $that->incNavtop()?>
        <?if($side) $that->incNavside()?>
    </nav>
    <?
}

/**
 * Показать уведомления, установленные контроллером ($this->alert)
 * или массив уведомлений
 * @param  array|null $alerts [type=>[message1, message2], type2=>message3...]
 */
function showAlerts(array $alerts = null){
    if(!is_array($alerts)){
        global $that;
        if(!is_array($that->alert)) return;
        $alerts = $that->alert;
    }

    foreach($alerts as $type => $messages){
        $messages = is_array($messages) ? $messages : [$messages];
        foreach ($messages as $message) {
            uiAlert($message, $type);
        }
    }
}

/**
 * Показать бар с уведомлением
 * @param string $message Текст сообщения
 * @param string $type=info|danger|warning|error|success
 */
function uiAlert(string $message = null, string $type='info'){
    $view = is_null($message) ? 'hidden' : '';
?>
    <div class="alert alert-<?=$type?> <?=$view?>">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <p class='text'><?=$message?></p>
    </div>
<?
}

/**
 * Подключить js-скрипты фреймворка
 */
function jsFrame(){
    global $that;
    $that->js('ts-client/frame.js', 'ts-client/user.js');
    ?><script type="text/javascript">tsFrame.basePath = <?=json_encode(\tsframe\App::getBasePath())?>;</script><?
}

/**
 * Отобразить json редактор
 * @param  array       $data      Массив с данными
 * @param  string      $fieldName Имя поля в редакторе
 * @param  int|integer $rows      Максимальное количество строк
 */
function uiJsonEditor(array $data, string $fieldName, int $rows = 10){
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
            var $textarea = $(<?=json_encode('#' . $fieldID . '-textarea')?>);
            var $editor = $(<?=json_encode('#' . $fieldID . '-editor')?>);
            var $formGroup = $(<?=json_encode('#' . $fieldID . '-form-group')?>);
            var data = <?=json_encode($data)?>;

            var editorParams = {
                // При изменении данных в редакторе - сохраняем исходный код в textarea
                change: function(input) { 
                    $textarea.val(JSON.stringify(input, null, 2));
                },
                valueElement: "<textarea>"
            };
            
            $editor.jsonEditor(data, editorParams);

            // И наоборот - при изменении данных в текстовом поле - меняем данные в визуальном редакторе
            $textarea.change(function() {
                var val = $textarea.val();

                if (val) {
                    try { 
                        json = JSON.parse(val); 
                        $formGroup.removeClass('has-error');
                    }
                    catch (e) { 
                        //alert('Error in parsing json. ' + e); 
                        $formGroup.addClass('has-error');
                    }
                } else {
                    json = {};
                }
                
                $editor.jsonEditor(json, editorParams);
            });
        });
    </script>
    <?
}

/**
 * Отобразить страницы пагинатора
 * @param  Paginator  $paginator    
 * @param  string $btnClass
 */
function uiPaginatorNav($paginator, string $btnClass = "btn-primary"){
    foreach($paginator->getPages() as $page){
        ?><a class="btn <?=$btnClass?> <?=($page['current'] ? "disabled" : "btn-outline")?>" href="<?=$page['url']?>"><?=$page['title']?></a> <?
    }
}

/**
 * Отобразить колчество элементов на странице
 * @param  Paginator $paginator 
 * @return null      Сразу выводит HTML-контент  
 */
function uiPaginatorCount($paginator){
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
    <?
}

/**
 * Отобразить футер для пагинатора (ссылки на страницы + выбор колчества элементов)
 * @param  Paginator $paginator 
 * @return null      Сразу выводит HTML-контент  
 */
function uiPaginatorFooter($paginator){
    ?><div class="row">
        <div class="col-lg-6">
            <?uiPaginatorNav($paginator)?>
        </div>

        <div class="col-lg-6 pull-right">
            <?uiPaginatorCount($paginator)?>
        </div>
    </div><?
}

/**
 * Отобразить сворачиваемую панель
 * @param  callable|string  $headerContent 
 * @param  callable|string  $bodyContent   
 * @param  callable|string  $footerContent 
 * @param  string           $panelClass    
 * @param  string|null      $icon          
 * @param  string|null      $paneId      
 * @return null             Сразу выводит HTML-контент  
 */
function uiCollapsePanel($headerContent, $bodyContent, $footerContent = null, string $panelClass = "panel-default", string $icon = null, string $paneId = null){
    $paneId = is_null($paneId) ? uniqid('panel') : $paneId ;
    ?>
    <div class="row">
        <div class="col-lg-12">
            <div class="panel panel-collapsable <?=$panelClass?>">
                <div class="panel-heading">
                    <h4 class="panel-title">
                        <a data-toggle="collapse" href="#<?=$paneId?>"><?if(!is_null($icon)):?><i class='fa fa-<?=$icon?>'></i>&nbsp;&nbsp;<?endif?><?=(is_callable($headerContent) ? call_user_func($headerContent) : $headerContent)?></a>
                    </h4>
                </div>
                <div id="<?=$paneId?>" class="panel-collapse collapse">
                    <div class="panel-body"><?=(is_callable($bodyContent) ? call_user_func($bodyContent) : $bodyContent)?></div>
                    <?if(!is_null($footerContent)):?>
                    <div class="panel-footer"><?=(is_callable($footerContent) ? call_user_func($footerContent) : $footerContent)?></div>
                    <?endif?>
                </div>
            </div>
        </div>
    </div><?
}

/**
 * Отобразить панель табов
 * @param  callable|string  $headerContent 
 * @param  array            $tabs           [id=>[title=>, content=>]]
 * @param  int|string|null  $activeTab
 * @param  string           $panelClass   
 */
function uiTabPanel($headerContent = null, array $tabs = [], $activeTab = null, ?string $panelClass = 'panel-default'){
    $hasHeader = is_callable($headerContent) || strlen($headerContent) > 0;
    ?>
    <div class="row">
        <div class="panel tabbed-panel <?=$panelClass?>">
            <div class="panel-heading clearfix">
                <?if($hasHeader):?>
                <div class="panel-title pull-left"><?=(is_callable($headerContent) ? call_user_func($headerContent) : $headerContent)?></div>
                <div class="pull-right">
                <?else:?>
                <div class="pull-left">
                <?endif?>

                    <!-- Nav tabs -->
                    <ul class="nav nav-tabs">
                        <?
                        $tabN = 0;
                        foreach ($tabs as $k => $tab):
                            $tabId = is_numeric($k) ? uniqid('tab' . $k . '_') : $k;
                            $isActive = $activeTab === $tabId || is_numeric($activeTab) && $activeTab == $tabN;
                            $tabs[$k]['id'] = $tabId;
                            $tabN++;
                        ?>
                        <li <?=$isActive ? 'class="active"' : ''?>>
                            <a href="#<?=$tabId?>" data-toggle="tab"><?=is_callable($tab['title']) ? call_user_func($tab['title']) : $tab['title'] ?></a>
                        </li>
                        <?endforeach?>
                    </ul>
                </div>
            </div>

            <div class="panel-body">

                <div class="tab-content">
                <?
                $tabN = 0;
                foreach ($tabs as $k => $tab):
                    $isActive = $activeTab === $tab['id'] || is_numeric($activeTab) && $activeTab == $tabN;
                    $tabN++;
                ?>
                    <div class="tab-pane fade <?=$isActive ? 'in active' : ''?>" id="<?=$tab['id']?>">
                         <?=is_callable($tab['content']) ? call_user_func($tab['content']) : $tab['content']?>           
                    </div>
                <?endforeach?>
                </div>
            </div>
        </div>
    </div><?
}