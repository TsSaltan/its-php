<?php
/**
 * Форма для ввода номера телефона
 * @url http://andr-04.github.io/inputmask-multi/ru.html
 */
function uiPhoneField(string $value = null, string $name = 'phone', string $id = 'phoneField'){
	global $tpl;

	?>
	<div class="form-group">

        <div class="form-group input-group">
        	<input type="text" class="form-control" id="<?=$id?>" name="<?=$name?>" value="<?=$value?>" size="25" placeholder="Введите номер телефона">
            <span class="input-group-addon" id="country_<?=$id?>">&nbsp;</span>
        </div>
        <p class="help-block">
        	<label class="checkbox-inline"><input id="checkbox_<?=$id?>" type="checkbox" checked>Маска ввода</label>
        </p>
    </div>

    <script type="text/javascript">
    	$(function(){
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
					autoUnmask: true,
					greedy: false
				},
				match: /[0-9]/,
				replace: '#',
				list: phoneInputMask,
				listKey: "mask",
				onMaskChange: function(maskObj, completed) {
					if (completed) {

						if(typeof(maskObj.name_ru) != 'undefined'){
							var hint = maskObj.name_ru;
							if (maskObj.desc_ru && maskObj.desc_ru != "") {
								hint += " (" + maskObj.desc_ru + ")";
							}
							$("#country_<?=$id?>").html(hint);
						}
						else if(typeof(maskObj.name) != 'undefined'){
							var hint = maskObj.name;
							if (typeof(maskObj.flag) != 'undefined') {
								hint += " <img src='" + maskObj.flag + "' alt='flag'/>";
							}
							$("#country_<?=$id?>").html(hint);
						}
					} else {
						$("#country_<?=$id?>").html("&nbsp;");
					}
				}
			};

			$('#checkbox_<?=$id?>').change(function() {
                if ($('#checkbox_<?=$id?>').is(':checked')) {
                	$('#<?=$id?>').inputmasks(maskOpts);
                	$("#country_<?=$id?>").css('display', 'table-cell');
                } else {
                	$('#<?=$id?>').inputmasks('remove');
                	$("#country_<?=$id?>").css('display', 'none');
                }
            });

            $('#checkbox_<?=$id?>').change();
    	});
    </script>
	<?php 
}