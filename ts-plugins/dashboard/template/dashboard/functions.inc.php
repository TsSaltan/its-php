<?php
global $that;
$that = $this;

function showAlerts(/*array $alerts*/){
    global $that;
    foreach($that->alert as $type => $messages){
        $messages = is_array($messages) ? $messages : [$messages];
        foreach ($messages as $message) {
            ?>
            <div class="alert alert-<?=$type?>">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <p class='text'><?=$message?></p>
            </div>
            <?
        }
    }
}

