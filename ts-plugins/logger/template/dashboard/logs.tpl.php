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
                            <div class="panel-title pull-left">
                                <b><?=$logs->getDataSize()?></b> записей
                            </div>
                            <div class="pull-right">
                                <?foreach ($logTypes as $type):?>
                                <a class="btn btn-primary btn-xs <?=($logType==$type?'':'btn-outline')?>" href="<?=$this->makeURI('/dashboard/logs/' . $type)?>"><?=ucfirst($type)?></a>
                                <?endforeach?>
                                <a class="btn btn-danger btn-xs btn-outline" data-toggle="modal" data-target="#clearConfirm" href="#clearConfirm">Очистить</a>
                            </div>
                        </div>

                        <!-- Modal -->
                        <div class="modal fade" id="clearConfirm" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="<?=$this->makeURI('/dashboard/logs-clear/')?>" method="POST">
                                        <input type="hidden" name="action" value="clear">
                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                            <h4 class="modal-title">Очистить логи</h4>
                                        </div>
                                        <div class="modal-body">
                                            <div class="form-group">
                                                <label>Выберите группу</label>
                                                <select class="form-control" name="group">
                                                    <option value="*">Все</option>
                                                    <?foreach ($logTypes as $type):?>
                                                    <option value="<?=$type?>"<?=($logType == $type) ? ' selected':''?>><?=ucfirst($type)?></option>
                                                    <?endforeach?>
                                                </select>
                                            </div>

                                            <div class="form-group">
                                                <label>Выберите время и дату, до которой будет проведено удаление</label>
                                                <input class="form-control" id="clearDate" name="date" type="datetime-local" value="<?=date('Y-m-d')?>T<?=date('H:i')?>:00"/>
                                                <ul>
                                                    <li><a href="#" onclick="$('#clearDate').val('<?=date('Y-m-d', time()+60*60*24)?>T00:00:00')">Удалить за всё время</a></li>
                                                    <li><a href="#" onclick="$('#clearDate').val('<?=date('Y-m-d')?>T00:00:00')">Оставить записи за сегодня</a></li>
                                                    <li><a href="#" onclick="$('#clearDate').val('<?=date('Y-m')?>-01T00:00:00')">Оставить записи этого месяца</a></li>
                                                </ul>
                                            </div>

                                        </div>

                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-default" data-dismiss="modal">Отмена</button>
                                            <button type="submit" class="btn btn-danger">Очистить</button>
                                        </div>
                                    </form>
                                </div>
                                <!-- /.modal-content -->
                            </div>
                            <!-- /.modal-dialog -->
                        </div>
                        <!-- /.modal -->



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

                        <div class="panel-footer"><?uiPaginatorFooter($logs)?></div>
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