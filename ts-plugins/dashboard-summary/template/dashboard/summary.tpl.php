<?php $this->incHeader()?>
<?php $this->incNavbar()?>

<!-- Page Content -->
<div id="page-wrapper">
    <div class="container-fluid">

        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header"><?=$this->title?></h1>
            </div>
        </div>

        <div class="row">
            <?php $this->hook('dashboard.stat.before') ?>
    
            <!-- Users critical errors -->
            <div class="col-lg-3 col-md-6">
                <div class="panel panel-<?=($summary_critical_total > 0) ? 'red' : 'green'?>">
                    <div class="panel-heading">
                        <div class="row">
                            <div class="col-xs-3">
                                <i class="fa fa-warning fa-5x"></i>
                            </div>
                            <div class="col-xs-9 text-right">
                                <div class="huge"><?=$summary_critical_total?></div>
                                <div>
                                    <p>Критические ошибки, требующие внимания</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <a href="<?=$this->makeURI('/dashboard/logs', ['level' => 5])?>">
                        <div class="panel-footer">
                            <span class="pull-left">Просмотр логов</span>
                            <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>

                            <div class="clearfix"></div>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Users stats-->
            <div class="col-lg-3 col-md-6">
                <div class="panel panel-primary">
                    <div class="panel-heading">
                        <div class="row">
                            <div class="col-xs-3">
                                <i class="fa fa-users fa-5x"></i>
                            </div>
                            <div class="col-xs-9 text-right">
                                <div>
                                    <p>Всего пользователей: <strong><?=$summary_users_total?></strong></p>
                                    <p>Зарегистрировано сегодня: <strong><?=$summary_users_today?></strong></p>
                                    <p>Зарегистрировано в этом месяце: <strong><?=$summary_users_tomonth?></strong></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <a href="<?=$this->makeURI('/dashboard/user/list')?>">
                        <div class="panel-footer">
                            <span class="pull-left">Список пользователей</span>
                            <span class="pull-right"><i class="fa fa-users"></i></span>

                            <div class="clearfix"></div>
                        </div>
                    </a>

                    <a href="<?=$this->makeURI('/dashboard/logs', ['section' => 'user-registration'])?>">
                        <div class="panel-footer">
                            <span class="pull-left">Логи регистрации</span>
                            <span class="pull-right"><i class="fa fa-list-alt"></i></span>

                            <div class="clearfix"></div>
                        </div>
                    </a>

                    <a href="<?=$this->makeURI('/dashboard/config', [], 'user')?>">
                        <div class="panel-footer">
                            <span class="pull-left">Настройки авторизации и регистрации</span>
                            <span class="pull-right"><i class="fa fa-wrench"></i></span>

                            <div class="clearfix"></div>
                        </div>
                    </a>
                </div>
            </div>

            <?php $this->hook('dashboard.stat.after') ?>
        </div>
    </div>
</div>

<?php $this->incFooter()?>