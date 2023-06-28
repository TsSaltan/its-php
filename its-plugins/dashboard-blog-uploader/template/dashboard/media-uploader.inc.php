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
                <button id="insert-media" class="btn btn-info hidden"><?=__('button/insert-link')?></button>
            </div>
        </div>
    </form>
    <!-- /.panel -->
</div>

<script type="text/javascript">
    $('#insert-media').on('click', function(e){
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