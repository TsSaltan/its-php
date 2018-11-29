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
                <?showAlerts()?>
            </div>
            <!-- /.row -->

            <?
            $configTabs = [];
            $configTabs['main']['title'] = 'Основные настройки';
            $configTabs['main']['content'] = function() use ($user, $selectUser, $self){
                uiAlert();
                ?>
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
                    <?if(UserAccess::checkCurrentUser('user.delete') || $self):?>
                        <a href="<?=$this->makeURI('/dashboard/user/' . $selectUser->get('id') . '/delete')?>" class="btn btn-danger btn-outline btn-sm" title='Удалить'><i class='fa fa-remove'></i> Удалить профиль</a><?endif?>
                    </div>
                </form>
                <?
            };

            $configTabs['password']['title'] = 'Изменение пароля';
            $configTabs['password']['content'] = function() use ($selectUser, $self){
                $msg = (isset($this->vars['tempPass']) && strlen($this->vars['tempPass']) > 0) ? 'Вам установлен автоматически сгенерированный пароль <b>'.$this->vars['tempPass'].'</b>. Смените его в настройках!' : null;
                uiAlert($msg, 'warning');

                if($self):?>
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
                <?
            };

            $configTabs['sessions']['title'] = 'Сессии';
            $configTabs['sessions']['content'] = function() use ($user, $selectUser){
                uiAlert();
                ?>
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
                            <?foreach($selectUser->getSessions() as $k => $session):?>
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
                <?
            };

            $configTabs['social']['title'] = 'Социальные сети';
            $configTabs['social']['content'] = function() use ($self, $social, $selectUser) {
                showAlerts($this->vars['socialAlert'] ?? []);
                ?>

                <div class="panel-body col-lg-6">
                    <div class="col-lg-12">
                        <?if($self && isset($this->vars['socialLogin'])):?>
                        <h3>Привязать аккаунт</h3>
                        <div class="form-group">
                            <label>Выберите социальную сеть</label>
                            <?=$this->vars['socialLogin']?>
                        </div>
                        <?endif?>
                
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
                <?
            };

            $activeTab = 0;
            $this->hook('user.edit', [&$configTabs, &$activeTab]);
            uiTabPanel(null, $configTabs, $activeTab, 'panel-default');
            ?>
        </div>
    </div>

<?$this->incFooter()?>