<?php $this->incHeader(); ?>
<?php echo $this->uiNavbar(true, false); ?>

<div class="container">
    <div class="row">
        <div class="col-md-4 col-md-offset-4">
            <?php

            $tabPane = $this->uiTabPanel('default');
            $tabPane->tab('login',
                function(){
                    ?><i class='fa fa-user-md'></i>&nbsp;Авторизация<?php
                }, 
                
                function() use ($socialLoginTemplate, $socialEnabled, $loginEnabled){ 
                    $this->inc('auth-login');              
                }
            );

            $tabPane->tab('register', 
                function(){
                    ?><i class='fa fa-user-plus'></i>&nbsp;Регистрация<?php
                },

                function() use ($loginEnabled, $passwordEnabled){                    
                    $this->inc('auth-register');              
                }
            );  

            echo $tabPane->render()->addClass('login-panel');
            ?>
        </div>
    </div>
</div>
<?php $this->incFooter(); ?>