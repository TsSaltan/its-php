<form action="<?=$this->makeURI('/dashboard/config/telegram-bot-api')?>" method="POST">
<?php 
    $configPane = $this->uiCollapsePanel();
    $configPane->setId('telegrambotapi');
    $configPane->header($this->uiIcon('paper-plane') . '&nbsp;' . __('%s configs', 'Telegram Bot API'));
    $configPane->body(function() use ($tgbotapi_token, $tgbotapi_uri){
    ?>
        <div class="row">
            <div class="col-lg-12">
                <div class="form-group">
                    <label><?php _e('Bot API token'); ?></label>   
                    <input class="form-control" type="text" name="tgapi-token" value="<?=$tgbotapi_token?>"/>
                </div>

                <div class="form-group">
                    <label><?php _e('WebHook URL'); ?></label>   
                    <input class="form-control" type="text" name="tgapi-uri" value="<?=$tgbotapi_uri?>" readonly/>
                    <p class="help-block"><?php _e('WebHook URL will be automatically applied after saving token'); ?></p>
                </div>
            </div>
        </div>
        <?php    
    });

    $configPane->footer(function(){
        ?><button class='btn btn-primary'><?php _e('Save'); ?></button><?php 
    });

    echo $configPane;
?>
</form>