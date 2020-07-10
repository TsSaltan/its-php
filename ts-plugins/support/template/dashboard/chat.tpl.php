<?php $this->incHeader()?>
<?php $this->incNavbar()?>
<style type="text/css">
    .chat li:nth-last-child(1){
        border-bottom: none;
    }
</style>
    <!-- Page Content -->
<div id="page-wrapper">
    <div class="container-fluid">

        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header">
                    <?php if(isset($chatRole) && $chatRole == 'operator'):?>
                    Оператор поддержки
                    <?php else:?>
                    Поддержка
                    <?php endif?>
                </h1>
            </div>
        </div>
        <?php $this->hook('support.header', [$chatId, $chatRole])?>
        <div class="row">
            <div class="col-lg-12">
                <?php 
                if($isClosed){
                    if($chatRole == 'operator'){
                        uiAlert('Данный диалог закрыт. Пользователь не может отвечать.', 'warning');
                    } else {
                        uiAlert('Данный диалог закрыт оператором. Чтоб задать вопрос, откройте новое обращение.', 'warning');
                    }
                } 
                ?>

                <div class="chat-panel panel panel-default">
                    <div class="panel-heading">
                        <i class="fa fa-comments fa-fw"></i>
                        <?=$chatTitle?>
                        <div class="btn-group pull-right">
                            <button type="button" class="btn btn-default btn-xs dropdown-toggle"
                                    data-toggle="dropdown">
                                <i class="fa fa-chevron-down"></i>
                            </button>
                            <ul class="dropdown-menu slidedown">
                                <li>
                                    <a href="#" onclick="updateMessages()">
                                        <i class="fa fa-refresh fa-fw"></i> Обновить
                                    </a>
                                </li>
                                <li>
                                    <a href="<?=$this->makeURI('/dashboard/' . ($chatRole == 'client' ? 'support' : 'operator'))?>">
                                        <i class="fa fa-sign-out fa-fw"></i> Назад к списку диалогов
                                    </a>
                                </li>
                                <?php if($chatRole == 'operator'):?>
                                <li class="divider"></li>
                                <li>
                                    <form action="<?=$this->makeURI('/dashboard/operator/close')?>" method="POST" id="closeChat">
                                        <input type="hidden" name="chat_id" value="<?=$chatId?>"/>
                                    </form>
                                    <a href="#" onclick="$('#closeChat').submit();"><i class="fa fa-close fa-fw"></i> Закрыть диалог</a>
                                </li>
                                <li>
                                    <form action="<?=$this->makeURI('/dashboard/operator/delete')?>" method="POST" id="deleteChat">
                                        <input type="hidden" name="chat_id" value="<?=$chatId?>"/>
                                    </form>
                                    <a href="#" onclick="$('#deleteChat').submit();"><i class="fa fa-trash fa-fw"></i> Удалить диалог</a>
                                </li>
                                <?php endif?>
                                <?php $this->hook('support.menu', [$chatId, $chatRole])?>
                            </ul>
                        </div>
                    </div>
                    <!-- /.panel-heading -->
                    <div class="panel-body" id="chat-container">
                        <ul class="chat" id="chat-content">
                            <?php foreach($chatMessages as $message):?>
                                <?php  $this->vars['messageAuthor'] = $message->getOwner()->get('login') ?>
                                <?php  $this->vars['messageTime'] = date('Y-m-d h:i:s', $message->getDate()) ?>
                                <?php  $this->vars['messageText'] = $message->getMessage() ?>
                                <?php  $this->incMessage() ?>
                            <?php endforeach?>
                        </ul>
                    </div>
                    <!-- /.panel-body -->
                    <div class="panel-footer">
                        <?php  if((!$isClosed && $chatRole == 'client') || $chatRole == 'operator'):?>
                        <div class="input-group">
                            <input id="message" type="text" class="form-control input-sm" placeholder="Введите текст сообщения"/>
                            <span class="input-group-btn">
                                <button class="btn btn-warning btn-sm" id="send-chat">
                                    Отправить
                                </button>
                            </span>
                        </div>
                        <?php endif?>
                    </div>
                    <!-- /.panel-footer -->
                </div>
                <!-- /.panel .chat-panel -->
            </div>
        </div>
        <?php $this->hook('support.footer', [$chatId, $chatRole])?>
    </div>
</div>

<script type="text/javascript">
    var chat_id = <?=$this->chatId?>;
    var from_id = <?=$this->fromId?>;
    var $chat_parent = $("#chat-container");
    var $chat = $("#chat-content");

    function sendMessage(){
        let $message = $('#message');
        let text = $message.val();
        $message.val('');

        <?php if($chatRole == 'operator'):?>
        tsFrame.query('POST', 'support/message-operator', {chat: chat_id, message: text}, function(){
            updateMessages();
        });
        <?php else:?>
        tsFrame.query('POST', 'support/message', {chat: chat_id, message: text}, function(data){
            updateMessages();
            if(data.error){
                document.location.reload();
            }
        });
        <?php endif?>
    }

    function updateMessages(){
        tsFrame.query('POST', 'support/updates', {chat: chat_id, from_id: from_id}, function(data){
            if(data.updates && data.html.length > 0){
                $chat.append(data.html);
                scrollChat();

                if(data.from_id){
                    from_id = data.from_id;
                }
            }
        });
    }

    function scrollChat(){
        $chat_parent.animate({ scrollTop: $chat.height()}, 300);
    }

    setInterval(function(){
        updateMessages();
    }, 5000);

    $('#send-chat').click(function(){
        sendMessage();
    });

    $('#message').keypress(function (e){
        if(e.which == 13){
            sendMessage(); 
        }
    });   

    scrollChat();
</script>
<?php $this->incFooter()?>