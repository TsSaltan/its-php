<?php
namespace tsframe\controller;

use tsframe\Http;
use tsframe\controller\BaseApiController;
use tsframe\module\io\Input;
use tsframe\module\user\UserAccess;

/**
 * @route POST /api/blog/[upload-media:action]
 */ 
class BlogDashboardUploader extends BaseApiController {
	private static $maxFileSize = 10 * 1024 * 1024; // 10 MiB
	private static $avaliableTypes = ["image/jpeg", "image/jpg", "image/png", "image/gif"];

	public static function setMaxFileSize(int $filesize){
		self::$maxFileSize = $filesize;
	}

	public static function setAvaliableTypes(array $types = []){
		self::$avaliableTypes = $types;
	}

	public function postUploadMedia(){
		if(!UserAccess::checkCurrentUser('blog')){
            return $this->sendError('Access denied', 403);
		}

        $data = Input::files()
            ->name('media-file')->required()
        ->assert();

        $tmpFile = $data['media-file']['tmp_name'];

        if($data['media-file']['size'] > self::$maxFileSize){
            return $this->sendError('Uploaded file so large (' . round($data['media-file']['size']/1024, 2) . ' KiB), maximum file size: ' . round(self::$maxFileSize/1024, 2) . ' KiB');
        }

        if(!in_array($data['media-file']['type'], self::$avaliableTypes)){
            return $this->sendError('Uploaded file type not supported (' . $data['media-file']['type'] . ')');
        }

        $tmpExt = explode('.', $data['media-file']['name']);
        $ext = end($tmpExt);

        $filename = APP_MEDIA . DS . md5_file($data['media-file']['tmp_name']) . '.' . $ext;
        if(file_exists($filename) || move_uploaded_file($data['media-file']['tmp_name'], $filename)){
        	$fileURI = str_replace([APP_ROOT, '\\', '//'], '/', $filename);
        	return $this->sendData(['file' => $data['media-file'], 'uri' => Http::makeURI($fileURI)]);
        } else {
            return $this->sendError('Undefined error on uploading file');
        }

        //$this->sendData(['file' => $data['media-file']]);

      /*  try {
            $mask = FFDump::makeMask($data['file-errored']['tmp_name'], $data['file-cleared']['tmp_name']);
            $this->sendData(['mask' => $mask, 'encoded' => base64_encode(gzencode($mask, 9))]);
        } catch (FFDumpException $e){
            $this->sendError('Error on creating mask: ' . $e->getMessage());
        }*/
    }
}