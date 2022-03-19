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
                            <div class="panel-title pull-right"><a href="<?=$this->makeURI('/dashboard/blog/post/new')?>" class="btn btn-success btn-xs"><?=__('menu/new-blog-post')?></a></div>
                        </div>

                    <?php if($posts->isData()):?>
                        <div class="panel-body">
                            <div class="list-group">
                            <?php foreach($posts->getData() as $post): ?>
                                <a href="<?=$this->makeURI('/dashboard/blog/post/' . $post->getId())?>" class="list-group-item">
                                    <span class="pull-left text-muted">#<?=$post->getId()?></span>
                                    &nbsp;
                                    <strong><?=$post->getTitle()?></strong>
                                    <span class="pull-right text-muted"><em><?=$post->getCreateTime()?></em></span>
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