<?php


/**
 * Подключить js-скрипты фреймворка
 */
function jsFrame(){
    global $tpl;
    $tpl->js('ts-client/frame.js', 'ts-client/user.js');
    ?><script type="text/javascript">tsFrame.basePath = <?=json_encode(\tsframe\App::getBasePath())?>;</script><?php
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
                        <a data-toggle="collapse" href="#<?=$paneId?>"><?php if(!is_null($icon)):?><i class='fa fa-<?=$icon?>'></i>&nbsp;&nbsp;<?php endif?><?=(is_callable($headerContent) ? call_user_func($headerContent) : $headerContent)?></a>
                    </h4>
                </div>
                <div id="<?=$paneId?>" class="panel-collapse collapse">
                    <div class="panel-body"><?=(is_callable($bodyContent) ? call_user_func($bodyContent) : $bodyContent)?></div>
                    <?php if(!is_null($footerContent)):?>
                    <div class="panel-footer"><?=(is_callable($footerContent) ? call_user_func($footerContent) : $footerContent)?></div>
                    <?php endif?>
                </div>
            </div>
        </div>
    </div><?php
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
        <div class="col-lg-12">
            <div class="panel tabbed-panel <?=$panelClass?>">
                <div class="panel-heading clearfix">
                    <?php if($hasHeader):?>
                    <div class="panel-title pull-left"><?=(is_callable($headerContent) ? call_user_func($headerContent) : $headerContent)?></div>
                    <div class="pull-right">
                    <?php else:?>
                    <div class="pull-left">
                    <?php endif?>

                        <!-- Nav tabs -->
                        <ul class="nav nav-tabs">
                            <?php
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
                            <?php endforeach?>
                        </ul>
                    </div>
                </div>

                <div class="panel-body">

                    <div class="tab-content">
                    <?php
                    $tabN = 0;
                    foreach ($tabs as $k => $tab):
                        $isActive = $activeTab === $tab['id'] || is_numeric($activeTab) && $activeTab == $tabN;
                        $tabN++;
                    ?>
                        <div class="tab-pane fade <?=$isActive ? 'in active' : ''?>" id="<?=$tab['id']?>">
                             <?=is_callable($tab['content']) ? call_user_func($tab['content']) : $tab['content']?>           
                        </div>
                    <?php endforeach?>
                    </div>
                </div>
            </div>
        </div>
    </div><?php 
}