<?use tsframe\module\user\UserAccess; ?>
<?$this->incHeader()?>
<?$this->incNavbar()?>

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

                        <div class="panel tabbed-panel panel-default">
                            <div class="panel-heading clearfix">
                                <div class="panel-title pull-left">Список зарегистрированных пользователей</div>
                                <div class="pull-right">
                                    
                                </div>
                            </div>
                            <div class="panel-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Имя</th>
                                                <th>Email</th>
                                                <th>Группа</th>
                                                <th width="130px" align="center">Действия</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?foreach($userList->getData() as $userItem):?>
                                            <tr>
                                                <td><?=$userItem->get('id')?></td>
                                                <td><?=$userItem->get('login')?></td>
                                                <td><?=$userItem->get('email')?></td>
                                                <td><?=array_flip($accessList)[$userItem->get('access')]?></td>
                                                <td>
                                                    <?if(UserAccess::checkCurrentUser('user.view')):?><a href="/dashboard/user/<?=$userItem->get('id')?>" class="btn btn-default btn-sm btn-outline" title='Профиль'><i class='fa fa-user'></i></a><?endif?>
                                                    <?if(UserAccess::checkCurrentUser('user.edit')):?><a href="/dashboard/user/<?=$userItem->get('id')?>/edit" class="btn btn-primary btn-outline btn-sm" title='Редактировать'><i class='fa fa-pencil'></i></a><?endif?>
                                                    <?if(UserAccess::checkCurrentUser('user.delete')):?><a href="/dashboard/user/<?=$userItem->get('id')?>/delete" class="btn btn-danger btn-outline btn-sm" title='Удалить'><i class='fa fa-remove'></i></a><?endif?>
                                                </td>
                                            </tr>
                                            <?endforeach?>
                                        </tbody>
                                    </table>
                                </div>
                                <!-- /.table-responsive -->
                            </div>
                            <div class="panel-footer">
                                <?foreach($userList->getPages() as $page):?>
                                <a class="btn btn-primary <?=($page['current'] ? "disabled" : "btn-outline")?>" href="<?=$page['url']?>"><?=$page['title']?></a>
                                <?endforeach?>
                            </div>
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