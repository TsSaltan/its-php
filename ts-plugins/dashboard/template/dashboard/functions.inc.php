<?php
global $that;
$that = $this;

function showAlerts(){
    global $that;
    if(!isset($that->alert) || !is_array($that->alert)) return;
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

