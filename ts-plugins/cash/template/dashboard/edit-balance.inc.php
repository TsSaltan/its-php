<div class="col-md-3">
    <button class="btn btn-warning btn-block" data-toggle="modal" data-target="#editBalance">Изменить баланс</button>
</div>

<!-- Modal -->
<div class="modal fade" id="editBalance" tabindex="-1" role="dialog" aria-labelledby="editBalanceLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?=$this->makeURI('/dashboard/user/edit-balance')?>" method="POST">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="editBalanceLabel">Изменить баланс пользователя</h4>
                </div>
                <div class="modal-body">
                    <div class='row'>
                        <div class='col-lg-6'>
                            Пользователь
                        </div>
                        <div class='col-lg-6'>
                            <p><b><?=$this->selectUser->get('login')?></b> (ID: <b><?=$this->selectUser->get('id')?></b>)</p>
                            <input name="user_id" type="hidden" value="<?=$this->selectUser->get('id')?>"/>
                        </div>
                    </div>

                    <div class='row'>
                        <div class='col-lg-6'>
                            Введите сумму, на которую нужно изменить баланс
                        </div>
                        <div class='col-lg-6'>
                            <input class="form-control" name="balance" type="number" value="0" step="0.01"/>
                            <p class="help-block">Можно указать отрицательные значения</p>
                        </div>
                    </div>

                    <div class='row'>
                        <div class='col-lg-6'>
                            Описание
                        </div>
                        <div class='col-lg-6'>
                            <textarea class="form-control" name="description" rows="3">Изменение баланса</textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-warning">Изменить</button>
                </div>
            </form>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->