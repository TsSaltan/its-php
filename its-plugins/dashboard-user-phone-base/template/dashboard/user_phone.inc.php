<form role="form" method="POST" action="<?=$this->makeURI('/dashboard/user/' . $this->selectUser->get('id'). '/edit/phone')?>">
	<?php uiPhoneField($this->selectUser->getMeta()->get('phone'), 'phone')?>
	<button class="btn btn-success">Сохранить</button>
</form>