<div class="col-md-3">
	<button class="btn btn-primary btn-block" data-toggle="modal" data-target="#payssion-form">Пополнить баланс</button>
	<label class="description"><i style="opacity: 0.5"><?=$payssionDescription?></i></label>
</div>
<style type="text/css">
	.payssion-form-group {
		text-align: center;
	}

	.payssion-form-group label, 
	.payssion-form-group input {
		cursor: pointer;
	}

	.payssion-form-group label {
		display: block;
		background-repeat: no-repeat;
		background-size: contain;
		background-position-x: center;
		background-position-y: center;
		margin-bottom: 15px;
	}
	.payssion-form-group label input {
		opacity: 0.01;
	}

	.payssion-form-group label input + span {
		color: gray;
		font-size: 10px;
	} 

	.payssion-form-group label input:checked + span {
		border-bottom: 2px solid black;
		color: black;
	} 
</style>
<!-- Modal -->
<div class="modal fade" id="payssion-form" tabindex="-1" role="dialog" aria-labelledby="pay-form-label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
        	<form action="<?=$this->makeUri('/payssion/pay')?>" method="POST">
        		<input type="hidden" name="user_id" value="<?=$selectUser->get('id')?>"/>
	            <div class="modal-header">
	                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
	                <h4 class="modal-title">Пополнение счёта</h4>
	            </div>
	            <div class="modal-body">
	            	<div class="form-group">
				        <label>Введите необходимую сумму</label>
				        <div class="form-group">
					        <div class="form-group input-group">
					        	<input type="number" class="form-control" name="amount" value="0.0" min="0.1" step="0.01"/>
					            <span class="input-group-addon" style="display: table-cell;"><?=$currency?></span>
					        </div>
					    </div>
				    </div>

				    <div class="form-group">
				        <label>Введите метод оплаты</label>
				        <div class="form-group payssion-form-group">
				        	<div class="row">
				        		
					        <?php foreach ($payssionTypes as $key => $value): ?>
				        		<div class="col-lg-2"><label style="background-image: url('<?=$value['icon']?>')"><input type="radio" class="form-control" name="payment_type" value="<?=$value['type']?>" <?php if($key == 0): ?>checked<?php endif ?>/><span><?=$value['name']?></span></label></div>
				        	<?php if($key > 0 && (($key+1) % 6 == 0)): ?>
				        	</div>
				        	<div class="row">
					        <?php endif; ?>
					        <?php endforeach; ?>
				        	</div>
					    </div>
				    </div>
	            </div>
	            <div class="modal-footer">
	                <button type="button" class="btn btn-default" data-dismiss="modal">Отмена</button>
	                <button type="submit" class="btn btn-primary">Оплатить</button>
	            </div>
        	</form>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->