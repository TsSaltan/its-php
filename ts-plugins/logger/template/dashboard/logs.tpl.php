<?$this->incHeader()?>
<?$this->incNavbar()?>
<style>
    table.meta pre{
        max-height: 100px; overflow-y: auto;
    }
</style>

    <!-- Page Content -->
    <div id="page-wrapper">
        <div class="container-fluid">

            <div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header"><?=$this->title?></h1>
                </div>
            </div>

            <?=$this->hook('referrer')?>

            <div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading clearfix">
                            <div class="panel-title pull-left">Логи</div>
                            <div class="pull-right">
                                <?foreach ($logTypes as $type):?>
                                <a class="btn btn-primary btn-xs <?=($logType==$type?'':'btn-outline')?>" href="<?=$this->makeURI('/dashboard/logs/' . $type)?>"><?=ucfirst($type)?></a>
                                <?endforeach?>
                            </div>
                        </div>
                    <?if($logs->isData()):?>
                        <div class="panel-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>Дата</th>
                                            <th>Сообщение</th>
                                            <th>Мета</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?foreach($logs->getData() as $log):?>
                                        <?
                                        $logMessage = null;
                                        if(isset($log['data']['message'])){
                                            $logMessage = $log['data']['message'];
                                            unset($log['data']['message']);
                                        }
                                        ?>
                                        <tr>
                                            <td><?=$log['date']?></td>
                                            <td><?=$logMessage?></td>
                                            <td>
                                                <?if(sizeof($log['data'])>0):?>
                                                    <table class="table meta">
                                                        <?foreach ($log['data'] as $key => $value):?>
                                                            <tr>
                                                                <td width="100px"><?=$key?></td>
                                                                <td><pre><?=(is_string($value)?$value:var_export($value, true))?></pre></td>
                                                            </tr>
                                                        <?endforeach?>
                                                    </table>
                                                <?endif?>
                                            </td>
                     
                                        </tr>
                                        <?endforeach?>
                                    </tbody>
                                </table>
                            </div>
                            <!-- /.table-responsive -->
                        </div>

                        <div class="panel-footer">
                            <?foreach($logs->getPages() as $page):?>
                            <a class="btn btn-primary <?=($page['current'] ? "disabled" : "btn-outline")?>" href="<?=$page['url']?>"><?=$page['title']?></a>
                            <?endforeach?>
                        </div>
                    <?endif?>
                    </div>
                    <!-- /.panel -->
                </div>
            </div>
        </div>
    </div>
<script type="text/javascript">
    // При клике выделяем содержимое pre
    $('.meta pre').click(function(e){ 
        var range = document.createRange(); 
        range.selectNode($(this)[0]); 
        window.getSelection().removeAllRanges(); 
        window.getSelection().addRange(range); 
    });
</script>
<?$this->incFooter()?>