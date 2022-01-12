<form action="<?=$this->makeURI('/dashboard/config/vk-api/callback')?>" method="POST">
<?php 
    $vkPane = $this->uiCollapsePanel();
    $vkPane->setId('vk-callback-api');
    $vkPane->header($this->uiIcon('vk') . ' VK callback API');
    $vkPane->body(function() use ($vkRandom, $vkGroups){
        if(is_array($vkGroups) && sizeof($vkGroups) > 0):
        ?>
        <h3 style="margin: 0 10px 10px 0;">Список групп</h3>
        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover">
                <thead>
                    <tr>
                        <th>ID группы</th>
                        <th>Secret</th>
                        <th>Confirm</th>
                        <th>Callback URL</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($vkGroups as $groupId => $fields):?>
                        <tr>
                            <td><?=$groupId?></td>
                            <td><?=($fields['secret'] ?? 'null')?></td>
                            <td><?=($fields['confirm'] ?? 'null')?></td>
                            <td><input class="form-control" type="text" value="<?=$this->makeURI('/vk-callback/'. $groupId)?>" readonly></td>
                        </tr>
                    <?php endforeach?>
                </tbody>
            </table>
        </div>
        <?php endif?>

        <h3 style="margin: 0 10px 10px 0;">Добавление группы</h3>
        <div class="row">
            <div class="col-lg-12">
                <div class="form-group">
                    <label>ID группы</label>
                    <input class="form-control" type="number" min="1" step="1" name="group_id" id="vk-api-group" required placeholder="Например, 12345789">
                </div>
                
                <div class="form-group">
                    <label>Код, который должен вернуть сервер</label>
                    <input class="form-control" type="text" name="confirm_code" required placeholder="Например, sk3us9c">
                </div>

                <div class="form-group">
                    <label>Ваша ссылка для Callback API</label>
                    <input class="form-control" type="text" id="vk-api-link" readonly>
                </div>

                <div class="form-group">
                    <label>Секретный ключ</label>
                    <input class="form-control" type="text" name="secret_key" value="<?=$vkRandom?>">
                </div>
            </div>
        </div> 
        <?php   
    });

    $vkPane->footer(function(){
        ?>
        <button class="btn btn-primary">Добавить группу</button>
        <script type="text/javascript">
            $('#vk-api-group').focusout(function(){
                let baseURI = <?=json_encode($this->makeURI('/vk-callback/'))?>;

                let group = $('#vk-api-group').val();
                $('#vk-api-link').val(baseURI + group);
            });
        </script>
        <?php 
    });
  
    echo $vkPane;
?>
</form>