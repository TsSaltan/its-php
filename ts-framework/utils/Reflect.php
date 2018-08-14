<?
namespace tsframe\utils;

use tsframe\Autoload;

class Reflect{
	/**
	 * Получить список классов и их расположение по namespace
	 * @param  string $namespace [description]
	 * @return [absolute path => full classname]
	 */
	public static function getClasses(string $namespace) : array {
		$namespace .= substr($namespace, -1, 1) == '\\' ? '' : '\\';
		$classes = [];
		$paths = Autoload::getPaths($namespace);
		foreach ($paths as $path) {
			$path .= '*.php';
			$classes = array_merge($classes, glob($path));
		}

		$return = [];
	    foreach ($classes as $key => $value) {
	    	$return[realpath($value)] = $namespace . str_replace('.php', '', basename($value));
	    }
	    
	    return $return;
	}

	public static function getDoc(string $classPath, string $docField = '*') : array {
		$docs = [];
		$comment = (new \ReflectionClass($classPath))->getDocComment();
		if($docField == '*'){
			$lines = explode("\n", $comment);
			foreach ($lines as $line){
				$line = trim($line);
				if($line == '/**' || $line == '/*' || $line == '*/' || $line == '*'){
					continue;
				}
				$docs[] = (substr($line, 0, 1) == '*') ? trim(substr($line, 1)) : $line ;
			}
		} elseif(preg_match_all('#@'.$docField.'\s+([^\n]+)\n#Ui', $comment, $matches)){
			foreach ($matches[0] as $key => $value) {
				$docs[] = $matches[1][$key];
			}
		}	

		return $docs;
	}

	public static function getConstants(string $className) : array {
		return (new \ReflectionClass($className))->getConstants();
	}

}