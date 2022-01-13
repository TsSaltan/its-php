<?php $this->incHeader()?>
<?php $this->incNavbar()?>

    <!-- Page Content -->
    <div id="page-wrapper">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header"><?=$this->title?></h1>
                    <?php echo $this->uiAlerts(); ?>
                </div>
            </div>
            <div class="row">
                <form id="setmode_form" action="<?=$this->makeURI('/dashboard/config/setmode')?>" method="POST">  
                    <div class="col-lg-3">
                        <div class="form-group input-group">
                            <span class="input-group-addon" style="width:50%">Dev mode</span>
                            <select class="form-control" name="dev_mode" onchange="$('#setmode_form').submit()">
                                <option value="0">False</option>
                                <option value="1" <?php if($dev_mode === true){ echo "selected"; }?>>True</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-lg-3">
                        <div class="form-group input-group">
                            <span class="input-group-addon" style="width:50%">Install mode</span>
                            <select class="form-control" name="install_mode" onchange="$('#setmode_form').submit()">
                                <option value="0">False</option>
                                <option value="1"<?php if($install_mode === true){ echo "selected"; }?>>True</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            
            <?php $this->hook('config')?>
            
            <form action="<?=$this->makeURI('/dashboard/config/theme')?>" method="POST">
                <?php 
                $themePane = $this->uiCollapsePanel('default');
                $themePane->setId('theme');
                $themePane->header($this->uiIcon('eye') . '&nbsp;Настройки внешнего вида');
                $themePane->body(function(){ 
                    $this->inc('config-theme');
                });
                $themePane->footer(function(){ ?><button class='btn btn-primary'>Сохранить</button><?php });
                echo $themePane;
                ?>
            </form>            
            
            <form action="<?=$this->makeURI('/dashboard/config/siteinfo')?>" method="POST">
                <?php 
                $siteinfoPane = $this->uiCollapsePanel('default');
                $siteinfoPane->setId('siteinfo');
                $siteinfoPane->header($this->uiIcon('info-circle') . '&nbsp;Изменить информацию о сайте');
                $siteinfoPane->body(function(){ 
                    $this->inc('config-siteinfo');
                });
                $siteinfoPane->footer(function(){ ?><button class='btn btn-primary'>Сохранить</button><?php });
                echo $siteinfoPane;
                ?>
            </form>

            <form method="POST">
                <?php 
                $cfgPane = $this->uiCollapsePanel('primary');
                $cfgPane->setId('configFile');
                $cfgPane->header($this->uiIcon('file-text-o') . ' Системный файл настроек');
                $cfgPane->body(function() use ($systemConfigs){ 
                    echo $this->uiAlert('Будьте осторожны при редактировании системного файла настроек!', 'info', false);
                    echo $this->uiJsonEditor($systemConfigs, 'config');
                });
                $cfgPane->footer(function(){ ?><button class='btn btn-primary'>Сохранить</button><?php });
                echo $cfgPane;
                ?>
            </form>
        </div>
    </div>

<?php $this->incFooter()?>