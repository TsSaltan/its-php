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
                <div class="col-lg-8">
                    <div class="panel panel-default">
                        <div class="panel-heading clearfix">
                            <div class="panel-title pull-left"><?=__('blog-categories')?>: <b><?=__('blog-categories-num', $catsNum)?></b></div>
                        </div>

                    <?php if($categories->isData()):?>
                        <div class="row">
                            <div class="panel-body">
                                <div class="col-lg-12"> 
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
                            </div>
                        </div>

                        <div class="panel-footer"><?php $this->uiPaginatorFooter($categories)?></div>
                    <?php endif?>
                    </div>
                    <!-- /.panel -->
                </div>

                <div class="col-lg-4">
                    <form action="<?=$this->makeURI('/dashboard/blog/create-category')?>" method="POST">   
                        <div class="panel panel-default">
                            <div class="panel-heading clearfix">
                                <div class="panel-title pull-left"><?=__('blog-new-category')?></div>
                            </div>
                            <div class="row">
                                <div class="panel-body">
                                    <div class="col-lg-12"> 
                                        <div class="form-group">
                                            <label><?=__('category-title')?></label>
                                            <input class="form-control" name="title" type="text" required>
                                        </div>
                                        <div class="form-group">
                                            <label><?=__('category-alias')?></label>
                                            <input class="form-control" name="alias" type="text" placeholder="<?=__('placeholder-optional')?>">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="panel-footer">
                                <button class="btn btn-success">Create</button>
                            </div>
                        </div>
                    </form>
                    <!-- /.panel -->
                </div>
            </div>
        </div>
    </div>
<?php $this->incFooter()?>