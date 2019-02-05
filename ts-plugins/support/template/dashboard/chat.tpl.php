<?$this->incHeader()?>
<?$this->incNavbar()?>
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
                <h1 class="page-header">Поддержка</h1>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="chat-panel panel panel-default">
                    <div class="panel-heading">
                        <i class="fa fa-comments fa-fw"></i>
                        <?=$chatTitle?>
                        <!--div class="btn-group pull-right">
                            <button type="button" class="btn btn-default btn-xs dropdown-toggle"
                                    data-toggle="dropdown">
                                <i class="fa fa-chevron-down"></i>
                            </button>
                            <ul class="dropdown-menu slidedown">
                                <li>
                                    <a href="#">
                                        <i class="fa fa-refresh fa-fw"></i> Refresh
                                    </a>
                                </li>
                                <li>
                                    <a href="#">
                                        <i class="fa fa-check-circle fa-fw"></i> Available
                                    </a>
                                </li>
                                <li>
                                    <a href="#">
                                        <i class="fa fa-times fa-fw"></i> Busy
                                    </a>
                                </li>
                                <li>
                                    <a href="#">
                                        <i class="fa fa-clock-o fa-fw"></i> Away
                                    </a>
                                </li>
                                <li class="divider"></li>
                                <li>
                                    <a href="#">
                                        <i class="fa fa-sign-out fa-fw"></i> Sign Out
                                    </a>
                                </li>
                            </ul>
                        </div-->
                    </div>
                    <!-- /.panel-heading -->
                    <div class="panel-body" id="chat-container">
                        <ul class="chat" id="chat-content">
                            <?foreach($chatMessages as $message):?>
                                <? $this->vars['messageAuthor'] = $message->getOwner()->get('login') ?>
                                <? $this->vars['messageTime'] = date('Y-m-d h:i:s', $message->getDate()) ?>
                                <? $this->vars['messageText'] = $message->getMessage() ?>
                                <? $this->incMessage() ?>
                            <?endforeach?>
                        </ul>
                    </div>
                    <!-- /.panel-body -->
                    <div class="panel-footer">
                        <div class="input-group">
                            <input id="message" type="text" class="form-control input-sm" placeholder="Введите текст сообщения"/>
                            <span class="input-group-btn">
                                <button class="btn btn-warning btn-sm" id="send-chat">
                                    Отправить
                                </button>
                            </span>
                        </div>
                    </div>
                    <!-- /.panel-footer -->
                </div>
                <!-- /.panel .chat-panel -->
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    var chat_id = <?=$this->chatId?>;
    var $chat_parent = $("#chat-container");
    var $chat = $("#chat-content");

    function sendMessage(){
        let $message = $('#message');
        let text = $message.val();
        $message.val('');

        tsFrame.query('POST', 'support/message', {chat: chat_id, message: text}, function(){
            updateMessages();
        });
    }

    function updateMessages(){
        tsFrame.query('POST', 'support/updates', {chat: chat_id}, function(data){
            if(data.updates && data.html.length > 0){
                $chat.append(data.html);
                scrollChat();
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
<?$this->incFooter()?>