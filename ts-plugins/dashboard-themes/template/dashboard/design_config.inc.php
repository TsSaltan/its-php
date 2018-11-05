<form action="<?=$this->makeURI('/dashboard/config/theme')?>" method="POST">
<?uiCollapsePanel('Настройки внешнего вида', function() use ($current_theme, $themes){
    ?><div class="form-group">
        <label>Текущая тема</label>
        <select class="form-control" name="theme">
            <option value="">По умолчанию</option>
            <?foreach($themes as $theme):?>
                <option value="<?=$theme?>" <?=($theme==$current_theme ? 'selected' : '')?>><?=ucfirst($theme)?></option>
            <?endforeach?>
        </select>
    </div><?
}, 

function(){
    ?><button class='btn btn-primary'>Сохранить</button><?
},
"panel-default",
"eye",
"theme")?>
</form>