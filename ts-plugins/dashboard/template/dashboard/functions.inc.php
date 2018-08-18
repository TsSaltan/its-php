<?php
global $that;
$that = $this;

function showAlerts(){
    global $that;
    if(!is_array($that->alert)) return;
    foreach($that->alert as $type => $messages){
        $messages = is_array($messages) ? $messages : [$messages];
        foreach ($messages as $message) {
            uiAlert($message, $type);
        }
    }
}

function uiAlert(string $message = null, string $type='primary'){
    $view = is_null($message) ? 'hidden' : '';
?>
    <div class="alert alert-<?=$type?> <?=$view?>">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <p class='text'><?=$message?></p>
    </div>
<?
}

function jsFrame(){
    global $that;
    $that->js('ts-client/frame.js', 'ts-client/user.js');
    ?><script type="text/javascript">tsFrame.basePath = <?=json_encode($that->makeURI(\tsframe\App::getBasePath()))?>;</script><?
}

