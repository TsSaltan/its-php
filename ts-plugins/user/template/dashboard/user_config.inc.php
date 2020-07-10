<form action="<?=$this->makeURI('/dashboard/config/user')?>" method="POST">
<?php uiCollapsePanel('Настройки пользователей', function() use ($registerEnabled, $socialEnabled, $loginEnabled, $passwordEnabled, $emailOnRegister, $loginOnRegister, $accesses){
    ?>
    <h3 style="margin: 0 10px 10px 0;">Настройка авторизации</h3>
    <div class="row">
        <div class="col-lg-6">
            <div class="form-group">
                <label>Регистрация на сайте</label>
                <div class="radio">
                    <label><input type="radio" name="registerEnabled" value="1" <?=($registerEnabled)?'checked':''?>> Разрешена</label>
                </div>
                <div class="radio">
                    <label><input type="radio" name="registerEnabled" value="0" <?=(!$registerEnabled)?'checked':''?>> Запрещена</label>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="form-group">
                <label>Авторизация через социальные сети</label>
                <div class="radio">
                    <label><input type="radio" name="socialEnabled" value="1" <?=($socialEnabled)?'checked':''?>> Разрешена</label>
                </div>
                <div class="radio">
                    <label><input type="radio" name="socialEnabled" value="0" <?=(!$socialEnabled)?'checked':''?>> Запрещена</label>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="form-group">
                <label>Данные для авторизации/регистрации</label>
                <div class="radio">
                    <label><input type="radio" name="loginEnabled" value="1" <?=($loginEnabled)?'checked':''?>> Логин и e-mail</label>
                </div>
                <div class="radio">
                    <label><input type="radio" name="loginEnabled" value="0" <?=(!$loginEnabled)?'checked':''?>> Только e-mail</label>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="form-group">
                <label>Пароль при регистрации</label>
                <div class="radio">
                    <label><input type="radio" name="passwordEnabled" value="1" <?=($passwordEnabled)?'checked':''?>> Пароль вводится пользователем при регистрации</label>
                </div>
                <div class="radio">
                    <label><input type="radio" name="passwordEnabled" value="0" <?=(!$passwordEnabled)?'checked':''?>> Генерируется случайный пароль</label>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="form-group">
                <label>Авторизация после регистрации</label>
                <div class="radio">
                    <label><input type="radio" name="loginOnRegister" value="1" <?=($loginOnRegister)?'checked':''?>> Автоматически авторизовывать пользователя после регистрации</label>
                </div>
                <div class="radio">
                    <label><input type="radio" name="loginOnRegister" value="0" <?=(!$loginOnRegister)?'checked':''?>> После регистрации пользователь должен ввести логин/пароль вручную</label>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="form-group">
                <label>Письмо при регистрации</label>
                <div class="radio">
                    <label><input type="radio" name="emailOnRegister" value="1" <?=($emailOnRegister)?'checked':''?>> Отправить пользователю письмо после регистрации</label>
                </div>
                <div class="radio">
                    <label><input type="radio" name="emailOnRegister" value="0" <?=(!$emailOnRegister)?'checked':''?>> Не отправлять письмо</label>
                </div>
            </div>
        </div>
    </div>
    
    <h3>Настройка прав доступа</h3> 
    <?php 
        function showUserAccess(?string $prefix, array $accesses){
            $prefixTitle = strlen($prefix) > 0 ? $prefix . '.': null;
            foreach($accesses as $key => $access){
                if(is_array($access)){
                    showUserAccess($key, $access);
                }
                else {
                    $name = ucfirst($prefixTitle . $key);
                    $access = intval($access);
                    uiSelectAccess($name, $access, (strlen($prefix) > 0 ? 'access[' .$prefix . ']['.$key.']': 'access[' . $key . ']'));
                }
            }
        }

        
    showUserAccess(null, $accesses);    
}, 

function(){
    ?><button class='btn btn-primary'>Сохранить</button><?php 
},
"panel-default",
"user",
"user")?>
</form>