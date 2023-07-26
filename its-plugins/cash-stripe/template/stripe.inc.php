<div class="col-md-3">
	<button class="btn btn-primary btn-block" data-toggle="modal" data-target="#stripe-form"><?=__('top-up-balance')?> <i class="fa fa-cc-stripe"></i></button>
</div>

<!-- Modal -->
<div class="modal fade" id="stripe-form" tabindex="-1" role="dialog" aria-labelledby="pay-form-label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
        	<form action="<?=$this->makeUri('/stripe-payment/checkout')?>" method="POST">
	            <div class="modal-header">
	                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
	                <h4 class="modal-title"><?=__('top-up-balance')?></h4>
	            </div>
	            <div class="modal-body">
	            	<div class="form-group">
				        <label><?=__('input-amount')?></label>
				        <div class="form-group">
					        <div class="form-group input-group">
					        	<input type="number" class="form-control" name="amount" value="0.0" min="0.1" step="0.01"/>
					            <span class="input-group-addon" style="display: table-cell;"><?=$currency?></span>
					        </div>
					    </div>
				    </div>
	            </div>
	            <div class="modal-footer">
	                <button type="button" class="btn btn-default" data-dismiss="modal"><?=__('button/cancel')?></button>
	                <button type="submit" class="btn btn-primary"><?=__('button/checkout')?></button>
	            </div>
        	</form>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->