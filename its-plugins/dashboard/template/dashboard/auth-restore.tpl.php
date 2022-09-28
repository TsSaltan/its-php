<?php $this->incHeader(); ?>
<?php echo $this->uiNavbar(true, false); ?>

<div class="container">
    <div class="row">
        <div class="col-md-4 col-md-offset-4">
            <?php

            $panel = $this->uiPanel('default');
            $panel->header(function(){
                ?><i class='fa fa-user-md'></i>&nbsp;<?=__('password-restore')?><?php
            });
            $panel->body(function(){
                ?>
                <?php echo $this->uiAlerts(); ?>
                <?php echo $this->uiAlert(); ?>

                <form role="form" onsubmit="tsUser.restore(this); return false;">
                    <div class="form-group">
                        <input class="form-control" placeholder="<?php _e('e-mail'); ?>" name="email" type="text" autofocus required>
                    </div>
                    
                    <button class="btn btn-lg col-md-6 btn-success btn-block"><?php _e('button/restore-password'); ?></button>
                </form>
                <?php
            });

            echo $panel->render()->addClass('login-panel');
            ?>
        </div>

        <div class="col-md-4 col-md-offset-4">
            <?php 
            $auth = $this->uiPanel('default');
            $auth->body(function(){
                ?>
                    <a href="<?=$this->makeURI('/dashboard/auth')?>" class="forgot-link"><?=__('authorization')?></a>
                <?php
            });
            echo $auth->render();
            ?>
        </div>
    </div>
</div>
<?php $this->incFooter(); ?>