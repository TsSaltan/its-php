<?php $this->incHeader(); ?>
<?php uiNavbar(true, false); ?>

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
                $this->inc('auth-login');              
            };

            // Tab: register / Регистрация
            if($registerEnabled){       
                $authTabs['register']['title'] =  function(){
                    ?>
                    <i class='fa fa-user-plus'></i>&nbsp;Регистрация
                    <?php
                };

                $authTabs['register']['content'] = function() use ($loginEnabled, $passwordEnabled){                    
                    $this->inc('auth-register');              
                };
            }
            
            $this->hook('auth', [&$authTabs]);
            uiTabPanel(null, $authTabs, 0, 'panel-default login-panel');
            ?>
        </div>
    </div>
</div>
<?php $this->incFooter(); ?>