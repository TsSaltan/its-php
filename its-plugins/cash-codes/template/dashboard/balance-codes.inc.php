<div class="col-md-4">
    <button class="btn btn-primary btn-block" data-toggle="modal" data-target="#balanceCodes">Использовать платёжный код</button>
</div>

<!-- Modal -->
<div class="modal fade" id="balanceCodes" tabindex="-1" role="dialog" aria-labelledby="codeBalanceLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?=$this->makeURI('/dashboard/user/'.$this->vars['selectUser']->get('id').'/cash-code')?>" method="POST">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="codeBalanceLabel">Пополнить баланс через платёжный код</h4>
                </div>
                <div class="modal-body">
                    <div class='row'>
                        <div class='col-lg-12'>
                            Введите код
                        </div>
                        <div class='col-lg-12'>
                            <input class="form-control" name="code" type="text" maxlength="30"/>
                            <input class="form-control" name="user_id" type="hidden" value="<?=$this->vars['selectUser']->get('id')?>"/>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-primary">Оплатить</button>
                </div>
            </form>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->