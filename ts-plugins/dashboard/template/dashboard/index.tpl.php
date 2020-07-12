<?php $this->incHeader(); ?>
<?php $this->incNavbar(); ?>

    <!-- Page Content -->
    <div id="page-wrapper">
        <div class="container-fluid">
        	<?php showAlerts()?>
            <?php $this->hook('index')?>

        </div>
    </div>

<?php $this->incFooter(); ?>