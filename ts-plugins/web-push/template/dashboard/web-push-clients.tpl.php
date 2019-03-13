<?$this->incHeader()?>
<?$this->incNavbar()?>
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
                                Количество записей: <b><?=$clients->getDataSize()?></b>
                            </div>
                        </div>

                    <?if($clients->isData()):?>
                        <div class="panel-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered table-hover clients">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>IP</th>
                                            <th>Страна</th>
                                            <th>Город</th>
                                            <th>Push-ключ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?foreach($clients->getData() as $client):?>

                                        <tr>
                                            <td><?=$client['id']?></td>
                                            <td><?=$client['ip']?></td>
                                            <td><?=$client['country']?></td>
                                            <td><?=$client['city']?></td>
                                            <td><pre><?=json_encode(['endpoint' => $client['endpoint'], 'keys' => ['p256dh' => $client['p256dh'], 'auth' => $client['auth']]])?></pre>
                                            </td>
                     
                                        </tr>
                                        <?endforeach?>
                                    </tbody>
                                </table>
                            </div>
                            <!-- /.table-responsive -->
                        </div>

                        <div class="panel-footer"><?uiPaginatorFooter($clients)?></div>
                    <?endif?>
                    </div>
                    <!-- /.panel -->
                </div>
            </div>
        </div>
    </div>
<script type="text/javascript">
    // При клике выделяем содержимое pre
    $('.clients pre').click(function(e){ 
        var range = document.createRange(); 
        range.selectNode($(this)[0]); 
        window.getSelection().removeAllRanges(); 
        window.getSelection().addRange(range); 
    });
</script>
<?$this->incFooter()?>