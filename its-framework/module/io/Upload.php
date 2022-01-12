<?php
namespace tsframe\module\io;

use tsframe\exception\InputException;

/**
 * Валидатор входящих данных
 *
 * @method Input maxSize() maxSize(int $bytes) Максимальный размер файла 
 */
class Upload extends Input {
	protected $uploadPath = APP_UPLOAD;
	protected $uploadOnAssert = true;

	/**
	 * Изменение параметра - путь для загрузки и сохранения файлов
	 */
	public function setUploadPath(string $path): Upload {
		$this->uploadPath = $path;
		return $this;
	}

	/**
	 * Изменение параметра - автоматическая загрузка файлов
	 */
	public function setUploadOnAssert(bool $upload): Upload {
		$this->uploadOnAssert = $upload;
		return $this;
	}

	public static function files(): Upload {
		return new self($_FILES);
	}

	/**
	 * Проверка для каждого элемента - является ли этот элемент загружаемым файлом
	 */
	protected function checkUploadFile(){
		$current = $this->getCurrentData();
		return is_array($current) && isset($current['name']) && isset($current['type']) && isset($current['size']) && isset($current['tmp_name']);
	}

	/**
	 * @override
	 */
	public function key(string $key): Upload {
		parent::key($key);

		if(!$this->checkUploadFile()){
			$this->invalid[$this->currentKey]['filter'][] = 'Upload file object';
			$this->invalid[$this->currentKey]['value'] = $this->getCurrentData();
		}

		return $this;
	}

	/**
	 * @override
	 */
	public function assert(){
		$data = parent::assert();

		if($this->uploadOnAssert){
			foreach($data as $k => $v){
				$filename = $this->uploadPath . DS . date('ymd-His-u') . rand(0, 999) . '-' . basename($v['name']);
				if(move_uploaded_file($v['tmp_name'], $filename)){
					$data[$k]['filepath'] = $filename;
				} else {
					$data[$k]['filepath'] = null;
				}
			}
		}

		return $data;
	}
}

// Добавление фильтров
Upload::addFilter('maxSize', function(Upload $upload, int $size){
	$current = $upload->getCurrentData();
	return isset($current['size']) && intval($current['size']) <= $size;
});

Upload::addFilter('minSize', function(Upload $upload, int $size){
	$current = $upload->getCurrentData();
	return isset($current['size']) && intval($current['size']) >= $size;
});

Upload::addFilter('type', function(Upload $upload, string $type){
	$current = $upload->getCurrentData();
	return isset($current['type']) && strtolower($current['type']) == strtolower($type);
});

Upload::addFilter('types', function(Upload $upload, array $types){
	$current = $upload->getCurrentData();
	return isset($current['type']) && in_array($current['type'], $types);
});

Upload::addFilter('ext', function(Upload $upload, $ext){
	$current = $upload->getCurrentData();
	if(!isset($current['name'])) return false;

	$name = $current['name'];
	if(!is_array($ext)){
		$ext = [$ext];
	}

	$parts = explode('.', $name);
	$curExt = strtolower(end($parts));

	foreach($ext as $k => $v){
		$e = strtolower(str_replace('.', '', $v));
		if($e == $curExt) return true;
	}


	return false;
});

Upload::addFilter('image', function(Upload $upload){
	$current = $upload->getCurrentData();
	if(!isset($current['type'])) return false;
	$type = strtolower($current['type']);

	return strpos($type, 'image/') !== false;
});

Upload::addFilter('required', function(Input $input){
	return $input->isCurrentExists();
});