<?php $this->incHeader(); ?>
<?php $this->uiNavbar(true, false); ?>

<div class="container">
    <div class="row">
        <div class="col-md-4 col-md-offset-4">
            <?php

            echo   $this->uiTabPanel('panel-default login-panel')
                        ->tab('login',
                            function(){
                                ?><i class='fa fa-user-md'></i>&nbsp;Авторизация<?php
                            }, 
                            
                            function() use ($socialLoginTemplate, $socialEnabled, $loginEnabled){ 
                                $this->inc('auth-login');              
                            }
                        )

                        ->tab('register', 
                            function(){
                                ?><i class='fa fa-user-plus'></i>&nbsp;Регистрация<?php
                            },

                            function() use ($loginEnabled, $passwordEnabled){                    
                                $this->inc('auth-register');              
                            }
                        );  
            ?>
        </div>
    </div>
</div>
<?php $this->incFooter(); ?>