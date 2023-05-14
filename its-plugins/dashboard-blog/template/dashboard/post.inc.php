
<div class="row">
    <div class="panel panel-default">
        <div class="panel-heading clearfix">
            <?php if(isset($post)): ?>
            <div class="panel-title pull-left">#<?=$post->getId();?>&nbsp;<strong><?=$post->getTitle()?></strong></div>                
            <div class="panel-title pull-right">
                <?php if(isset($postLink) && is_string($postLink) && strlen($postLink) > 0): ?>
                <a href="<?=$postLink?>" class="btn btn-default btn-xs btn-outline"><i class="fa fa-link"></i>&nbsp;<?=__('button/open-link')?></a>
                <?php endif; ?>
                <a href="#" data-toggle="modal" data-target="#modal-delete" class="btn btn-danger btn-xs btn-outline"><i class="fa fa-trash"></i>&nbsp;<?=__('button/delete')?></a>
            </div>
            <?php else: ?>
            <div class="panel-title pull-left"><?=$this->title?></div>
            <?php endif; ?>
        </div>

        <?php if(isset($post)): ?>
        <form action="<?=$this->makeURI('/dashboard/blog/post/' . $post->getId() . '/save')?>" method="POST">                    
        <?php else: ?>
        <form action="<?=$this->makeURI('/dashboard/blog/post/new')?>" method="POST">
        <?php endif; ?>
            <div class="panel-body">
                <div class="col-12 col-lg-8">  
                    <div class="form-group">
                        <label><?=__('post-title')?></label>
                        <input class="form-control" name="title" type="text" value="<?=(isset($post)) ? $post->getTitle() : null?>" required>
                    </div>

                    <div class="form-group">
                        <label><?=__('post-alias')?></label>
                        <input class="form-control" name="alias" type="text" value="<?=(isset($post)) ? $post->getAlias() : null?>">
                        <p class="help-block"><?=__('post-alias-description')?></p>
                    </div>

                    <div class="form-group">
                        <label><?=__('post-content')?></label>
                        <textarea class="form-control" name="content" rows="10" type="text" required><?=(isset($post)) ? $post->getContent(false) : null ?></textarea>
                    </div>
                </div>

                <div class="col-12 col-lg-4">  
                    <?php if(isset($post) && isset($author)): ?>
                    <div class="form-group">
                        <label><?=__('post-author')?></label>
                        <p>
                            <?php if($author->isAuthorized()): ?>
                            <a href="<?=$this->makeURI('/dashboard/user/' . $author->get('id'))?>" class="btn btn-default"><?=$author->get('login')?></a>
                            <?php else: ?>
                            <a href="#" class="btn btn-default btn-disabled" disabled><?=__('post-author-undefined')?></a>
                            <?php endif; ?>
                        </p>
                    </div>
                    
                    <div class="form-group">
                        <label><?=__('post-timestamp')?></label>
                        <p class="help-block"><strong><?=__('post-create-time')?></strong>:<em><?=$post->getCreateTime()?></em></p>
                        <p class="help-block"><strong><?=__('post-update-time')?></strong>:<em><?=$post->getUpdateTime()?></em></p>
                    </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label><?=__('post-type')?></label>
                        <select class="form-control" name="type">
                            <option value="0" <?php if(isset($post) && $post->isDraft()): ?>selected<?php endif; ?>><?=__('post-type-draft')?></option>
                            <option value="1" <?php if(isset($post) && $post->isProduction()): ?>selected<?php endif; ?>><?=__('post-type-production')?></option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="panel-footer">
                <button type="submit" class="btn btn-primary"><?= isset($post) ? __('button/save') : __('button/post')?></button>
            </div>
        </form>
    </div>
    <!-- /.panel -->
</div>


<?php if(isset($post)): ?>
<!-- Modal -->
<div class="modal fade" id="modal-delete" tabindex="-1" role="dialog" aria-labelledby="modal-delete-label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="modal-delete-label"><?=__('blog-post-delete-title')?></h4>
            </div>
            <div class="modal-body"><?=__('blog-post-delete-confirm')?></div>
            <div class="modal-footer">
                <form action="<?=$this->makeURI('/dashboard/blog/post/' . $post->getId() . '/delete')?>" method="POST">
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
<?php endif; ?>