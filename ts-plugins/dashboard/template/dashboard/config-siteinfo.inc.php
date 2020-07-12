<?php
global $tpl; 
$icons = $tpl->getDesigner()->getAwesomeIcons(); 
?>

<div class="form-group">
	<label>Имя сайта</label>
	<div class="input-group">
	    <span class="input-group-addon input-addon-sm">sitename</span>
	    <input class="form-control" name="sitename" value="<?=$tpl->getDesigner()->getSitename()?>"/>
	</div>
</div>

<div class="form-group">
	<label>Домашняя страница (ссылка)</label>
	<div class="input-group">
	    <span class="input-group-addon input-addon-sm">sitehome</span>
   		<input class="form-control" name="sitehome" value="<?=$tpl->getDesigner()->getSitehome()?>"/>
	</div>
</div>

<div class="form-group">
	<label>Иконка</label>
	<span>(код awesome-иконки либо ссылка на изображение)</span>
	<div class="input-group">
	    <span class="input-group-addon input-addon-sm">siteicon</span>
    	<input class="form-control" name="siteicon" id="siteicon" value="<?=$tpl->getDesigner()->getSiteicon()?>"/>
	</div>

	<div class="row awesome-icons-list">
    <?php 
    foreach($icons as $icon):
    ?>
    <div class="col-md-4 col-sm-6 col-lg-3 awesome-icon" onclick="document.getElementById('siteicon').value = '<?=$icon?>'">                            
        <i class="fa fa-fw <?=$icon?>" aria-hidden="true"></i>
        <?=$icon?>
    </div>
    <?php endforeach; ?>
    </div>
</div>