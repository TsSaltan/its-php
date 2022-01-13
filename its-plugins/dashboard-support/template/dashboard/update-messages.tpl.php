<?php foreach($chatMessages as $message):?>
    <?php  $this->vars['messageAuthor'] = $message->getOwner()->get('login') ?>
    <?php  $this->vars['messageTime'] = date('Y-m-d h:i:s', $message->getDate()) ?>
    <?php  $this->vars['messageText'] = $message->getMessage() ?>
    <?php  $this->incMessage() ?>
<?php endforeach?>