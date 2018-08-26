<?php

function uiPhoneField(string $value = null, string $name = 'phone', string $id = 'phoneField'){
	global $that;

	?>
	<div class="form-group">
        <input type="text" class="form-control" id="<?=$id?>" name="<?=$name?>" value="<?=$value?>" size="25" placeholder="Введите номер телефона">
        <p class="help-block" for="<?='mask_' . $id?>">&nbsp;</p>
    </div>

    <script type="text/javascript">
    	$(function(){
    		var maskList = $.masksSort($.masksLoad("<?=$that->getURI("data/phone-codes.json")?>"), ['#'], /[0-9]|#/, "mask");
			var maskOpts = {
				inputmask: {
					definitions: {
						'#': {
							validator: "[0-9]",
							cardinality: 1
						}
					},
					//clearIncomplete: true,
					showMaskOnHover: false,
					autoUnmask: true
				},
				match: /[0-9]/,
				replace: '#',
				list: maskList,
				listKey: "mask",
				onMaskChange: function(maskObj, completed) {
					if (completed) {
						var hint = maskObj.name_ru;
						if (maskObj.desc_ru && maskObj.desc_ru != "") {
							hint += " (" + maskObj.desc_ru + ")";
						}
						$("[for=<?='mask_' . $id?>]").html(hint);
					} else {
						$("[for=<?='mask_' . $id?>]").html("&nbsp;");
					}
					//$(this).attr("placeholder", $(this).inputmask("getemptymask"));
				}
			};

			$('#<?=$id?>').inputmasks(maskOpts);
    	});
    </script>
	<?
}