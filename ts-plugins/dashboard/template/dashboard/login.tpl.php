<?$this->incHeader()?>

        <div class="container">

            <div class="row">
                <div class="col-md-4 col-md-offset-4">
                    <div class="login-panel panel panel-default">
                        <?if(isset($alert)) showAlerts($alert)?>
                        <div class="panel-heading">
                            <h3 class="panel-title">Авторизация</h3>
                        </div>                        


                        <div class="panel-body">
                            <div class="alert hidden">
                                <p class='text'></p>    
                            </div>

                            <form role="form" onsubmit="tsUser.login(this); return false;">
                                <fieldset>
                                    <div class="form-group">
                                        <input class="form-control" placeholder="Логин или e-mail" name="login" type="text" autofocus>
                                    </div>
                                    <div class="form-group">
                                        <input class="form-control" placeholder="Пароль" name="password" type="password" value="">
                                    </div>
                                    <!-- Change this to a button or input when using this as a form -->
                                    <button class="btn btn-lg col-md-6 btn-success btn-block">Войти</button>
                                </fieldset>
                            </form>
                        </div>

                        <div class="panel-footer">
                            <div class="row">
                                <div class="col-md-8 col-md-offset-3">
                                    <?=$socialLogin?>
                                </div>
                            </div>
                        </div>

                        <div class="panel-heading" style='margin-top: 10px; border-top:1px solid #ddd; border-radius:0;'>
                            <h3 class="panel-title">Регистрация</h3>
                        </div>
                        <div class="panel-body">
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
                                    <button class="btn btn-lg col-md-6 btn-default btn-block">Регистрация</button>
                                </fieldset>
                            </form>

                        </div>
                    </div>                    
                </div>
            </div>
        </div>
<?$this->incFooter()?>