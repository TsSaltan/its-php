<?php
namespace tsframe\controller;

use tsframe\Hook;
use tsframe\Http;
use tsframe\exception\GraphException;
use tsframe\module\Graph;
use tsframe\module\io\Input;
use tsframe\module\user\UserAccess;

/**
 * @route POST /api/blog/[upload-media:action]
 */ 
class BlogDashboardUploader extends BaseApiController {
    public static $makeThumbs = true;
    public static $thumbsSizes = ['240x320', '640x480', '720x540'];
    public static $imageExts = ['jpg', 'jpeg', 'png', 'gif'];
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

        if($data['media-file']['size'] > self::$maxFileSize){
            return $this->sendError('Uploaded file so large (' . round($data['media-file']['size']/1024, 2) . ' KiB), maximum file size: ' . round(self::$maxFileSize/1024, 2) . ' KiB');
        }

        if(!in_array($data['media-file']['type'], self::$avaliableTypes)){
            return $this->sendError('Uploaded file type not supported (' . $data['media-file']['type'] . ')');
        }

        $tmpExt = explode('.', $data['media-file']['name']);
        $ext = end($tmpExt);

        $filename = APP_MEDIA . DS . md5_file($data['media-file']['tmp_name']) . '.' . $ext;
        Hook::call('dashboard.upload-file.before', [$data['media-file'], &$filename]);
        if(file_exists($filename) || move_uploaded_file($data['media-file']['tmp_name'], $filename)){
            $uploaded = [/*'file' => $data['media-file'], */'uri' => self::makeFileURI($filename), 'image' => false];
            if(self::isImage($filename)){
                $uploaded['image'] = true;
                if(self::$makeThumbs){
                    $thumbs = self::processThumbs($filename);
                    if(is_array($thumbs) && sizeof($thumbs) > 0){
                        $uploaded['thumbs'] = $thumbs;
                    }
                }
            }
        	return $this->sendData($uploaded);
        } else {
            return $this->sendError('Undefined error on uploading file');
        }
    }
    
    protected static function makeFileURI(string $filepath): string {
        return Http::makeURI(str_replace([APP_ROOT, '\\', '//'], '/', $filepath));
    }

    protected static function isImage(string $filePath): bool {
        try {
            $graph = new Graph;
            $graph->loadFile($filePath);
            return true;
        } catch (GraphException $e){
            return false;
        }
    }

    protected static function processThumbs(string $filepath): array {
        $thumbs = [];
        $filename = basename($filepath);
        $filedir = dirname($filepath);
        list($fileNoExt, $fileExt) = explode('.', $filename, 2);
        
        try {
            $graph = new Graph;
            $graph->loadFile($filepath);
            
            foreach(self::$thumbsSizes as $size){
                list($width, $height) = explode('x', $size);
                $thumb = $graph->resizeTo($width, $height, 5);
                $thumbPath = $filedir . DS . $fileNoExt . '-' . $size . '.' . $fileExt;
                $thumb->save($thumbPath, 'jpeg', 90);
                $thumbs[$size] = self::makeFileURI($thumbPath);
            }
        } catch (GraphException $e){
            
        }
        
        return $thumbs;
    }
}