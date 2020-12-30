<?php $this->incHeader()?>
<?php $this->incNavbar()?>

    <!-- Page Content -->
    <div id="page-wrapper">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header"><?=$this->title?></h1>
                    <?php $this->uiAlerts(); ?>
                </div>
            </div>
            <?php $this->hook('config')?>
            
            <form action="<?=$this->makeURI('/dashboard/config/theme')?>" method="POST">
                <?php uiCollapsePanel('Настройки внешнего вида', function(){ 
                    $this->inc('config-theme');
                }, 

                function(){
                ?><button class='btn btn-primary'>Сохранить</button><?php
                },
                
                "panel-default",
                "eye",
                "theme"); ?>
            </form>            
            
            <form action="<?=$this->makeURI('/dashboard/config/siteinfo')?>" method="POST">
                <?php uiCollapsePanel('Изменить информацию о сайте', function(){ 
                    $this->inc('config-siteinfo');
                }, 

                function(){
                ?><button class='btn btn-primary'>Сохранить</button><?php
                },
                
                "panel-default",
                "info-circle",
                "siteinfo"); ?>
            </form>

            <form method="POST">
                <?php uiCollapsePanel('Системный файл настроек', function() use ($systemConfigs){
                    $this->uiAlert('Будьте осторожны при редактировании системного файла настроек!', 'warning', false);
                    $this->uiJsonEditor($systemConfigs, 'config');
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