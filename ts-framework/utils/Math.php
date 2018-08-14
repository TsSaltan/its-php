<?
namespace tsframe\utils;

class Math{
	public static function calculate(string $statement) {

	    $calcQueue = array();
	    $operStack = array();
	    $operPriority = array(
	        '(' => 0,
	        ')' => 0,
	        '+' => 1,
	        '-' => 1,
	        '*' => 2,
	        '/' => 2,
	    );
	    $token = '';
	    foreach (str_split($statement) as $char) {
	        // Если цифра, то собираем из цифр число
	        if ($char >= '0' && $char <= '9') {
	            $token .= $char;
	        } else {
	            // Если число накопилось, сохраняем в очереди вычисления
	            if (strlen($token)) {
	                array_push($calcQueue, $token);
	                $token = '';
	            }
	            // Если найденный символ - операция (он есть в списке приоритетов)
	            if (isset($operPriority[$char])) {
	                if (')' == $char) {
	                    // Если символ - закрывающая скобка, переносим операции из стека в очередь вычисления пока не встретим открывающую скобку
	                    while (!empty($operStack)) {
	                        $oper = array_pop($operStack);
	                        if ('(' == $oper) {
	                            break;
	                        }
	                        array_push($calcQueue, $oper);
	                    }
	                    if ('(' != $oper) {
	                        // Упс! А открывающей-то не было. Сильно ругаемся (18+)
	                        throw new \Exception('Unexpected ")"');
	                    }
	                } else {
	                    // Встретили операцию кроме скобки. Переносим операции с меньшим приоритетом в очередь вычисления
	                    while (!empty($operStack) && '(' != $char) {
	                        $oper = array_pop($operStack);
	                        if ($operPriority[$char] > $operPriority[$oper]) {
	                            array_push($operStack, $oper);
	                            break;
	                        }
	                        if ('(' != $oper) {
	                            array_push($calcQueue, $oper);
	                        }
	                    }
	                    // Кладем операцию на стек операций
	                    array_push($operStack, $char);
	                }
	            } elseif (strpos(' ', $char) !== FALSE) {
	                // Игнорируем пробелы (можно добавить что еще игнорируем)
	            } else {
	                // Встретили что-то непонятное (мы так не договаривались). Опять ругаемся
	                throw new \Exception('Unexpected symbol "' . $char . '"');
	            }
	        }

	    }
	    // Вроде все разобрали, но если остались циферки добавляем их в очередь вычисления
	    if (strlen($token)) {
	        array_push($calcQueue, $token);
	        $token = '';
	    }
	    // ... и оставшиеся в стеке операции
	    if (!empty($operStack)) {
	        while ($oper = array_pop($operStack)) {
	            if ('(' == $oper) {
	                // ... кроме открывающих скобок. Это верный признак отсутствующей закрывающей
	                throw new \Exception('Unexpected "("');
	            }
	            array_push($calcQueue, $oper);
	        }
	    }
	    $calcStack = array();
	    // Теперь вычисляем все то, что напарсили
	    // Тут ошибки не ловил, но они могут быть (это домашнее задание)
	    foreach ($calcQueue as $token) {
	        switch ($token) {
	        case '+':
	            $arg2 = array_pop($calcStack);
	            $arg1 = array_pop($calcStack);
	            array_push($calcStack, $arg1 + $arg2);
	            break;
	        case '-':
	            $arg2 = array_pop($calcStack);
	            $arg1 = array_pop($calcStack);
	            array_push($calcStack, $arg1 - $arg2);
	            break;
	        case '*':
	            $arg2 = array_pop($calcStack);
	            $arg1 = array_pop($calcStack);
	            array_push($calcStack, $arg1 * $arg2);
	            break;
	        case '/':
	            $arg2 = array_pop($calcStack);
	            $arg1 = array_pop($calcStack);
	            array_push($calcStack, $arg1 / $arg2);
	            break;
	        default:
	            array_push($calcStack, $token);
	        }
	    }
	    return array_pop($calcStack);
	}

}