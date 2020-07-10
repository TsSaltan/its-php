<form action="<?=$this->makeURI('/dashboard/config/sendsms')?>" method="POST">
<?php uiCollapsePanel('Отправка SMS', 

function(){
    if(isset($_GET['sms']) && $_GET['sms'] == 'ok'):
        uiAlert('SMS отправлено!', 'success');
    elseif(isset($_GET['sms']) && $_GET['sms'] == 'fail'):
        uiAlert('Ошибка при отправке SMS!', 'danger');
    endif;

    uiPhoneField(null, 'phone');
    ?><div class="form-group">
        <label>Текст сообщения</label>
        <textarea class='form-control' name='message'></textarea>
    </div><?php 
}, 

function(){
    ?><button class='btn btn-primary'>Отправить</button>
    <a href="<?=$this->makeURI('/dashboard/logs/sms')?>" class='btn btn-default'>Открыть логи</a><?php 
},
"panel-default",
"send",
"sms")?>
</form>