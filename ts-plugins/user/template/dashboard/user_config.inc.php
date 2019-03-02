<form action="<?=$this->makeURI('/dashboard/config/user')?>" method="POST">
<?uiCollapsePanel('Настройки пользователей', function() use ($canRegister, $canSocial, $loginUsed, $accesses){
    ?>
    <h3 style="margin: 0 10px 10px 0;">Настройка авторизации</h3>
    <div class="row">
        <div class="col-lg-4">
            <div class="form-group">
                <label>Регистрация на сайте</label>
                <div class="radio">
                    <label><input type="radio" name="canRegister" value="1" <?=($canRegister)?'checked':''?>> Разрешена</label>
                </div>
                <div class="radio">
                    <label><input type="radio" name="canRegister" value="0" <?=(!$canRegister)?'checked':''?>> Запрещена</label>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="form-group">
                <label>Авторизация через социальные сети</label>
                <div class="radio">
                    <label><input type="radio" name="canSocial" value="1" <?=($canSocial)?'checked':''?>> Разрешена</label>
                </div>
                <div class="radio">
                    <label><input type="radio" name="canSocial" value="0" <?=(!$canSocial)?'checked':''?>> Запрещена</label>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="form-group">
                <label>Данные для авторизации</label>
                <div class="radio">
                    <label><input type="radio" name="loginUsed" value="1" <?=($loginUsed)?'checked':''?>> Логин и e-mail</label>
                </div>
                <div class="radio">
                    <label><input type="radio" name="loginUsed" value="0" <?=(!$loginUsed)?'checked':''?>> Только e-mail</label>
                </div>
            </div>
        </div>
    </div>
    
    <h3>Настройка прав доступа</h3> 
    <?
        function showUserAccess(?string $prefix, array $accesses){
            $prefixTitle = strlen($prefix) > 0 ? $prefix . '.': null;
            foreach($accesses as $key => $access){
                if(is_array($access)){
                    showUserAccess($key, $access);
                }
                else {
                    $name = ucfirst($prefixTitle . $key);
                    uiSelectAccess($name, $access, (strlen($prefix) > 0 ? 'access[' .$prefix . ']['.$key.']': 'access[' . $key . ']'));
                }
            }
        }

        
    showUserAccess(null, $accesses);    
}, 

function(){
    ?><button class='btn btn-primary'>Сохранить</button><?
},
"panel-default",
"user",
"user")?>
</form>