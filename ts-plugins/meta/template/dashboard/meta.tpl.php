<?php $this->incHeader()?>
<?php $this->incNavbar()?>
    <style>
        .meta-editor .item-header:nth-child(n+2) th{
            padding-top:35px;
        }

        .meta-editor .key-label{
            width: 170px;
            vertical-align: middle;
        }
    </style>   
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
                            <div class="panel-title pull-left">
                                <ul class="nav nav-pills">
                                    <li class="active"><a href="#meta-editor" data-toggle="tab">Редактор данных</a></li>
                                    <li><a href="#meta-add" data-toggle="tab">Добавить запись</a></li>
                                </ul>
                            </div>
                            <div class="panel-title pull-right"><form action=""><input class="form-control" name='filter' placeholder="Фильтр" value="<?=$filter?>"></form></div>
                        </div>
                        <div class="panel-body">
                            <div class="tab-content">
                                <div class="tab-pane fade in active" id="meta-editor">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover meta-editor">
                                           <?php foreach($metaData->getData() as $m):?>
                                           <tr class='item-header'>     
                                                <th colspan="2">
                                                    <code>
                                                    <?php $parts = explode('_',  $m['parent'])?>
                                                    <?php for($i = 0; $i < sizeof($parts); ++$i):?>
                                                        <?php $part = implode('_', array_slice($parts, 0, $i+1))?>
                                                        <a class="" href="?filter=<?=$part?>"><?=$parts[$i]?></a>
                                                        <?php if(isset($parts[$i+1])):?> &gt; <?php endif?>
                                                    <?php endfor?>
                                                    </code>
                                                </th>
                                            </tr>
                                                <?php foreach ($m['data'] as $key => $value):?>
                                                <tr>  
                                                    <th class="key-label"><?=$key?></th>    
                                                    <td>
                                                        <input class="form-control meta-field" data-parent="<?=$m['parent']?>" data-key="<?=$key?>" value="<?=$value?>" placeholder="delete"/>
                                                    </td>    
                                                </tr>    
                                                <?php endforeach?>
                                           <?php endforeach?>
                                        </table>
                                    </div>
                                    <!-- /.table-responsive -->

                                    <?php if($metaData->hasPages()):?>
                                        <div class="well">
                                            <?php foreach($metaData->getPages() as $page):?>
                                            <a class="btn btn-primary <?=($page['current'] ? "disabled" : "btn-outline")?>" href="<?=$page['url']?>"><?=$page['title']?></a>
                                            <?php endforeach?>
                                        </div>
                                    <?php endif?>
                                </div>

                                <div class="tab-pane fade" id="meta-add">
                                    <form role="form" onsubmit="return addMeta(this)">
                                        <div class="form-group">
                                            <label>Parent</label>
                                            <input class="form-control" name='parent' value='<?=$filter?>' required/>
                                        </div>
                                        <div class="form-group">
                                            <label>Key</label>
                                            <input class="form-control" name='key' required/>
                                        </div>
                                        <div class="form-group">
                                            <label>Value</label>
                                            <input class="form-control" name='value' required/>
                                        </div>
                                        <button class="btn btn-primary">Добавить</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /.panel -->
                </div>
            </div>
        </div>
    </div>
<script type="text/javascript">
    function addMeta(form){
        let $form = $(form);
        var parent = document.querySelector('input[name=parent]').value;
        let key = document.querySelector('input[name=key]').value;
        let value = document.querySelector('input[name=value]').value;

        saveMeta(
            parent, key, value, 
            function(){
                document.location.replace('?filter=' + parent);
            }
        );
    }

    function saveMeta(parent, key, value, callback){
        let data = {key: key, parent: parent, value: value};
        console.log(data);
        tsFrame.query('POST', 'meta', data, callback);
    }
    
    $('.meta-field').blur(function(e){ 
        let $field = $(e.target);
        return saveMeta($field.attr('data-parent'), $field.attr('data-key'), $field.val());
    });
</script>
<?php $this->incFooter()?>