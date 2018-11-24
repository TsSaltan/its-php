<?$this->incHeader()?>
<?uiNavbar(true, false)?>

        <div class="container">
            <div class="row">
                <div class="col-md-4 col-md-offset-4">
                    <div class="login-panel panel panel-default">
                        <!-- /.panel-heading -->
                        <div class="panel-body">
                            <?if(isset($alert)) showAlerts($alert)?>

                            <!-- Nav tabs -->
                            <ul class="nav nav-tabs">
                                <li class="active">
                                    <a href="#login" data-toggle="tab">Авторизация</a>
                                </li>
                                <li>
                                    <a href="#register" data-toggle="tab">Регистрация</a>
                                </li>
                            </ul>

                            <!-- Tab panes -->
                            <div class="tab-content" style="padding-top: 14px;">
                                <div class="tab-pane fade in active" id="login">
                                    <div class="alert hidden">
                                        <p class='text'></p>    
                                    </div>

                                    <form role="form" onsubmit="tsUser.login(this); return false;">
                                        <div class="form-group">
                                            <input class="form-control" placeholder="Логин или e-mail" name="login" type="text" autofocus>
                                        </div>
                                        <div class="form-group">
                                            <input class="form-control" placeholder="Пароль" name="password" type="password" value="">
                                        </div>
                                        <button class="btn btn-lg col-md-6 btn-success btn-block">Войти</button>
                                    </form>

                                    <div class="row">
                                        <div class="col-md-8 col-md-offset-3" style="margin-top: 18px">
                                            <?=$socialLogin?>
                                        </div>
                                    </div>
                                </div>

                                <div class="tab-pane fade" id="register">
                                    <div class="alert hidden">
                                        <p class='text'></p>   
                                    </div>

                                    <form role="form" onsubmit="tsUser.register(this); return false;">
                                        <fieldset>
                                            <div class="form-group">
                                                <input class="form-control" placeholder="Логин" name="login" type="text">
                                            </div>          
                                            <div class="form-group">
                                                <input class="form-control" placeholder="E-mail" name="email" type="email">
                                            </div>
                                            <div class="form-group">
                                                <input class="form-control" placeholder="Пароль" name="password" type="password" value="">
                                            </div>
                                            <button class="btn btn-lg col-md-6 btn-success btn-block">Регистрация</button>
                                        </fieldset>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <!-- /.panel-body -->
                    </div>
                    <!-- /.panel -->
                </div>
            </div>
            <?$this->hook('auth')?>
        </div>
<?$this->incFooter()?>