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