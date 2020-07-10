<?php $this->incHeader()?>
<?php $this->incNavbar()?>

    <!-- Page Content -->
    <div id="page-wrapper">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header"><?=$this->title?></h1>
                    <?php showAlerts()?>
                </div>
            </div>
            <?php $this->hook('config')?>
            
            <form method="POST">
                <?php uiCollapsePanel('Системный файл настроек', function() use ($systemConfigs){
                    uiAlert('Будьте осторожны при редактировании системного файла настроек!', 'warning');
                    uiJsonEditor($systemConfigs, 'config');
                }, function(){
                    ?><button class='btn btn-success'>Сохранить</button><?php
                },
                "panel-default",
                "file-text-o",
                "configFile"
                )?>
            </form>
        </div>
    </div>

<?php $this->incFooter()?>