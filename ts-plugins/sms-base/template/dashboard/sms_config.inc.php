<div class="row">
    <div class="col-lg-12">
        <?if(isset($_GET['sms']) && $_GET['sms'] == 'ok'):
            uiAlert('SMS отправлено!', 'success');
        elseif(isset($_GET['sms']) && $_GET['sms'] == 'fail'):
            uiAlert('Ошибка при отправке SMS!', 'error');
        endif?>
        <div class="panel tabbed-panel panel-default">
            <div class="panel-heading clearfix">
                <div class="panel-title pull-left">
                    Отправка SMS
                </div>
            </div>
            <form role="form" action="<?=$this->makeURI('/dashboard/config/sendsms')?>" method="POST">
                <div class="panel-body">
                    <?uiPhoneField(null, 'phone')?>
                    <div class="form-group">
                        <label>Текст сообщения</label>
                        <textarea class='form-control' name='message'></textarea>
                    </div>                      
                </div>
                <div class="panel-footer">
                    <button class='btn btn-primary'>Отправить</button>
                    <a href="<?=$this->makeURI('/dashboard/logs/sms')?>" class='btn btn-default'>Открыть логи</a>
                </div>
            </form>
        </div>
    </div>
</div>