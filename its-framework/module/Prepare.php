<?php
namespace tsframe\module;

/**
 * Класс позволяет создавать подготовленные запросы (как в PDO)
 */
class Prepare {
    const STRING = 'STRING';
    const STR = 'STRING';
    const INTEGER = 'INTEGER';
    const INT = 'INTEGER';
    const FLOAT = 'FLOAT';
    const BOOLEAN = 'BOOLEAN';
    const BOOL = 'BOOLEAN';

    // Типы переменных
    const VARTYPE_1 = ':([\w\d_]+?)'; // :var 
    const VARTYPE_2 = '%([\w\d_]+?)%'; // %var% 
          
    private $source; 
    private $vars = []; // 'key' => 'value'
    private $safeQuery;
    
    /**
     * Триммить строку перед выводом
     * @var boolean
     */
    public $autoTrim = true;

    /**
     * Обрамлять переменную кавычками
     * @var boolean
     */
    public $addStringQuotes = false;

    /**
     * Режим управления кавычками
     * 0 - ничего не делаем
     * 1 - кавычки экранируются \"
     * 2 - кавычки экранируются ""
     * @var int
     */
    public $quotesPolicy = 0;


    /**
     * Заменить отсутствующие переменные на NULL
     * @var boolean
     */
    public $replaceEmpty = false;

    /**
     * На что будет заменены отсутствующие переменные (при replaceEmpty === true)
     * @var mixed
     */
    public $replaceEmptyTo = 'NULL';

    /**
     * Регулярное выражение для поиска переменных
     * Для значения используются константы VARTYPE_*
     * @var string
     */
    public $varType;

    public function __construct($query){
        $this->source = $query;
        $this->varType = self::VARTYPE_1;
    }
    
    /**
     * @param array $bindParams [key => value] || [key => [value, type]]
     */
    public function bindAll($bindParams): Prepare {
        foreach($bindParams as $key => $value){
            if(is_array($value)){
                $this->bind($key, $value[0], $value[1]);
            } else {
                $this->bind($key, $value);
            }
        }

        return $this;
    }

    public function bind($key, $value, $type = 'STRING'): Prepare {
        //$key = (mb_substr($key, 0, 1) == ':') ? mb_substr($key, 1) : $key;
        if(preg_match('#' . $this->varType . '#Ui', $key, $m)){
            $key = $m[1];
        }

        $key = mb_strtolower($key);
        
        switch($type){
            case self::STRING:
                $value = strval($value);                
                $value = str_replace("\\", "\\\\", $value);
                
                switch($this->quotesPolicy){
                    case 1:
                        $value = str_replace("\"", "\\\"", $value);
                    break;

                    case 2:
                        $value = str_replace('"', '""', $value);
                    break;
                }
                
                if($this->addStringQuotes){
                    $value = '"' . $value . '"';
                } 

                $this->vars[$key] = $value;
            break;            
            
            case self::INTEGER:
                $this->vars[$key] = intval($value);
            break;       
            
            case self::FLOAT:
                $this->vars[$key] = floatval($value);
            break;  
            
            case self::BOOLEAN:
                $this->vars[$key] = boolval($value);
            break;
        }

        return $this;
    }
    
    public function getQuery($bindParams = []): ?string {
        $this->bindAll($bindParams);

        $result = preg_replace_callback('#' . $this->varType . '#Uis', function(array $matches){
            $key = mb_strtolower($matches[1]);
   
            if(isset($this->vars[$key])){
                return $this->vars[$key];
            }
            
            if($this->replaceEmpty){
                return $this->replaceEmptyTo;
            }   
            else return $matches[0];
        }, $this->source);

        return $this->autoTrim ? trim($result) : $result ;
    }

    public function setReplaceEmpty($replaceTo = false): Prepare {
        if($replaceTo === false){
            $this->replaceEmpty = false;
        } else {
            $this->replaceEmpty = true;
            $this->replaceEmptyTo = $replaceTo;
        }

        return $this;
    }

    public function setVarType(string $varType): Prepare {
        $this->varType = $varType;
        return $this;
    }

    public function setAddingStringQuotes(bool $addStringQuotes): Prepare {
        $this->addStringQuotes = $addStringQuotes;
        return $this;
    }

    public function setQuotesPolicy(int $quotesPolicy): Prepare {
        $this->quotesPolicy = $quotesPolicy;
        return $this;
    }

    public function setAutoTrim(bool $autoTrim): Prepare {
        $this->autoTrim = $autoTrim;
        return $this;
    }
    
    public static function query($query, $params = []){
        return (new self($query))->getQuery($params);
    }
}