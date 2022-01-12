<?php echo $this->uiAlerts(); ?>
<?php echo $this->uiAlert(); ?>

<form role="form" onsubmit="tsUser.login(this); return false;">
	<div class="form-group">
		<input class="form-control" placeholder="<?php ($loginEnabled ? _e('Login or e-mail') : _e('E-mail')); ?>" name="login" type="text" autofocus required>
    </div>
    
    <div class="form-group">
    	<input class="form-control" placeholder="<?php _e('Password'); ?>" name="password" type="password" required>
    </div>
    <?php $this->hook('auth.login')?>
    <button class="btn btn-lg col-md-6 btn-success btn-block"><?php _e('Sign in'); ?></button>
</form>

<?php if($socialEnabled): ?>
<div class="row">
    <div class="col-md-8 col-md-offset-3" style="margin-top: 18px">
    	<?=$socialLoginTemplate?>
	</div>
</div>
<?php endif; ?>