<?php $this->incHeader()?>
<?php $this->incNavbar()?>
    <!-- Page Content -->
    <div id="page-wrapper">
        <div class="container-fluid">

            <div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header"><?=$this->title?></h1>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading clearfix">
                            <div class="panel-title pull-left"><?=__('blog-categories')?>: <b><?=__('blog-categories-num', $catsNum)?></b></div>
                            <div class="panel-title pull-right"><a href="<?=$this->makeURI('/dashboard/blog/category/new')?>" class="btn btn-success btn-xs btn-outline"><i class="fa fa-pencil"></i>&nbsp;<?=__('menu/new-post-category')?></a></div>
                        </div>

                    <?php if($categories->isData()):?>
                        <div class="panel-body">
                            <div class="list-group">
                            <?php foreach($categories->getData() as $cat): ?>
                                <a href="<?=$this->makeURI('/dashboard/blog/category/' . $cat->getId())?>" class="list-group-item">
                                    <span class="pull-left text-muted">#<?=$cat->getId()?></span>
                                    &nbsp;
                                    <strong><?=$cat->getTitle()?></strong>
                                    <span class="pull-right text-muted"><em><?=$cat->getAlias()?></em></span>
                                </a>
                            <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="panel-footer"><?php $this->uiPaginatorFooter($categories)?></div>
                    <?php endif?>
                    </div>
                    <!-- /.panel -->
                </div>
            </div>
        </div>
    </div>
<?php $this->incFooter()?>