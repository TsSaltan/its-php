<?php 

function uiSelectAccess(?string $title = "", ?int $currentAccess = null, string $name = null){
	global $that;
	?>
    <div class="form-group input-group">
        <?php if(strlen($title)>0):?><span class="input-group-addon" style="width:150px"><?=$title?></span><?php endif?>

        <select class="form-control" name="<?=$name?>">
        	<option value="">По умолчанию</option>
        	<?php foreach($that->accessList as $accessName => $accessValue):?>
            <option value="<?=$accessValue?>" <?=$currentAccess===$accessValue?'selected':''?>><?=$accessName?></option>
            <?php endforeach?>
        </select>
	</div>

	<?php 
}