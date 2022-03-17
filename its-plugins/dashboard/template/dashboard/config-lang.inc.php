<?php
global $tpl; 
$langList = $tpl->vars['langList']; 
$langEditor = $tpl->vars['langEditor']; 
$langData = $tpl->vars['langData']; 
$langDataDelimeter = $tpl->vars['langDataDelimeter']; 
$langDataKeys = $tpl->vars['langDataKeys']; 
?>
<style type="text/css">
    span.delimeter {
        color:  rgb(100, 100, 200);
        padding: 0 2px;
        font-size: 14px;
    }
</style>

<?php if($langEditor): ?>
    <table class="table table-hover table-bordered">
        <thead>
            <tr>
                <th><?=__('lang-key')?></th>
                <?php foreach($langList as $lang): ?>
                <th><?=strtoupper($lang)?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
    <?php foreach($langDataKeys as $key):?>
        <tr>
            <th>
                <?php echo implode('<span class="delimeter">'.$langDataDelimeter.'</span>', explode($langDataDelimeter, $key)); ?>
            </th>
        <?php foreach($langList as $lang):?>
            <td <?php if(!isset($langData[$lang][$key])): ?>class="danger"<?php endif; ?>>                   
                <div class="input-group">
                    <span class="input-group-addon"><?=strtoupper($lang)?></span>
                    <input type="text" class="form-control" name="translate[<?=$lang?>][<?=$key?>]" placeholder="<?=__('empty')?>" value="<?=$langData[$lang][$key] ?? null ?>"> 
                </div>
            </td>
        <?php endforeach; ?>
        </tr>
    <?php endforeach; ?>

        <tr class='info'>
            <td>
                <input type="text" class="form-control" name="newkey" placeholder="<?=__('lang-new-key')?>"> 
            </td>
            <?php foreach($langList as $lang):?>
            <td>
                <div class="input-group">
                    <span class="input-group-addon"><?=strtoupper($lang)?></span>
                    <input type="text" class="form-control" name="new[<?=$lang?>]" placeholder="<?=__('empty')?>"> 
                </div>
            </td>
            <?php endforeach; ?>
        </tr>

    </table>
<?php endif; ?>