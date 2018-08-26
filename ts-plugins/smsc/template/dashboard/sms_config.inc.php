<div class="row">
    <div class="col-lg-12">
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
                </div>
            </form>
        </div>
    </div>
</div>