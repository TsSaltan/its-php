<?php $this->incHeader()?>
<?php $this->incNavbar()?>

    <!-- Page Content -->
    <div id="page-wrapper">
        <div class="container-fluid">
            
			<div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">Платёжные коды</h1>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
        			<?php showAlerts()?>
                    <div class="panel panel-default">
                        <div class="panel-heading clearfix">
                            <div class="panel-title pull-left">Добавить код</div>
                        </div>

                        <div class="panel-body">
                        	<form action="<?=$this->makeURI('/dashboard/cash-codes')?>" method="POST">
                            	<div class="form-group">
                                	<label>Введите сумму для кода</label>
                                	<input class="form-control" value="0.0" min="0" step="0.05" type="number" name="balance">
                        	    </div>
                        	    <button class="btn btn-primary">Создать код</button>
                        	</form>
                        </div>
                    </div>
                </div>
                

                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading clearfix">
                            <div class="panel-title pull-left">Список платёжных кодов</div>
                            <div class="pull-right">
                                
                            </div>
                        </div>
                        <div class="panel-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>Код</th>
                                            <th>Сумма</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($codes as $code):?>
                                        <tr>
                                            <td><?=$code['code']?></td>
                                            <td><?=$code['balance']?></td>
                                        </tr>
                                        <?php endforeach?>
                                    </tbody>
                                </table>
                            </div>
                            <!-- /.table-responsive -->
                        </div>
                    </div>
                    <!-- /.panel -->
                </div>
            </div>
        </div>
    </div>

<?php $this->incFooter()?>