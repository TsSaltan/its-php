<?use tsframe\module\user\UserAccess;?>
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
                                <div class="panel-title pull-left">Настройки профиля</div>
                                <div class="pull-right">
                                    <ul class="nav nav-tabs">
                                        <li <?=(isset($socialTab)?"":'class="active"')?>><a href="#tab-1" data-toggle="tab">Основные настройки</a></li>
                                        <li><a href="#tab-2" data-toggle="tab">Изменение пароля</a></li>
                                        <li><a href="#tab-3" data-toggle="tab">Сессии</a></li>
                                        <li <?=(isset($socialTab)?'class="active"':'')?>><a href="#tab-4" data-toggle="tab">Социальные сети</a></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="panel-body">
                                <div class="tab-content">
                                    <div class="tab-pane fade <?=(isset($socialTab)?"":"in active")?>" id="tab-1">
                                        <div class="alert hidden">
                                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                            <p class='text'></p>
                                        </div>

                                        <form role="form" onsubmit="tsUser.edit(this); return false;">
                                            <input class="form-control" name='id' type='hidden' value="<?=$selectUser->get('id')?>">
                                            <div class="form-group">
                                                <label>Имя пользователя</label>
                                                <input class="form-control" name='login' type='text' value="<?=$selectUser->get('login')?>">
                                            </div>                                            

                                            <div class="form-group">
                                                <label>E-mail</label>
                                                <input class="form-control" name='email' type='email' value="<?=$selectUser->get('email')?>">
                                            </div>                                            

                                            <div class="form-group">
                                                <label>Группа</label>
                                                <select name='access' class="form-control" <?=($self || !UserAccess::checkUser($user, 'user.editAccess') ? 'disabled' : '')?>>
                                                    <?foreach ($this->accessList as $name => $value):?>
                                                        <option value="<?=$value?>"<?=($value==$selectUser->get('access')?' selected':'')?>><?=$name?></option>
                                                    <?endforeach?>
                                                </select>
                                            </div>                                            
                                            
                                            <button type="submit" class="btn btn-success">Сохранить</button>
                                            <button type="reset" class="btn btn-default">Отмена</button>
                                            <div class='pull-right'>
                                            <?if(UserAccess::checkCurrentUser('user.delete') || $self):?><a href="/dashboard/user/<?=$selectUser->get('id')?>/delete" class="btn btn-danger btn-outline btn-sm" title='Удалить'><i class='fa fa-remove'></i> Удалить профиль</a><?endif?>
                                            </div>
                                        </form>
                                       
                                    </div>
                                    <div class="tab-pane fade" id="tab-2">                                    
                                        <?if(isset($tempPass)):?>
                                        <div class="alert alert-warning">
                                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                            <p class='text'>Вам установлен автоматически сгенерированный пароль <b><?=$tempPass?></b>. Смените его в настройках!</p>
                                        </div>
                                        <?endif?>

                                        <div class="alert hidden">
                                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                            <p class='text'></p>
                                        </div>
                                        <?if($self):?>
                                        <form role="form" onsubmit="tsUser.query('changePassword', this); return false;">
                                            <input class="form-control" name='id' type='hidden' value="<?=$selectUser->get('id')?>">
                                            <div class="form-group">
                                                <label>Текущий пароль</label>
                                                <input class="form-control" name='current_password' type='password' placeholder="">
                                            </div>                                              

                                            <div class="form-group">
                                                <label>Новый пароль</label>
                                                <input class="form-control" name='new_password' type='password' placeholder="">
                                            </div>                                          
                                            
                                            <button type="submit" class="btn btn-success">Изменить</button>
                                        </form>
                                        <?endif?>
                                        <hr/>
                                        <form role="form" onsubmit="tsUser.query('resetPassword', this); return false;">
                                            <input class="form-control" name='id' type='hidden' value="<?=$selectUser->get('id')?>">
                                            <button type="submit" class="btn btn-warning">Сбросить пароль</button>
                                        </form>
                                    </div>
                                    <div class="tab-pane fade" id="tab-3"> 
                                        <div class="alert hidden">
                                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                            <p class='text'></p>
                                        </div>
                                        <div class="table-responsive" id='sessions'>
                                            <table class="table table-striped table-bordered table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>IP</th>
                                                        <th>Истекает</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?foreach($user->getSessions() as $k => $session):?>
                                                    <tr>
                                                        <td><?=$k+1?></td>
                                                        <td><?=$session['ip']?></td>
                                                        <td><?=$session['expires']?></td>
                                                    </tr>
                                                    <?endforeach?>
                                                </tbody>
                                            </table>
                                        </div>
                                        <!-- /.table-responsive -->

                                        <form role="form" onsubmit="tsUser.query('closeSessions', this); $(sessions).hide(); return false;">
                                            <input class="form-control" name='id' type='hidden' value="<?=$selectUser->get('id')?>">
                                            <button type="submit" class="btn btn-primary">Закрыть все сессии</button>
                                        </form>
                                    </div>     

                                    <div class="tab-pane fade <?=(isset($socialTab)?"in active":'')?>" id="tab-4"> 
                                        <?showAlerts()?>

                                        <div class="panel-body col-lg-6">
                                            <div class="col-lg-12">
                                                <h3>Привязать аккаунт</h3>
                                                <?if(isset($socialLogin)):?>
                                                <div class="form-group">
                                                    <label>Выберите социальную сеть</label>
                                                    <?=$socialLogin?>
                                                </div>

                                                <h3>Присоединённые аккаунты</h3>
                                            </div>

                                            <?foreach ($social as $networkName => $id):?>
                                            <div class="col-lg-12" style="margin-top:15px">
                                                <div class="alert hidden">
                                                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                                    <p class='text'></p>
                                                </div>
                                                <div class="col-lg-10">
                                                    <? $replace = ['google' => 'google-plus', 'vkontakte' => 'vk']; ?>
                                                    <? $network = $replace[$networkName] ?? $networkName ; ?>
                                                    <a href="<?=$id?>" target="_blank" class="btn btn-block btn-default btn-social btn-<?=$network?>"><i class="fa fa-<?=$network?>"></i> <?=basename($id)?></a>
                                                    
                                                </div> 
                                                <div class="col-lg-2">
                                                    <form role="form" onsubmit="tsUser.query('deleteSocial', this); $(sessions).hide(); return false;">
                                                        <input class="form-control" name='id' type='hidden' value="<?=$selectUser->get('id')?>">
                                                        <input class="form-control" name='network' type='hidden' value="<?=$networkName?>">
                                                        <button type="submit" class="btn btn-outline btn-danger">Удалить</button>
                                                    </form>
                                                </div> 
                                            </div>
                                            <?endforeach?>

                                

                                        </div>                                        

                                        <?endif?>
                                    </div>
                                </div>
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