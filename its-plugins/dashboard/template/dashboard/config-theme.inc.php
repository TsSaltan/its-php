<?php
global $tpl; 
$themes = $tpl->getDesigner()->getThemes(); ?>

<div class="form-group">
    <label>Текущая тема</label>
    <select class="form-control" name="theme">
        <option value="">По умолчанию</option>
        <?php foreach($themes as $theme):?>
        <option value="<?=$theme['filename']?>" <?=($theme['current']? 'selected' : '')?>><?=$theme['name']?></option>
        <?php endforeach?>
    </select>
</div>