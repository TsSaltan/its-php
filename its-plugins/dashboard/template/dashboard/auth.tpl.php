<?php $this->incHeader(); ?>
<?php echo $this->uiNavbar(true, false); ?>

<div class="container">
    <div class="row">
        <div class="col-md-4 col-md-offset-4">
            <?php

            $tabPane = $this->uiTabPanel('default');
            $tabPane->tab('login',
                function(){
                    ?><i class='fa fa-user-md'></i>&nbsp;<?=__('authorization')?><?php
                }, 
                
                function() use ($socialLoginTemplate, $socialEnabled, $loginEnabled){ 
                    $this->inc('auth-login');              
                }
            );

            if($registerEnabled){
                $tabPane->tab('register', 
                    function(){
                        ?><i class='fa fa-user-plus'></i>&nbsp;<?=__('registration')?><?php
                    },

                    function() use ($loginEnabled, $passwordEnabled){                    
                        $this->inc('auth-register');              
                    }
                );  
            }

            $this->hook('auth', [$tabPane]);

            echo $tabPane->render()->addClass('login-panel');
            ?>
        </div>
        <?php if($isRestorePassword): ?>
        <div class="col-md-4 col-md-offset-4">
            <?php 
            $forgot = $this->uiPanel('default');
            $forgot->body(function(){
                ?>
                    <a href="<?=$this->makeURI('/dashboard/auth-restore')?>" class="forgot-link"><?=__('forgot-password')?></a>
                <?php
            });
            echo $forgot->render();
            ?>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php $this->incFooter(); ?>