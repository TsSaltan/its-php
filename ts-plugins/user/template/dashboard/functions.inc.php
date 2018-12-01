<?

function uiSelectAccess(?string $title = "", ?int $currentAccess = null, string $name = null){
	global $that;
	?>
    <div class="form-group input-group">
        <?if(strlen($title)>0):?><span class="input-group-addon" style="width:150px"><?=$title?></span><?endif?>

        <select class="form-control" name="<?=$name?>">
        	<option value="">По умолчанию</option>
        	<?foreach($that->accessList as $accessName => $accessValue):?>
            <option value="<?=$accessValue?>" <?=$currentAccess===$accessValue?'selected':''?>><?=$accessName?></option>
            <?endforeach?>
        </select>
	</div>

	<?
}