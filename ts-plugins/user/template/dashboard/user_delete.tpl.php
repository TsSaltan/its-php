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
                <div class="col-lg-12">
                    <div class="alert hidden">
                        <p class='text'></p>
                    </div>
                    
                    <form role="form" onsubmit="tsUser.query('delete', this); this.style.display = 'none'; return false;">
                        <div class="panel panel-default">
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
                                    <a href="<?=$this->makeURI('/dashboard/user/list')?>" class="btn btn-default btn-outline btn-sm">Отмена</a>
                                </div>
                            
                        </div>
                        <!-- /.panel -->
                    </form>
                </div>
            </div>
            <!-- /.row -->
        </div>
    </div>

<?$this->incFooter()?>