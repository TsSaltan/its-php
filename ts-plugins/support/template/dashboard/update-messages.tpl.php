<?foreach($chatMessages as $message):?>
    <? $this->vars['messageAuthor'] = $message->getOwner()->get('login') ?>
    <? $this->vars['messageTime'] = date('Y-m-d h:i:s', $message->getDate()) ?>
    <? $this->vars['messageText'] = $message->getMessage() ?>
    <? $this->incMessage() ?>
<?endforeach?>