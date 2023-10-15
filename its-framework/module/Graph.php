<?php
/**
 * Либа для работы с графическими файлами. Обёртка над GD.
 *
 * @throws GraphException
 *
 *** Load/Create Functions ***
 * @method __construct(string $file = false)
 * @method loadFile(string $filepath)
 * @method loadGD(resource $gd)
 * @method loadString(string $string)
 * @method create(int $width, int $height)
 *
 *** Getters ***
 * @method getRoot()
 * @return GD resource
 *
 * @method getBase64()
 * @return string with encoded image
 *
 * @method 	getColor(mixed $color)
 * @example getColor(['r' => (int), 'g' => (int), 'b' => (int)]);
 * @example getColor([0,0,0]);
 * @example getColor('#FF00FF');
 * @return int color indication for GD
 *
 * @method getPixel(int $x, int $y)
 * @return array with keys r, b ,g, l
 *
 *** Setters ***
 * @method setPixel(int $x, int $y, mixed $color)
 * @param mixed $color for getColor function 
 *
 *** Convert ***
 * @method hex2rgb(string $color)
 * @param string $color "#FF00FF"
 * @return array [r=>, b=> ,g=>]
 */

namespace tsframe\module;

use tsframe\exception\GraphException;

class Graph{
	public 	$width = 0,
			$height = 0,
			$type = 0,
			$im = NULL,
			$file = NULL;
	
	private $types = [
		0 => 'string',
		IMAGETYPE_GIF => 'gif',
		IMAGETYPE_JPEG => 'jpeg',
		IMAGETYPE_PNG => 'png',
		IMAGETYPE_WBMP => 'wbmp',
	];

	
	public function __construct(?string $filepath = null){
		if(!is_null($filepath) && file_exists($filepath)){
			$this->loadFile($filepath);
		} 
	}
	
	public function loadString($s){ 
		if(!($info = getimagesizefromstring($s)))throw new GraphException('String does not include the image');
		$this->width = $info[0];
		$this->height = $info[1];
		$this->im = imagecreatefromstring($s);
		return $this;
	}
	
	public function loadGD($res){
		$this->im = $res;
		$this->width = imagesx($this->im);
		$this->height = imagesy($this->im);
		return $this;
	}
	
	public function loadFile($file){
		if(!file_exists($file)) throw new GraphException('File "'.$file.'" does not exists');
		if(!($info = getimagesize($file))) throw new GraphException('File "'.$file.'" is not image');
		
		$this->file = realpath($file);
		$this->width = $info[0];
		$this->height = $info[1];
	
		$this->type = (isset($this->types[$info[2]]))?$info[2]:0;
		$func = 'imagecreatefrom'.$this->types[$this->type];
		
		if(!function_exists($func)) throw new GraphException('Can not call function "'.$func.'", image type: '.$info[2]);
		$this->im = $func($file);
		return $this;
	}

	public function fixOrientationFromExif(){
		if(!file_exists($this->file)) throw new GraphException('Image does not created from file. Cannot read exif data.');
		if(!function_exists('exif_read_data')) throw new GraphException('Function "exif_read_data" not exists');

    	
	    $this->im = imagerotate($this->im, array_values([0, 0, 0, 180, 0, 0, -90, 0, 90])[@exif_read_data($this->file)['Orientation'] ?: 0], 0);


    	$this->width = imagesx($this->im);
    	$this->height = imagesy($this->im);
	}
	
	public function create($width, $height){
		$this->width = $width;
		$this->height = $height;
		$this->im = imagecreatetruecolor($width, $height);
		return $this;
	}
	
	public function getRoot(){
		return $this->im;
	}
	
	public function getBase64(){
		ob_start();
		$type = ($this->type === 0) ? IMAGETYPE_JPEG : $this->type;
		$this->Save();
		$data = ob_get_clean();

		return 'data:image/'.$this->types[$type].';base64,'.base64_encode($data);
	}


	public function getColor($color){
		if(is_array($color) and sizeof($color)>2){
			return imagecolorallocate($this->im,
				isset($color['r'])?$color['r']:$color[0],
				isset($color['g'])?$color['g']:$color[1],
				isset($color['b'])?$color['b']:$color[2]
			);
		}elseif(is_string($color)){
			$color = $this->hex2rgb($color);
			return imagecolorallocate($this->im, $color['r'], $color['g'], $color['b']);
		}
		else throw new GraphException('Invalid color value',0,['color' => $color]);
	}
	
	public function getPixel($x,$y){
		$rgb = imagecolorat($this->im, $x, $y);
		$r = ($rgb >> 16) & 0xFF;
		$g = ($rgb >> 8) & 0xFF;
		$b = $rgb & 0xFF;
		
		$ret = [
				'r'=>$r,
				'g'=>$g,
				'b'=>$b,
		];
		

		$ret['l'] = 0.11*$r+0.59*$g+0.30*$b;
		
		return $ret;
	}
	
	public function setPixel($x,$y,$color){
		$col = $this->getColor($color);
		return imagesetpixel($this->im, $x, $y, $col);
	}
	
	public function hex2rgb ($hexstr){
		$int = hexdec($hexstr);
		return [
			"r" => 0xFF & ($int >> 0x10),
            "g" => 0xFF & ($int >> 0x8),
            "b" => 0xFF & $int
		];
	}	
		
	public function save($file = NULL, $type = 'auto', $quality = 100){
		if($type=='auto'){
			if($this->type === 0) $this->type = IMAGETYPE_JPEG;
			$type = $this->types[$this->type];
		}

		$func = 'image'.$type;
		if(!function_exists($func)){
			throw new \Exception('Can not call function "'.$func);
		}

		if(strtolower($type) == 'png'){
			$quality = round((100 - $quality) / 10);
		}

		return $func($this->im, $file, $quality);

	}
	
	public function destroy(){
		imageDestroy($this->im);
		unset($this->im);
	}

	/****************/

	/*
	1 | 2 | 3
	4 | 5 | 6
	7 | 8 | 9
	*/
	public function resizeTo($toWidth, $toHeight, $area = 5){		
		if(abs($this->width - $toWidth) > abs($this->height - $toHeight)){
			$newHeight = $toHeight;
			$newWidth = $newHeight / $this->height * $this->width;

			$hFix = 0;
			$yFix = 0;
			$wFix = $newWidth - $toWidth;

			switch($area){
				case 1:
				case 4:
				case 7:
					$xFix = 0;
				break;
				
				case 3:
				case 6:
				case 9:
					$xFix = -$wFix;
				break;

				default:
					$xFix = -$wFix/2;

			}

		} else {
			$newWidth = $toWidth;
			$newHeight = $newWidth / $this->width * $this->height;

			$k = $this->width / $toWidth;

			$wFix = 0;
			$xFix = 0;
			$hFix = $newHeight - $toHeight;

			switch($area){
				case 1:
				case 2:
				case 3:
					$yFix = 0;
				break;
				
				case 7:
				case 8:
				case 9:
					$yFix = -$hFix;
				break;

				default:
					$yFix = -$hFix/2;

			}
		}

		$newIm = new self;
		$newIm->create($toWidth, $toHeight);

		imagecopyresampled ( $newIm->getRoot() , $this->im , $xFix + 0 , $yFix + 0 , 0 , 0 , $wFix + $toWidth , $hFix + $toHeight , $this->width, $this->height);
		return $newIm;
	}
}