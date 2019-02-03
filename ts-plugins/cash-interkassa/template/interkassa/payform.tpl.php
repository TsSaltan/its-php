<?if(!$fieldsOnly):?><form id="payment" name="payment" method="post" action="<?=$formAction?>" enctype="utf-8"><?endif?>
	<input type="hidden" name="ik_co_id" value="<?=$cashId?>" />
	<input type="hidden" name="ik_pm_no" value="<?=$payId?>" />
	<input type="hidden" id="ik_am" name="ik_am" value="<?=$amount?>" />
	<input type="hidden" name="ik_cur" value="<?=$currency?>" />
	<input type="hidden" name="ik_desc" value="<?=$description?>" />
	<?if($amountEditable):?>
		<input type="number" name="amount" step="0.01" min="0.01" onchange="ik_am.value = this.value" onedit="ik_am.value = this.value" value="<?=$amount?>"/> <?=$currency?>
	<?endif?>
<?if(!$fieldsOnly):?><button>Оплатить</button></form><?endif?>