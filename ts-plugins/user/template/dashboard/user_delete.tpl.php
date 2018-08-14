<?$this->incHeader()?>
<?$this->incNavbar()?>
<?use tsframe\module\user\UserAccess;?>

    <!-- Page Content -->
    <div id="page-wrapper">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header"><?=$this->title?></h1>
                </div>
            </div>

            <div class="row">
                    <div class="panel tabbed-panel panel-default">
                            <div class="alert hidden">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                <p class='text'></p>
                            </div>

                            <form role="form" onsubmit="tsUser.query('delete', this); $(sessions).hide(); return false;">
                                <div class="panel-heading clearfix">
                                    <div class="panel-title pull-left">Удаление профиля</div>
                                </div>
                                <div class="panel-body">
                                    <?if($self):?>
                                    <p>Вы собираетесь удалить собственный профиль</p>
                                    <?else:?>
                                    <p>Вы собираетесь удалить пользователя <b><?=$selectUser->get('login')?></b> (ID: <b><?=$selectUser->get('id')?></b>).</p>
                                    <?endif?>
                                    <p>Это действие невозможно отменить</p>
                                </div>
                                <div class="panel-footer">
                                    <input class="form-control" name='id' type='hidden' value="<?=$selectUser->get('id')?>">
                                    <button class="btn btn-default btn-danger btn-sm">Удалить</button>
                                    <a href="/dashboard/user/list" class="btn btn-default btn-outline btn-sm">Отмена</a>
                                </div>
                            </form>
                        </div>
                        <!-- /.panel -->

                    </div>
                    <!-- /.col-lg-12 -->

                </div>
                <!-- /.row -->

            <!-- ... Your content goes here ... -->

        </div>
    </div>

<?$this->incFooter()?>