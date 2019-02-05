<li class="clearfix">
    <div class="chat-body clearfix">
        <div class="header">
            <strong class="primary-font"><?=$this->vars['messageAuthor']?></strong>
            <small class="pull-right text-muted">
                <i class="fa fa-clock-o fa-fw"></i> <?=$this->vars['messageTime']?>
            </small>
        </div>
        <p><?=$this->vars['messageText']?></p>
    </div>
</li>