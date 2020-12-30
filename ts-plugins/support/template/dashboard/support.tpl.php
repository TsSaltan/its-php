<?php $this->incHeader()?>
<?php $this->incNavbar()?>
    <!-- Page Content -->
    <div id="page-wrapper">
        <div class="container-fluid">

            <div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">Поддержка</h1>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading clearfix">
                            <div class="panel-title pull-left">
                                Диалоги: <b><?=$userChats->getDataSize()?></b>
                            </div>
                        </div>

                        <!-- Modal -->
                        <div class="modal fade" id="new-support-chat" tabindex="-1" role="dialog" aria-labelledby="new-support-chat" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="<?=$this->makeURI('/dashboard/support/new')?>" method="POST">
                                        <input type="hidden" name="action" value="clear">
                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                            <h4 class="modal-title">Создать заявку в поддержку</h4>
                                        </div>
                                        
                                        <div class="modal-body">
                                            <div class="form-group">
                                                <label>Введите тему обращения</label>
                                                <input class="form-control" minlength="1" name="title" type="text" value="У меня есть вопрос..." required/>
                                            </div>
                                            <div class="form-group">
                                                <label>Введите текст сообщения</label>
                                                <textarea class="form-control" name="message" rows="3" minlength="1" required></textarea>
                                            </div>
                                        </div>

                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-default" data-dismiss="modal">Отмена</button>
                                            <button type="submit" class="btn btn-primary">Отправить сообщение</button>
                                        </div>
                                    </form>
                                </div>
                                <!-- /.modal-content -->
                            </div>
                            <!-- /.modal-dialog -->
                        </div>
                        <!-- /.modal -->

                    
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-lg-3">
                                    <p><a class="btn btn-primary btn-block" data-toggle="modal" data-target="#new-support-chat" href="#new-support-chat">Новое обращение</a></p>
                                </div>
                            </div>
                            
                            <div class="row">
                            <?php if($userChats->isData()):?>
                                <div class="col-lg-12">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Тема</th>
                                                    <th width="100px">Статус</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($userChats->getData() as $chat):?>
                                                <tr class="chat-item <?=($chat->hasNewMessages() ? 'chat-update' : '')?>" onclick="document.location.replace('<?=$this->makeURI('/dashboard/support/chat/' . $chat->getId())?>')">
                                                    <td><?=$chat->getTitle()?></td>
                                                    <td class="chat-<?=($chat->getStatus() == 1) ? 'open' : 'close'?>"><?=($chat->getStatus() == 1) ? 'Открыт' : 'Закрыт'?></td>
                                                </tr>
                                                <?php endforeach?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            <!-- /.table-responsive -->
                            <?php endif?>
                            </div>
                        </div>
                        
                        <?php if($userChats->isData()):?>
                        <div class="panel-footer"><?php $this->uiPaginatorFooter($userChats)?></div>
                        <?php endif?>
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
<?php $this->incFooter()?>