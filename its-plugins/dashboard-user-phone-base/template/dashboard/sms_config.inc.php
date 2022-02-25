<form action="<?=$this->makeURI('/dashboard/config/sendsms')?>" method="POST">
    <?php 
    $smsPane = $this->uiCollapsePanel();
    $smsPane->setId('sms');
    $smsPane->header($this->uiIcon('send') . ' Отправка SMS');
    $smsPane->body(function(){
        if(isset($_GET['sms']) && $_GET['sms'] == 'ok'){
            echo $this->uiAlert('SMS отправлено!', 'success');
        }
        elseif(isset($_GET['sms']) && $_GET['sms'] == 'fail'){
            echo $this->uiAlert('Ошибка при отправке SMS!', 'danger');
        }

        uiPhoneField(null, 'phone');

        ?><div class="form-group">
            <label>Текст сообщения</label>
            <textarea class='form-control' name='message'></textarea>
        </div><?php 
    });
    $smsPane->footer(function(){
        ?><button class='btn btn-primary'>Отправить</button>
        <a href="<?=$this->makeURI('/dashboard/logs/sms')?>" class='btn btn-default'>Открыть логи</a><?php 
    });
    
    
    echo $smsPane;
    ?>
</form>