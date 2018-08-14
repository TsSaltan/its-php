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
                                        <li class="active"><a href="#tab-default-1" data-toggle="tab">Основные настройки</a></li>
                                        <li><a href="#tab-default-2" data-toggle="tab">Пароль</a></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="panel-body">
                                <div class="tab-content">
                                    <div class="tab-pane fade in active" id="tab-default-1">
                                        <div class="alert hidden">
                                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                            <p class='text'></p>
                                        </div>

                                        <form role="form" onsubmit="tsUser.edit(this); return false;">
                                            <div class="form-group">
                                                <label>Имя пользователя</label>
                                                <input class="form-control" name='login' type='text' value="<?=$this->login?>">
                                            </div>                                            

                                            <div class="form-group">
                                                <label>E-mail</label>
                                                <input class="form-control" name='email' type='email' value="<?=$this->email?>">
                                            </div>                                            

                                            <div class="form-group">
                                                <label>Группа</label>
                                                <select class="form-control" disabled>
                                                    <?foreach ($this->accessList as $name => $value):?>
                                                        <option value="<?=$value?>"<?=($value==$this->access?' selected':'')?>><?=$name?></option>
                                                    <?endforeach?>
                                                </select>
                                            </div>                                            
                                            
                                            <button type="submit" class="btn btn-success">Сохранить</button>
                                            <button type="reset" class="btn btn-default">Отмена</button>
                                        </form>
                                       
                                    </div>
                                    <div class="tab-pane fade" id="tab-default-2">                                    
                                        <div class="alert hidden">
                                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                            <p class='text'></p>
                                        </div>
                                        <form role="form" onsubmit="tsUser.query('changePassword', this); return false;">
                                            <div class="form-group">
                                                <label>Текущий пароль</label>
                                                <input class="form-control" name='current_password' type='password' placeholder="*********">
                                            </div>                                              

                                            <div class="form-group">
                                                <label>Новый пароль</label>
                                                <input class="form-control" name='new_password' type='password' placeholder="*********">
                                            </div>                                          
                                            
                                            <button type="submit" class="btn btn-warning">Изменить</button>
                                        </form>
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