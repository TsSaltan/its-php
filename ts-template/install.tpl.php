<!DOCTYPE html>
<html>
<head>
	<title>Installer</title>
	<style>
		#installPane{
			margin: 10px auto;
			position: relative;
			display: block;
			max-width: 900px;
		}

		
		#nav li, #content, .button{
			background-color: rgb(238, 238, 238);
			border-radius: 5px;
			color: rgb(25, 57, 84);
			font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
			margin: 7px;
			line-height: 21px;
		}
		
		#content{
			display: block;
			font-size: 14px;
			padding: 20px;
			min-height: 300px;
			overflow: hidden;
			position: relative;
			width: 800px;
		}

		#nav{
			display: block;
			margin: 0;
			padding: 0;
		}
		
		#nav li, .button{
			float:left;
			display: block;
			padding: 14px;
			font-size: 18px;
			min-width: 100px;
			font-weight: 400;
		}
		
		#nav li.active, .button{
			background-color: rgb(33, 132, 190);
			color: white;
		}

		#buttons{
			width: 855px;
		}

		.button{
			padding: 10px 15px;
			float:right;
			text-decoration: none;
			text-align: center;
		}

		h2{
			margin:0;
			padding:0 0 10px 0;
		}

		/**
		 * Switch
		 */
		
		.switch {
		  position: relative;
		  display: inline-block;
		  width: 60px;
		  height: 34px;
		}

		.switch input { 
		  opacity: 0;
		  width: 0;
		  height: 0;
		}

		.slider {
		  position: absolute;
		  cursor: pointer;
		  top: 0;
		  left: 0;
		  right: 0;
		  bottom: 0;
		  background-color: #2196F3;
		  -webkit-transition: .4s;
		  transition: .4s;
		  box-shadow: 0 0 1px #2196F3;
		}

		.slider:before {
		  position: absolute;
		  content: "";
		  height: 26px;
		  width: 26px;
		  left: 4px;
		  bottom: 4px;
		  background-color: white;
		  -webkit-transition: .4s;
		  transition: .4s;

		  -webkit-transform: translateX(26px);
		  -ms-transform: translateX(26px);
		  transform: translateX(26px);
		}

		input:checked + .slider {
		  background-color: #ccc;
		}

		input:focus + .slider {
		  box-shadow: none;
		}

		input:checked + .slider:before {
		  -webkit-transform: translateX(0px);
		  -ms-transform: translateX(0px);
		  transform: translateX(0px);
		}

		/* Rounded sliders */
		.slider.round {
		  border-radius: 34px;
		}

		.slider.round:before {
		  border-radius: 50%;
		}

		/**
		 * Table
		 */
		
		td.pluginName{
			text-align: right;
			padding-right:10px;
			font-weight: 400;
			font-size: 16px;
		}

		tr.newLine{
			height: 40px;
    		vertical-align: bottom;
		}
	</style>
</head>
<body>
	<form action="?step=<?=(1+$step)?>" method="POST" id="installPane">
		<ul id="nav">
			<li class="<?=($step==1) ? "active" : ""?>">1. Выбор плагинов</li>
			<li class="<?=($step==2) ? "active" : ""?>">2. Установка параметров</li>
			<li class="<?=($step==3) ? "active" : ""?>">3. Завершение</li>
		</ul>

		<div id="content">
			<?switch($step){
				// 1. Плагины
				case 1:?>
					<h2>Выберите плагины, которые будут включены в работу системы</h2>
					<table>
					<?foreach ($plugins as $pluginName => $value):?>
						<tr>	
							<td class="pluginName">
							<?=ucfirst(str_replace('-', ' ', $pluginName))?>
							</td>
							<td>
								<label class="switch">
						  			<input id="plugin_<?=$pluginName?>" type="checkbox" name="param[plugins][disabled][<?=$pluginName?>]" <?=(in_array($pluginName, $disabled)?'checked':'')?>>
						  			<span class="slider round"></span>
								</label>
							</td>
						</tr>
					<?endforeach?>
					</table>
				<?break;

				// 2. Параметры
				case 2:
					$lastPart = '';
					?>
					<h2>Укажите необходимые параметры</h2>
					<table>
					<?foreach($fields as $name => $params):
						$id=md5($name);
						$part = explode('.', $name)[0];
						$label = ucfirst(str_replace('.', ' ', $name));
					?>
					<tr class="<?=($part!=$lastPart) ? "newLine": ""?>">
						<td><label for="<?=$id?>"><?=$label?></label></td>
						<td>
						<?switch ($params['type'] ?? 'text'):
							case 'error':?>
							<b style="color:rgba(255,100,100)"><?=$params['text']?></b>
							<?break;

							case 'text':
							case 'numeric':
							case 'email':
							default:
							?>
								<input id="<?=$id?>" name="param[<?=$name?>]" <?array_walk($params, function($value, $key){ ?><?=$key?>="<?=$value?>"<? })?>/>

							<?break;
							
						endswitch;
						?>
						</td>
					</tr>
					<?
					$lastPart = $part;
					endforeach?>
					</table>
				<?break;

				case 3:?>
					<h2>Установка успешно завершена!</h2>
					<p>Следующие плагины были отключены, т.к. либо вы их отметили на первом этапе установке, либо они использовали функционал отключенных плагинов, либо произошла ошибка во время установки.</p>
					<p><b>Отключенные плагины:</b><ul><li><?=implode('</li><li>', $disabled)?></li></ul></p>
					<p>Для предотвращения запуска процесса установки файл <b>install.php</b> будет переименован.</p>
				<?
			}
			?>
		</div>

		<div id="buttons">
		<?if($step < 3):?>
			<button class="button">Далее</button>
		<?else:?>
			<a href="./" class="button">На главную</a>
		<?endif?>
		</div>
	</form>
</body>
</html>