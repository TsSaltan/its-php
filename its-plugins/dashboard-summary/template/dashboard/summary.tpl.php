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
            <?php $this->hook('summary.before') ?>
    
            <!-- Critical errors -->
            <?php if(isset($summary_critical_total)): ?>
            <div class="col-lg-4">
                <div class="panel panel-<?=($summary_critical_total > 0) ? 'red' : 'green'?>">
                    <div class="panel-heading">
                        <div class="row">
                            <div class="col-xs-3">
                                <i class="fa fa-warning fa-5x"></i>
                            </div>
                            <div class="col-xs-9 text-right">
                                <div class="huge"><?=$summary_critical_total?></div>
                                <div>
                                    <p>
                                    <?php if($summary_critical_total == 0): ?>
                                        Нет критических ошибок
                                    <?php else: ?>
                                        Есть критические ошибки, требующие внимания
                                    <?php endif; ?>
                                    </p>
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
                    <a href="<?=$this->makeURI('/dashboard/summary', ['action' => 'reset-errors'])?>">
                        <div class="panel-footer">
                            <span class="pull-left">Сбросить счётчик ошибок</span>
                            <span class="pull-right"><i class="fa fa-check-circle"></i></span>

                            <div class="clearfix"></div>
                        </div>
                    </a>
                </div>
            </div>
            <?php endif ?>

            <!-- Users stats-->
            <?php if(isset($summary_users_total)): ?>
            <div class="col-lg-4">
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
            <?php endif ?>            

            <?php if(isset($summary_cache) && is_array($summary_cache)): ?>
            <div class="col-lg-4">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <div class="row">
                            <div class="col-xs-3">
                                <i class="fa fa-tasks fa-5x"></i>
                            </div>
                            <div class="col-xs-9 text-right">
                                <div class="huge"><?=round(array_sum($summary_cache) / 1024 / 1024, 2)?> MiB</div>
                                <div>
                                    <p><?=__('file-cache')?>: <strong><?=round(($summary_cache['fs'] ?? 0) / 1024 / 1024, 2)?> MiB</strong></p>
                                    <p><?=__('database-cache')?>: <strong><?=round(($summary_cache['db'] ?? 0) / 1024 / 1024, 2)?> MiB</strong></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <a href="#" data-toggle="modal" data-target="#modal-clear-cache">
                        <div class="panel-footer">
                            <span class="pull-left"><?=__('button/clear-cache')?></span>
                            <span class="pull-right"><i class="fa fa-trash"></i></span>
                            <div class="clearfix"></div>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Modal -->
            <div class="modal fade" id="modal-clear-cache" tabindex="-1" role="dialog" aria-labelledby="modal-clear-cache-label" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                            <h4 class="modal-title" id="modal-clear-cache-label"><?=__('confirm-clear-cache-title')?></h4>
                        </div>
                        <div class="modal-body"><?=__('confirm-clear-cache')?></div>
                        <div class="modal-footer">
                            <form action="<?=$this->makeURI('/dashboard/summary-clear-cache')?>" method="POST">
                                <button type="button" class="btn btn-default" data-dismiss="modal"><?=__('button/cancel')?></button>
                                <button type="submit" class="btn btn-danger"><?=__('button/clear')?></button>
                            </form>
                        </div>
                    </div>
                    <!-- /.modal-content -->
                </div>
                <!-- /.modal-dialog -->
            </div>
            <!-- /.modal -->
            <?php endif ?>

            <?php $this->hook('summary.after') ?>
        </div>
    </div>
</div>

<?php $this->incFooter()?>