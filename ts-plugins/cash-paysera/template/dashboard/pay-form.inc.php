<div class="col-md-3">
	<button class="btn btn-primary btn-block" data-toggle="modal" data-target="#pay-form">Пополнить баланс</button>
	<label class="description"><i style="opacity: 0.5">via paysera</i></label>
</div>

<!-- Modal -->
<div class="modal fade" id="pay-form" tabindex="-1" role="dialog" aria-labelledby="pay-form-label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
        	<form action="<?=$this->makeUri('/dashboard/paysera')?>" method="POST">
	            <div class="modal-header">
	                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
	                <h4 class="modal-title" id="pay-form-label">Пополнение счёта</h4>
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