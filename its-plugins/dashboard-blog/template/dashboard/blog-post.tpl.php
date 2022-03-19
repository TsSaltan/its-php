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
                <div class="panel panel-default">
                    <div class="panel-heading clearfix">
                        <div class="panel-title pull-left">#<?=$post->getId();?>&nbsp;<strong><?=$post->getTitle()?></strong></div>
                    </div>

                    <form action="<?=$this->makeURI('/dashboard/post-save/' . $post->getId())?>" method="POST">                    
                        <div class="panel-body">
                            <div class="col-12 col-lg-8">  
                                <div class="form-group">
                                    <label><?=__('post-title')?></label>
                                    <input class="form-control" name="title" type="text" value="<?=$post->getTitle()?>">
                                </div>
          
                                <div class="form-group">
                                    <label><?=__('post-alias')?></label>
                                    <input class="form-control" name="title" type="text" value="<?=$post->getAlias()?>">
                                    <p class="help-block"><?=__('post-alias-description')?></p>
                                </div>
                            </div>

                            <div class="col-12 col-lg-4">  
                                <div class="col-12 col-lg-12">  
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
                                </div>
                                
                                <div class="col-12 col-lg-12">  
                                    <div class="form-group">
                                        <label><?=__('post-timestamp')?></label>
                                        <p class="help-block"><strong><?=__('post-create-time')?></strong>:<em><?=$post->getCreateTime()?></em></p>
                                        <p class="help-block"><strong><?=__('post-update-time')?></strong>:<em><?=$post->getUpdateTime()?></em></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="panel-footer"></div>
                    </form>
                </div>
                <!-- /.panel -->
            </div>
        </div>
    </div>
<script type="text/javascript">
    // При клике выделяем содержимое pre
    $('.log-meta pre').dblclick(function(e){ 
        var range = document.createRange(); 
        range.selectNode($(this)[0]); 
        window.getSelection().removeAllRanges(); 
        window.getSelection().addRange(range); 
    });
</script>
<?php $this->incFooter()?>