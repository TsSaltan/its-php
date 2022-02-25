<?php
/**
 * Основа для ввода номера телефона
 * JQuery required
 * 
 * Использование плагина:
 * - В шаблоне в месте инициализации скриптов вызвать хук $tpl->hook('template.phone-input.script')
 * - Скрипт использовать так:
 * $(selector).inputmasks({
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
		list: phoneInputMask, // phoneInputMask определен в хедере
		listKey: "mask",
		onMaskChange: function(maskObj, completed) {
			if (completed) {
				// Данные содержат или maskObj.name_ru, maskObj.desc_ru
				// Или maskObj.name, maskObj.flag (если включен плагин geodata)
			} else {
				// &nbsp;
				// Если не найден номер телефона
			}
		}
	});
 */
namespace tsframe;

use tsframe\Hook;
use tsframe\Plugins;
use tsframe\module\Geo\PhoneData;
use tsframe\module\io\Input;
use tsframe\view\HtmlTemplate;
use tsframe\view\TemplateRoot;

Hook::registerOnce('app.init', function(){
	TemplateRoot::addDefault(__DIR__ . DS . 'template');
	TemplateRoot::addDefault(CD . 'vendor' . DS . 'andr-04' . DS . 'jquery.inputmask-multi');

	Input::addFilter('phone', function(Input $input){
		$phone = $input->getCurrentData();
		$phone = str_replace(['+', ' ', '-', '(', ')', '.', "\t", '_'], '', $phone);
		
		if(is_numeric($phone)){
			$input->varProcess(function() use ($phone){
				return '+' . $phone;
			});

			return true;
		}

		return false;
	});
});

Hook::register('template.phone-input.script', function(HtmlTemplate $tpl){
	$tpl->js('js/jquery.inputmask.bundle.min.js');
	$tpl->js('js/jquery.inputmask-multi.js');

	if(Plugins::isEnabled('geodata')){
		$phonesJsonDatabase = (string) PhoneData::load(true);
	} else {
		$phonesJsonDatabase = false;
	}
	?>
	<script type="text/javascript">
   		var phoneInputMask, phoneInputDatabase = <?php if($phonesJsonDatabase !== false): echo $phonesJsonDatabase; else :?> [] <?php endif; ?>;
    	$(function(){
    		<?php if($phonesJsonDatabase === false):?> phoneInputDatabase = $.masksLoad("<?=$tpl->getURI("data/phone-codes.json")?>"); <?php endif; ?>
    		phoneInputMask = $.masksSort(phoneInputDatabase, ['#'], /[0-9]|#/, "mask");
    	});
    </script>
    <?php
});
