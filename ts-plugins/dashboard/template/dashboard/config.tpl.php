<?$this->incHeader()?>
<?$this->incNavbar()?>

    <!-- Page Content -->
    <div id="page-wrapper">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header"><?=$this->title?></h1>
                    <?showAlerts()?>
                </div>
            </div>
            <?$this->hook('config')?>
            <div class="row">
                    <div class="col-lg-12">
                        <div class="panel tabbed-panel panel-default">
                            <div class="panel-heading clearfix">
                                <div class="panel-title pull-left">
                                    Системный файл настроек
                                </div>
                            </div>
                            <form role="form" method="POST">
                                <div class="panel-body">
                                    <?uiAlert('Будьте осторожны при редактировании системного файла настроек!', 'warning')?>
	                                <?uiJsonEditor($systemConfigs, 'config')?>
	                            </div>
	                            <div class="panel-footer">
	                           		<button class='btn btn-success'>Сохранить</button>
	                            </div>
                           	</form>
                        </div>
                    </div>
            </div>
        </div>
    </div>

<?$this->incFooter()?>