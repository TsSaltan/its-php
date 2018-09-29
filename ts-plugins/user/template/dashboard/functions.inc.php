<?
/**
 * Создать панель с табами
 * @param  array       $params   [title, type]
 * @param  array       $tabs   ['name' => 'contents']
 * @param  int|integer $active 
 */
function uiTabPanel(array $params = [], array $tabs = [], int $active = 0){
	global $that;

    $title = $params['title'] ?? false;
    $type = $params['type'] ?? 'default';

    $tabTitle = '';
    $tabContent = '';
    $i = 0;

    foreach($tabs as $tab => $content){
        if(is_callable($content)){
            ob_start();
            call_user_func($content);
            $content = ob_get_clean();
        }

        $tabId = md5($tab) . $i;
        $tabTitle .= ($i == $active) ? '<li class="active">' : '<li>';
        $tabTitle .= '<a href="#tab-'. $tabId . '" data-toggle="tab">'. $tab .'</a></li>';

        $tabContent .= '<div class="tab-pane fade in '. ($i == $active ? 'active' : '') . '" id="tab-' . $tabId . '">' . $content . '</div>';
        $i++;
    }

    ?>
    <div class="col-lg-12">
    	<div class="panel tabbed-panel panel-<?=$type?>">
        	<div class="panel-heading clearfix">
                <?if($title !== false):?>
            	<div class="panel-title pull-left"><?=$title?></div>
                <div class="pull-right">
                <?else:?>
                <div class="pull-left">
                <?endif?>
                    <ul class="nav nav-tabs">
                        <?=$tabTitle?>
                    </ul>
                </div>
            </div>
            <div class="panel-body">
            	<div class="tab-content">
                   <?=$tabContent?>
                </div>
            </div>
        </div>
        <!-- /.panel -->
    </div>
    <!-- /.col-lg-12 -->
    <?
}