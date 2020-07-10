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

            <?=$this->hook('referrer.before')?>

            <div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading clearfix">
                            <div class="panel-title pull-left">Партнёрская программа</div>
                        </div>

                        <div class="panel-body">

                            <div class="form-group">
                                <label>Выша ссылка для приглашения пользователей</label>
                                <input class="form-control" value="<?=$userRefLink?>" onfocus="this.select()">
                                <?php if(!is_null($userRefStatLink)):?>
                                <p class="help-block"><a href="<?=$userRefStatLink?>" target="_blank">Статистика переходов по ссылке</a></p>
                                <?php endif?>
                                <?php if(!is_null($userReferrer)):?>
                                <p class="help-block">Вас пригласил: <a href="<?=$this->makeURI('/dashboard/user/' . $userReferrer->get('id'))?>"><b><?=$userReferrer->get('login')?></b></a></p>
                                <?php endif?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?=$this->hook('referrer.middle')?>

                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading clearfix">
                            <div class="panel-title pull-left">Приглашённые вами пользователи</div>
                            <div class="pull-right">
                                
                            </div>
                        </div>
                        <div class="panel-body">
                            <?php if(sizeof($userReferrals) == 0):?>
                            <p>Вы ещё никого не пригласили</p>
                            <?php else:?>
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Пользователь</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($userReferrals as $user):?>
                                        <tr>
                                            <td><?=$user->get('id')?></td>
                                            <td><a href="<?=$this->makeURI('/dashboard/user/' . $user->get('id'))?>"><?=$user->get('login')?></a></td>
                     
                                        </tr>
                                        <?php endforeach?>
                                    </tbody>
                                </table>
                            </div>
                            <!-- /.table-responsive -->
                            <?php endif?>
                        </div>
                    </div>
                    <!-- /.panel -->
                </div>
            </div>
            <?=$this->hook('referrer.after')?>
        </div>
    </div>

<?php $this->incFooter()?>