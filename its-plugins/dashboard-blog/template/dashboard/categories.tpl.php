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
            <?php $this->incCategories()?>
        </div>
    </div>
<?php $this->incFooter()?>