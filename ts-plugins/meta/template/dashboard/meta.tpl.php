<?$this->incHeader()?>
<?$this->incNavbar()?>
    <style>
        .meta-editor .item-header:nth-child(n+2) th{
            /*border-top:20px solid transparent;*/
            padding-top:35px;
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
                            <div class="panel-title pull-left" style="padding-top:6px">Редактор</div>
                            <div class="panel-title pull-right"><form action=""><input class="form-control" name='filter' placeholder="Фильтр" value="<?=$filter?>"></form></div>
                        </div>
                    <?if($metaData->isData()):?>
                        <div class="panel-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover meta-editor">
                                   <?foreach($metaData->getData() as $m):?>
                                   <tr class='item-header'>     
                                        <th colspan="2">
                                            <code>
                                            <?$parts = explode('_',  $m['parent'])?>
                                            <?for($i = 0; $i < sizeof($parts); ++$i):?>
                                                <?$part = implode('_', array_slice($parts, 0, $i+1))?>
                                                <a class="" href="?filter=<?=$part?>"><?=$parts[$i]?></a>
                                                <?if(isset($parts[$i+1])):?> &gt; <?endif?>
                                            <?endfor?>
                                            </code>
                                        </th>
                                    </tr>
                                        <?foreach ($m['data'] as $key => $value):?>
                                        <tr>  
                                            <th style="padding-left:15px" width="120px"><?=$key?></th>    
                                            <td>
                                                <input class="form-control meta-field" data-parent="<?=$m['parent']?>" data-key="<?=$key?>" value="<?=$value?>" placeholder="delete"/>
                                            </td>    
                                        </tr>    
                                        <?endforeach?>
                                   <?endforeach?>
                                </table>
                            </div>
                            <!-- /.table-responsive -->
                        </div>

                        <div class="panel-footer">
                            <?foreach($metaData->getPages() as $page):?>
                            <a class="btn btn-primary <?=($page['current'] ? "disabled" : "btn-outline")?>" href="<?=$page['url']?>"><?=$page['title']?></a>
                            <?endforeach?>
                        </div>
                    <?endif?>
                    </div>
                    <!-- /.panel -->
                </div>
            </div>
        </div>
    </div>
<script type="text/javascript">
    function saveMeta(parent, key, value){
        let data = {key: key, parent: parent, value: value};
        console.log(data);
        tsFrame.query('POST', 'meta', data);
    }
    
    $('.meta-field').blur(function(e){ 
        let $field = $(e.target);
        return saveMeta($field.attr('data-parent'), $field.attr('data-key'), $field.val());
    });
</script>
<?$this->incFooter()?>