<!DOCTYPE html>
<html>
<head>
	<title>Installer</title>
</head>
<body>
	<h2>ts-framework installer</h2>
	<form action="" method="POST">
		<table>

		<tr>
			<td valign="top">Disabled plugins:</td>
			<td>
				<ul>
				<?foreach ($plugins as $pluginName => $value):?>
					<li><label for="plugin_<?=$pluginName?>"><?=$pluginName?></label><input id="plugin_<?=$pluginName?>" type="checkbox" name="param[plugins][disabled][<?=$pluginName?>]"/></li>
				<?endforeach?>
				</ul>
			</td>
		</tr>


		<?foreach($fields as $name => $params):?>
		<?$id=md5($name)?>
		<tr>
			<td><label for="<?=$id?>"><?=ucfirst(str_replace('.', ' ', $name))?></label>: </td>
			<td>
			<?switch ($params['type'] ?? 'text'):
				case 'text':
				case 'numeric':
				?>
					<input id="<?=$id?>" name="param[<?=$name?>]" <?array_walk($params, function($value, $key){ ?><?=$key?>="<?=$value?>"<? })?>/>

				<?break;
				
				case 'error':?>
				<b style="color:rgba(255,100,100)"><?=$params['text']?></b>
				<?break;
				default:
					# code...
					break;

			endswitch;
			?>
			</td>
		</tr>
		<?endforeach?>
		</table>
		<button>Install</button>
	</form>
</body>
</html>