<?php $this->incHeader()?>
<?php $this->incNavbar()?>

    <!-- Page Content -->
    <div id="page-wrapper">
        <div class="container-fluid">

            <div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">Оператор поддержки</h1>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading clearfix">
                            <div class="panel-title pull-left">
                                Всего заявок: <b><?=$chats->getDataSize()?></b>
                            </div>
                        </div>
                    
                        <div class="panel-body">                           
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered table-hover">
                                            <thead>
                                                <tr>
                                                    <th width="150px" align="center">Автор</th>
                                                    <th>Тема</th>
                                                    <th width="100px">Статус</th>
                                                    <th width="100px">Действия</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($chats->getData() as $chat):?>
                                                <tr class="chat-item <?=((!$chat->isAnswered() && $chat->getStatus() > 0) ? 'chat-danger' : '')?>" onclick="document.location.replace('<?=$this->makeURI('/dashboard/operator/chat/' . $chat->getId())?>')">
                                                    <td>
                                                        <?php 
                                                        $owner = $chat->getOwner();
                                                        if($owner->get('id') > -1):
                                                    ?>
                                                        <a href="<?=$this->makeURI('/dashboard/user/' . $owner->get('id'))?>" class='btn btn-default btn-block' target="_blank"><?=$owner->get('login')?></a>
                                                    <?php 
                                                        else: 
                                                    ?>
                                                        <a href="#" class='btn btn-default btn-block btn-disabled'><?=__('unnamed')?></a>
                                                    <?php 
                                                        endif; 
                                                    ?>
                                                    </td>
                                                    <td><?=$chat->getTitle()?></td>
                                                    <?php if($chat->getStatus() == 1):?>
                                                        <td class="chat-open">Открыт</td>
                                                        <td>
                                                            <form action="<?=$this->makeURI('/dashboard/operator/close')?>" method="POST">
                                                                <input type="hidden" name="chat_id" value="<?=$chat->getId()?>"/>
                                                                <button class="btn btn-warning"><i class="fa fa-close"></i> Закрыть</button></td>
                                                            </form>
                                                    <?php else:?>
                                                        <td class="chat-close">Закрыт</td>
                                                        <td>
                                                            <form action="<?=$this->makeURI('/dashboard/operator/delete')?>" method="POST">
                                                                <input type="hidden" name="chat_id" value="<?=$chat->getId()?>"/>
                                                                <button class="btn btn-danger"><i class="fa fa-trash"></i> Удалить</button>
                                                            </form>
                                                        </td>
                                                    <?php endif?>
                                                </tr>
                                                <?php endforeach?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            <!-- /.table-responsive -->
                            </div>
                        </div>
                        
                        <div class="panel-footer"><?php $this->uiPaginatorFooter($chats)?></div>
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