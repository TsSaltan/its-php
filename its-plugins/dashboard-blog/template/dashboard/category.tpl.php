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
                <div class="col-12 col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading clearfix">
                            <div class="panel-title pull-left">#<?=$category->getId();?>&nbsp;<strong><?=$category->getTitle()?></strong></div>
                            <div class="panel-title pull-right">
                                <a href="#" data-toggle="modal" data-target="#modal-delete" class="btn btn-danger btn-xs btn-outline"><i class="fa fa-trash"></i>&nbsp;<?=__('button/delete')?></a>
                            </div>
                        </div>

                        <form action="<?=$this->makeURI('/dashboard/blog/category/' . $category->getId())?>" method="POST">                    
                            <div class="panel-body">
                                <div class="col-12 col-lg-12">  
                                    <div class="form-group">
                                        <label><?=__('category-title')?></label>
                                        <input class="form-control" name="title" type="text" value="<?=$category->getTitle()?>">
                                    </div>
              
                                    <div class="form-group">
                                        <label><?=__('category-alias')?></label>
                                        <input class="form-control" name="alias" type="text" value="<?=$category->getAlias()?>">
                                        <p class="help-block"><?=__('category-alias-description')?></p>
                                    </div>

                                    <div class="form-group">
                                        <label><?=__('category-parent')?></label>
                                        <select class="form-control" name="parent-id">
                                            <option value="-1"><?=__('no-parent-category')?></option>

                                        <?php foreach($allCategories as $cat): ?>
                                            <?php if($cat->getId() == $category->getId()) continue;?>
                                            <option value="<?=$cat->getId()?>"<?=($cat->getId() == $category->getParentId()) ? ' selected' : ''?>>[#<?=$cat->getId()?>] <?=$cat->getTitle()?></option>
                                        <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                            </div>

                            <div class="panel-footer"><button type="submit" class="btn btn-primary"><?=__('button/save')?></button></div>
                        </form>
                    </div>
                    <!-- /.panel -->
                </div>
            </div>

            <?php $this->incCategories()?>
        </div>
    </div>


    <!-- Modal -->
    <div class="modal fade" id="modal-delete" tabindex="-1" role="dialog" aria-labelledby="modal-delete-label" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="modal-delete-label"><?=__('blog-category-delete-title')?></h4>
                </div>
                <div class="modal-body"><?=__('blog-category-delete-confirm')?></div>
                <div class="modal-footer">
                    <form action="<?=$this->makeURI('/dashboard/blog/delete-category/' . $category->getId())?>" method="POST">
                        <button type="button" class="btn btn-default" data-dismiss="modal"><?=__('button/cancel')?></button>
                        <button type="submit" class="btn btn-danger"><?=__('button/delete')?></button>
                    </form>
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
    <!-- /.modal -->
<?php $this->incFooter()?>