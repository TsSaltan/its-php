<?php 
foreach($chatMessages as $message){
    if($message->getOwner()->get('id') > -1){
        $this->vars['messageAuthor'] = $message->getOwner()->get('login');
    } else {
        $this->vars['messageAuthor'] = __('unnamed');
    }
    $this->vars['messageTime'] = date('Y-m-d h:i:s', $message->getDate());
    $this->vars['messageText'] = $message->getMessage();
    $this->incMessage();
}