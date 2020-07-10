<?php $this->incHeader()?>
<?php uiNavbar(true, false)?>

<div class="container">
    <div class="row">
        <div class="col-md-4 col-md-offset-4">
            <?php
            $alert = isset($alert) ? $alert : [];
            $authTabs = [];

            // Tab: login / Авторизация
            $authTabs['login']['title'] = function(){
                ?>
                <i class='fa fa-user-md'></i>&nbsp;Авторизация
                <?php
            };
            $authTabs['login']['content'] = function() use ($socialLoginTemplate, $socialEnabled, $loginEnabled, $alert){
                if(isset($alert)) showAlerts($alert); ?>

                <div class="alert hidden">
                    <p class='text'></p>    
                </div>

                <form role="form" onsubmit="tsUser.login(this); return false;">
                    <div class="form-group">
                        <input class="form-control" placeholder="<?=($loginEnabled ? 'Логин или ': '')?>E-mail" name="login" type="text" autofocus required>
                    </div>
                    <div class="form-group">
                        <input class="form-control" placeholder="Пароль" name="password" type="password" required>
                    </div>
                    <?php $this->hook('auth.login')?>
                    <button class="btn btn-lg col-md-6 btn-success btn-block">Войти</button>
                </form>
                <?php if($socialEnabled): ?>
                <div class="row">
                    <div class="col-md-8 col-md-offset-3" style="margin-top: 18px">
                        <?=$socialLoginTemplate?>
                    </div>
                </div>
                <?php endif;
            };

            // Tab: register / Регистрация
            if($registerEnabled){       
                $authTabs['register']['title'] =  function(){
                    ?>
                    <i class='fa fa-user-plus'></i>&nbsp;Регистрация
                    <?php
                };
                $authTabs['register']['content'] = function() use ($loginEnabled, $passwordEnabled){
                    ?>
                    <div class="alert hidden">
                        <p class='text'></p>   
                    </div>


                    <?php if(!$passwordEnabled): ?>
                    <div class="alert alert-info">
                        <p class='text'>Пароль будет создан автоматически</p>   
                    </div>
                    <?php endif; ?>

                    <form role="form" onsubmit="tsUser.register(this); return false;">
                        <fieldset>
                            <?php if($loginEnabled): ?>
                            <div class="form-group">
                                <input class="form-control" placeholder="Логин" name="login" type="text" required>
                            </div>          
                            <?php endif; ?>
                            <div class="form-group">
                                <input class="form-control" placeholder="E-mail" name="email" type="email" required>
                            </div>

                            <?php if($passwordEnabled): ?>
                            <div class="form-group">
                                <input class="form-control" placeholder="Пароль" name="password" type="password" value="" required>
                            </div>
                            <?php endif; ?>

                            <?php $this->hook('auth.register'); ?>
                            <button class="btn btn-lg col-md-6 btn-success btn-block">Регистрация</button>
                        </fieldset>
                    </form>
                    <?php
                };
            }
            
            $this->hook('auth', [&$authTabs]);
            uiTabPanel(null, $authTabs, 0, 'panel-default login-panel');
            ?>
        </div>
    </div>
</div>
<?php $this->incFooter()?>