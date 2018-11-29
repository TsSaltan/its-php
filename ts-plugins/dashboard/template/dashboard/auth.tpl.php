<?$this->incHeader()?>
<?uiNavbar(true, false)?>

        <div class="container">
            <div class="row">
                <div class="col-md-4 col-md-offset-4">
                    <?
                    if(isset($alert)) showAlerts($alert);

                    $authTabs = [];
                    $authTabs['login']['title'] = 'Авторизация';
                    $authTabs['login']['content'] = function() use ($socialLogin){
                        ?>
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
                        <?
                    };

                    $authTabs['register']['title'] = 'Регистрация';
                    $authTabs['register']['content'] = function(){
                        ?>
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
                        <?
                    };
                    
                    $this->hook('auth', [&$authTabs]);
                    uiTabPanel(null, $authTabs, 0, 'panel-default login-panel');
                    ?>
                </div>
            </div>
        </div>
<?$this->incFooter()?>