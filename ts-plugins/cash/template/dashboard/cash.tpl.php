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

            <?=$this->hook('cash.global')?>

            <div class="row">
                <div class="col-lg-12">
                    <div class="panel tabbed-panel panel-default">
                        <div class="panel-heading clearfix">
                            <div class="panel-title pull-left">Список операций</div>
                            <div class="pull-right">
                                
                            </div>
                        </div>
                        <div class="panel-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>Дата</th>
                                            <th>Пользователь</th>
                                            <th>Сумма</th>
                                            <th>Описание</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?foreach($cashHistory->getData() as $item):?>
                                        <?$user = $userList[$item['owner']]?>
                                        <tr>
                                            <td><?=$item['timestamp']?></td>
                                            <td><a href="<?=$this->makeURI('/dashboard/user/' . $user->get('id'))?>"><?=$user->get('login')?></a></td>
                                            <td><b style="color:<?=($item['balance'] > 0) ? "green" : ($item['balance'] == 0 ? "black" : "red")?>"><?=$item['balance']>0?'+':''?><?=$item['balance']?></b></td>
                                            <td><?=$item['description']?></td>
                     
                                        </tr>
                                        <?endforeach?>
                                    </tbody>
                                </table>
                            </div>
                            <!-- /.table-responsive -->
                        </div>
                        <div class="panel-footer">
                            <?foreach($cashHistory->getPages() as $page):?>
                            <a class="btn btn-primary <?=($page['current'] ? "disabled" : "btn-outline")?>" href="<?=$page['url']?>"><?=$page['title']?></a>
                            <?endforeach?>
                        </div>
                    </div>
                    <!-- /.panel -->
                </div>
            </div>
        </div>
    </div>

<?$this->incFooter()?>