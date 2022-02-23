<?php $this->incHeader()?>
<?php $this->incNavbar()?>
<?php use tsframe\module\user\UserAccess;?>

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
                        <?php $colors = [
                            0 => 'panel-default',
                            1 => 'panel-primary',
                            2 => 'panel-success',
                            4 => 'panel-danger',
                        ];?>
                        <div class="panel tabbed-panel <?=$colors[$selectUser->get('access')]?>">
                            <div class="panel-heading clearfix">
                                <div class="panel-title pull-left"><b><?=($loginEnabled ? $selectUser->get('login') : $selectUser->get('email'))?></b></div>
                                <div class="pull-right">
                                    <p style='margin: 5px;'><?=$selectUser->get('accessText')?></p>
                                </div>
                            </div>
                            <div class="panel-body">
                                <form role='form'>
                                    <?php $this->hook('user.profile', [$selectUser])?>
                                </form>
                            </div>
                            <div class="panel-footer">
                                <?php if(UserAccess::checkCurrentUser('user.edit') || $self):?><a href="<?=$this->makeURI('/dashboard/user/' . $selectUser->get('id') . '/edit')?>" class="btn btn-primary btn-outline btn-sm" title='Редактировать'><i class='fa fa-pencil'></i> Редактировать</a><?php endif?>
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

<?php $this->incFooter()?>