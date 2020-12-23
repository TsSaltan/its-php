<?php $this->incHeader()?>
<?php $this->incNavbar()?>
<style>

</style>

    <!-- Page Content -->
    <div id="page-wrapper">
        <div class="container-fluid">

            <div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header"><?=$this->title?></h1>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading clearfix">
                            <div class="panel-title pull-left">
                                Всего в базе: <b><?=$logTotalCount?></b> записей размером в <b><?=round($logTotalSize / 1024 / 1024, 2)?> MiB</b>
                            </div>
                            <div class="pull-right">
                                <a class="btn btn-danger btn-xs btn-outline" data-toggle="modal" data-target="#logs-delete" href="#logs-delete"><i class='fa fa-trash'></i>&nbsp;Удалить логи</a>
                            </div>
                        </div>

                        <div class="panel-heading clearfix">
                            <form action="" method="GET">
                                <div class="row">
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label>Раздел</label>
                                            <select class="form-control" name="section">
                                                <option value="*">Все разделы</option>    
                                                <?php foreach ($logSections as $section): ?>
                                                <option value="<?=$section?>" <?php if($section == $logSection):?>selected<?php endif ?>><?=ucfirst($section)?></option>  
                                                <?php endforeach?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label>Минимальный уровень критичности</label>
                                            <select class="form-control" name="level">
                                                <option value="-1">Все типы логов</option>    
                                                <?php foreach ($logLevels as $levelName => $level): ?>
                                                <option value="<?=$level?>" class="log-<?=$levelName?>" <?php if($level == $logMinLevel):?>selected<?php endif ?>><?=$level?>. <?=ucfirst($levelName)?></option>  
                                                <?php endforeach?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label>&nbsp;</label>
                                            <button class="form-control btn btn-block btn-primary">Применить фильтр</button>
                                            <p class="help-block">
                                                Фильтру соответствует <b><?=$logs->getDataSize()?></b> записей
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- Modal -->
                        <div class="modal fade" id="logs-delete" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="<?=$this->makeURI('/dashboard/logs-delete/')?>" method="POST">
                                        <input type="hidden" name="action" value="clear">
                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                            <h4 class="modal-title">Удалить логи</h4>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-lg-6">
                                                    <div class="form-group">
                                                        <label>Раздел</label>
                                                        <select class="form-control" name="section">
                                                            <option value="*">Все разделы</option>    
                                                            <?php foreach ($logSections as $section): ?>
                                                            <option value="<?=$section?>" <?php if($section == $logSection):?>selected<?php endif ?>><?=ucfirst($section)?></option>  
                                                            <?php endforeach?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6">
                                                    <div class="form-group">
                                                        <label>Типы логов</label>
                                                        <select class="form-control" name="level">
                                                            <option value="-1">Все типы логов</option>    
                                                            <?php foreach ($logLevels as $levelName => $level): ?>
                                                            <option value="<?=$level?>" class="log-<?=$levelName?>" <?php if($level == $logMinLevel):?>selected<?php endif ?>><?=$level?>. <?=ucfirst($levelName)?></option>  
                                                            <?php endforeach?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <div class="form-group">
                                                        <p class="help-block">Будут удалены логи только выбранного типа и раздела   </p>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label>Выберите время и дату, до которой будет проведено удаление</label>
                                                <input class="form-control" id="clearDate" name="date" type="datetime-local" value="<?=date('Y-m-d')?>T<?=date('H:i')?>:00"/>
                                                <ul>
                                                    <li><a href="#" onclick="$('#clearDate').val('<?=date('Y-m-d', time()+60*60*24)?>T00:00:00')">Удалить записи за всё время</a></li>
                                                    <li><a href="#" onclick="$('#clearDate').val('<?=date('Y-m-d')?>T00:00:00')">Оставить записи за сегодня</a></li>
                                                    <li><a href="#" onclick="$('#clearDate').val('<?=date('Y-m')?>-01T00:00:00')">Оставить записи этого месяца</a></li>
                                                </ul>
                                            </div>

                                        </div>

                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-default" data-dismiss="modal">Отмена</button>
                                            <button type="submit" class="btn btn-danger">Удалить</button>
                                        </div>
                                    </form>
                                </div>
                                <!-- /.modal-content -->
                            </div>
                            <!-- /.modal-dialog -->
                        </div>
                        <!-- /.modal -->

                    <?php if($logs->isData()):?>
                        <div class="panel-body">
                            <div class="table-responsive">
                                <table id="table-logger" class="table table-striped table-bordered table-hover">
                                    <thead>
                                        <th width="40px">Тип</th>
                                        <th width="125px">Дата / Раздел</th>
                                        <th>Сообщение </th>
                                        <th style="min-width: 500px">Мета</th>
                                    </thead>
                                    <tbody>
                                        <?php foreach($logs->getData() as $log): ?>
                                        <?php
                                        $logMessage = null;
                                        if(isset($log['data']['message'])){
                                            $logMessage = $log['data']['message'];
                                            unset($log['data']['message']);
                                        }
                                        ?>
                                        <tr class="log-entry">
                                            <td class="log-type log-<?=$log['levelName']?>">
                                                <?=ucfirst($log['levelName'])?>
                                            </td>

                                            <td>
                                                <p class="log-date"><?=$log['date']?></p>
                                                <p class="log-section"><?=$log['section']?></p>
                                            </td>

                                            <td class="log-message">
                                                <p><?=$logMessage?></p>
                                            </td>
                                            <td>
                                            <?php if(sizeof($log['data'])>0):?>
                                                <table class="table log-meta">
                                                    <?php foreach ($log['data'] as $key => $value):?>
                                                        <tr>
                                                            <td width="100px"><?=$key?></td>
                                                            <td>
                                                                <?php if(is_array($value) || is_object($value)): ?>
                                                                <pre><?php var_export($value)?></pre>
                                                                <?php elseif(is_string($value) && strlen($value) == 0): ?>
                                                                <span>NULL</span>
                                                                <?php else: ?>
                                                                <pre><?=($value)?></pre>
                                                                <?php endif ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach?>
                                                </table>
                                            <?php endif?>
                                            </td>
                     
                                        </tr>
                                        <?php endforeach?>
                                    </tbody>
                                </table>
                            </div>
                            <!-- /.table-responsive -->
                        </div>

                        <div class="panel-footer"><?php uiPaginatorFooter($logs)?></div>
                    <?php endif?>
                    </div>
                    <!-- /.panel -->
                </div>
            </div>
        </div>
    </div>
<script type="text/javascript">
    // При клике выделяем содержимое pre
    $('.log-meta pre').dblclick(function(e){ 
        var range = document.createRange(); 
        range.selectNode($(this)[0]); 
        window.getSelection().removeAllRanges(); 
        window.getSelection().addRange(range); 
    });
</script>
<?php $this->incFooter()?>