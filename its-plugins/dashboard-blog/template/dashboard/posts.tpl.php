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
                            <div class="panel-title pull-left"><?=__('blog-posts')?>: <b><?=__('blog-posts-num', $postsNum)?></b></div>
                            <div class="panel-title pull-right"><a href="<?=$this->makeURI('/dashboard/blog/post/new')?>" class="btn btn-success btn-xs btn-outline"><i class="fa fa-pencil"></i>&nbsp;<?=__('menu/new-blog-post')?></a></div>
                        </div>

                    <?php if($posts->isData()):?>
                        <div class="panel-body">
                            <div class="list-group">
                            <?php foreach($posts->getData() as $post): ?>
                                <a href="<?=$this->makeURI('/dashboard/blog/post/' . $post->getId())?>" class="list-group-item">
                                    <p>
                                        <span class="pull-left text-muted">#<?=$post->getId()?></span>
                                        &nbsp;
                                        <strong><?=$post->getTitle(80)?></strong>
                                        &nbsp;
                                        <?php if($post->isDraft()): ?>
                                        <span class="text-muted"><code><?=__('post-type-draft')?></code></span>
                                        <?php endif; ?>
                                        <span class="pull-right text-muted"><i class="fa fa-clock-o"></i>&nbsp;<em><?=$post->getCreateTime()?></em></span>
                                    </p>
                                    <?php $cats = $post->getCategories(); ?>
                                    <?php if(sizeof($cats) > 0): ?>
                                    <span>
                                        <?php foreach($cats as $cat): ?>
                                        <button type="button" class="btn btn-default btn-xs disabled"><?=$cat->getTitle();?></button>
                                        <?php endforeach; ?>
                                    </span>
                                    <?php endif; ?>
                                </a>
                            <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="panel-footer"><?php $this->uiPaginatorFooter($posts)?></div>
                    <?php endif?>
                    </div>
                    <!-- /.panel -->
                </div>
            </div>
        </div>
    </div>
<?php $this->incFooter()?>