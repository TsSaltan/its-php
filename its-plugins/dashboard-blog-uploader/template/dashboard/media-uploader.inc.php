<div class="col-12 col-lg-4" id="media-uploader">
    <form action="<?=$this->makeURI('/dashboard/blog/media-upload')?>" method="POST">   
        <div class="panel panel-default">
            <div class="panel-heading clearfix">
                <div class="panel-title pull-left"><?=__('blog-upload-media')?></div>
            </div>
            <div class="row">
                <div class="panel-body">
                    <div class="col-lg-12"> 
                        <?=$this->uiAlert(null, 'danger', true)?>
                        <div class="form-group">
                            <label><?=__('media-file')?></label>
                            <input class="form-control" id="media-file" name="media-file" type="file">
                        </div>
                        <div class="form-group">
                            <label><?=__('media-url')?></label>
                            <input class="form-control" id="media-url" onclick="this.setSelectionRange(0, this.value.length)" type="text" readonly>
                        </div>
                    </div>
                </div>
            </div>

            <div class="panel-footer">
                <button id="upload-media" class="btn btn-default"><?=__('button/upload')?></button>
                <span id="insert-media" class="hidden">
                    <a href="#" id="insert-link" class="btn btn-info"><?=__('button/insert-link')?></a>
                    <a href="#" id="insert-img" class="btn btn-info" data-toggle="modal" data-target="#insert-img-modal"><?=__('button/insert-img')?></a>
                </span>
            </div>
        </div>
    </form>
    <!-- /.panel -->
</div>

<!-- Modal -->
<div class="modal fade" id="insert-img-modal" tabindex="-1" role="dialog" aria-labelledby="insert-img-modal-label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="insert-img-modal-label"><?=__('insert-image-thumbs')?></h4>
            </div>
            <div class="modal-body" id="insert-img-body">

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?=__('button/close')?></button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->

<script type="text/javascript">
    $('#insert-link').on('click', function(e){
        return insertTextToContent($('#media-url').val());
    });

    $('#upload-media').on('click', function(e){
        let files = new FormData(),
            url = '<?=$this->makeURI('/api/blog/upload-media')?>';

        files.append('media-file', $('#media-file')[0].files[0]); 

        $('#media-uploader .alert').addClass('hidden');
        $('#upload-media').attr('disabled', 'disabled');

        $.ajax({
            type: 'post',
            url: url,
            processData: false,
            contentType: false,
            data: files,
            success: function (response) {
                $('#upload-media').removeAttr('disabled');
                if(response.error){
                    $('#media-uploader .alert').toggleClass('hidden');
                    $('#media-uploader .alert .text').text(response.error);
                    $('#insert-media').addClass('hidden');
                }
                else if(response.uri){
                    $('#insert-media').removeClass('hidden');
                    $('#media-url').val(response.uri);

                    if(response.image){
                        $('#insert-img').removeClass('hidden');
                        let $btnwrap = $('#insert-img-body');
                        $btnwrap.empty();

                        let $btn = $('<button/>');
                        $btn.addClass('btn');
                        $btn.addClass('btn-primary');
                        $btn.addClass('btn-block');
                        $btn.text('<img> Original size');
                        $btn.on('click', function(e){
                            return insertTextToContent("<img src=\"" + response.uri + "\" alt=\"\"/>");
                        });
                        $btnwrap.append($btn);

                        if(response.thumbs && Object.keys(response.thumbs).length > 0){
                            for(var i in response.thumbs){
                                let $btn = $('<button/>');
                                $btn.addClass('btn');
                                $btn.addClass('btn-default');
                                $btn.addClass('btn-block');
                                $btn.data('size', i);
                                $btn.data('link', response.thumbs[i]);
                                $btn.text('<img> ' + i);
                                $btn.on('click', function(e){
                                    console.log(e);
                                    var size = $(e.target).data('size').split('x');
                                    return insertTextToContent("<img src=\"" + $(e.target).data('link') + "\" width=\"" + size[0] + "px\" height=\"" + size[1] + "px\" alt=\"\"/>");
                                });
                                $btnwrap.append($btn);
                            }
                        }
                    } else {
                        $('#insert-img').addClass('hidden');
                    }
                }

            },
            error: function (e) {
                $('#insert-media').addClass('hidden');
                $('#upload-media').removeAttr('disabled');
                $('#media-uploader .alert').toggleClass('hidden');
                $('#media-uploader .alert .text').text('Error on uploading file');
            }
        });

        return false;
    });
</script>