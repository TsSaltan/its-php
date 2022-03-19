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
                        <div class="panel-title pull-left"><?=$this->title?></div>
                    </div>

                    <form action="<?=$this->makeURI('/dashboard/blog/post/new')?>" method="POST">                    
                        <div class="panel-body">
                            <div class="col-lg-12"> 
                                <div class="form-group">
                                    <label><?=__('post-title')?></label>
                                    <input class="form-control" name="title" type="text" required>
                                </div>
                            </div>
     
                            <div class="col-lg-8"> 
                                <div class="form-group">
                                    <label><?=__('post-alias')?></label>
                                    <input class="form-control" name="alias" type="text" required>
                                    <p class="help-block"><?=__('post-alias-description')?></p>
                                </div>
                            </div>

                            <div class="col-lg-4">                                    
                                <div class="form-group">
                                    <label><?=__('post-type')?></label>
                                    <select class="form-control" name="type">
                                        <option value="0"><?=__('post-type-draft')?></option>
                                        <option value="1" selected><?=__('post-type-production')?></option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-lg-12"> 
                                <div class="form-group">
                                    <label><?=__('post-content')?></label>
                                    <textarea class="form-control" name="content" rows="10" type="text" required></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="panel-footer"><button type="submit" class="btn btn-primary"><?=__('button/post')?></button></div>
                    </form>
                </div>
                <!-- /.panel -->
            </div>
        </div>
    </div>
<?php $this->incFooter()?>