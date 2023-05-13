<div class="row">
    <div class="col-12 col-lg-8">
        <div class="panel panel-default">
            <div class="panel-heading clearfix">
                <?php if(isset($category)): ?>
                <div class="panel-title pull-left"><?=__('blog-subcategories')?>: <b><?=__('blog-subcategories-num', $catsNum)?></b></div>
                <?php else: ?>
                <div class="panel-title pull-left"><?=__('blog-categories')?>: <b><?=__('blog-categories-num', $catsNum)?></b></div>
                <?php endif; ?>
            </div>

            <div class="row">
                <div class="panel-body">
                    <div class="col-lg-12"> 
                        <div class="list-group">
                        <?php if($categories->isData()):?>
                        <?php foreach($categories->getData() as $cat): ?>
                            <a href="<?=$this->makeURI('/dashboard/blog/category/' . $cat->getId())?>" class="list-group-item">
                                <span class="pull-left text-muted">#<?=$cat->getId()?></span>
                                &nbsp;
                                <strong><?=$cat->getTitle()?></strong>
                                <span class="pull-right text-muted"><em><?=$cat->getAlias()?></em></span>
                            </a>
                        <?php endforeach; ?>
                        <?php endif?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="panel-footer"><?php $this->uiPaginatorFooter($categories)?></div>
        </div>
        <!-- /.panel -->
    </div>

    <div class="col-12 col-lg-4">
        <form action="<?=$this->makeURI('/dashboard/blog/create-category')?>" method="POST">   
            <input type="hidden" name="parent-id" value="<?=(isset($category) ? $category->getId() : -1)?>">
            <div class="panel panel-default">
                <div class="panel-heading clearfix">
                    <?php if(isset($category)): ?>
                    <div class="panel-title pull-left"><?=__('blog-new-subcategory')?></div>
                    <?php else: ?>
                    <div class="panel-title pull-left"><?=__('blog-new-category')?></div>
                    <?php endif; ?>
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
                    <button class="btn btn-success"><?=__('button/create')?></button>
                </div>
            </div>
        </form>
        <!-- /.panel -->
    </div>
</div>