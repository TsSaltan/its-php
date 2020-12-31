<?php
/**
 * Подключить js-скрипты фреймворка
 */
function jsFrame(){
    global $tpl;
    $tpl->js('ts-client/frame.js', 'ts-client/user.js');
    ?><script type="text/javascript">tsFrame.basePath = <?=json_encode(\tsframe\App::getBasePath())?>;</script><?php
}