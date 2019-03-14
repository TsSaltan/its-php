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
                                Отправка Push-сообщений
                            </div>
                        </div>

                        <div class="panel-body">
                            <h4>Добавить Push сообщение в очередь</h4>
                            <form action="<?=$this->makeURI('/dashboard/web-push-clients/queue')?>" role="form" method="POST">
                                <div class="form-group">
                                    <label>Страна получателя</label>
                                    <select class="form-control" name="country">
                                        <option value="*">Любая страна</option>
                                        <?foreach($location['country'] as $country):?>
                                        <option value="<?=$country?>"><?=$country?></option>
                                        <?endforeach?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Город получателя</label>
                                    <select class="form-control" name="city">
                                        <option value="*">Любой город</option>
                                        <?foreach($location['city'] as $city):?>
                                        <option value="<?=$city?>"><?=$city?></option>
                                        <?endforeach?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Заголовок</label>
                                    <input class="form-control" placeholder="Максимум 250 символов" maxlength="250" name="title" type="text" required>
                                </div>

                                <div class="form-group">
                                    <label>Текст сообщения</label>
                                    <textarea class="form-control" placeholder="Максимум 1000 символов" maxlength="1000" name="body" type="text" required></textarea>
                                </div>

                                <div class="form-group">
                                    <label>Изображение</label>
                                    <input class="form-control" placeholder="Прямая ссылка" maxlength="500" name="icon" type="text" id="push_icon" required>
                                    <p class="helper"><a href="https://raw.githubusercontent.com/GoogleChromeLabs/web-push-codelab/master/app/images/icon.png" onclick="$('#push_icon').val($(this).attr('href')); return false;">По умолчанию</a></p>
                                </div>

                                <div class="form-group">
                                    <label>Ссылка, которая откроется при нажатии</label>
                                    <input class="form-control" placeholder="Прямая ссылка" maxlength="500" name="link" type="text" required>
                                </div>

                                <button class="btn btn-primary">Отправить в очередь</button>
                            </form>

                            <hr/>

                            <h4>Список сообщений в очереди</h4>
                        </div>
                    </div>
                </div>

                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading clearfix">
                            <div class="panel-title pull-left">
                                База Push-клиентов
                            </div>

                            <div class="pull-right">
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
                                            <th>User-Agent</th>
                                            <th>Push-ключ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?foreach($clients->getData() as $client):
                                            $location = $client->getLocation();
                                        ?>

                                        <tr>
                                            <td><?=$client->getId()?></td>
                                            <td><?=$client->getIP()?></td>
                                            <td><?=$location->getCountry()->getName()?></td>
                                            <td><?=$location->getCity()->getName()?></td>
                                            <td><?=$client->getUserAgent()?></td>
                                            <td><pre><?=$client->getPushKeys()?></pre>
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