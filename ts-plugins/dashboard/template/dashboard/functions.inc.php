<?php
global $that;
$that = $this;

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

function uiAlert(string $message = null, string $type='info'){
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
    ?><script type="text/javascript">tsFrame.basePath = <?=json_encode(\tsframe\App::getBasePath())?>;</script><?
}

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

