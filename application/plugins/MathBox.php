
<?php
/*=============  КЛАСС ОБРАБОТКИ МАТЕМАТИЧЕСКИХ ОШИБОК ========//
 * Ошибка пользователя
 * Modul MathBox.php Copyright @ 2009 BSA
 * @uses    Zend_Exception
 * @package ZF-BUH1C
 */
class Default_Plugin_MathException extends Zend_Exception {
    private $typeError = "";
    //-------------------------------
    //Конструктор класса
    public function __construct($typeError,$message,$code=0) {
        parent::__construct($message,$code);
        $this->typeError = $typeError;
    }
    //Перегрузим метод toString()
    public function __toString() {
        return "$this->message\n";
    }
    //Получим тип ошибки
    public function getTypeError() {
        return $this->typeError;
    }
}

/*================ КЛАСС РАБОТЫ С ТАТЕМАТИЧЕСКИМИ ФУНКЦИЯМИ =========//
 * Ошибка пользователя
 * Modul MathBox.php Copyright @ 2009 BSA
 * @uses
 * @package ZF-BUH1C
 */
class Default_Plugin_MathBox {
//===================== Математические операции с массивами ========================//
//Выполнить операцию над массивом
    static function doOperation($TypeOperation,array $arrValues) {
        $count = count($arrValues);
        if($count==0) {
            MathBox::errMath("ERR_OPERATION_FOR_EMPTY_ARRAY");
            return;
        }
        switch($TypeOperation) {
            case "sum":
                $result = array_sum($arrValues);
                break;
            case "min":
                $result = min($arrValues);
                break;
            case "max":
                $result = max($arrValues);
                break;
            case "average":
                $count = count($arrValues);
                $result = array_sum($arrValues)/$count;
                break;
            case "count":
                $count = count($arrValues);
                $result = $count;
                break;
            default:
                MathBox::errMath("ERR_NOT_THIS_OPERATION",array($TypeOperation));
                break;
        }
        return  $result;
    }
    //================ DEC TO HEX ======================//
    //Преобразуем десятиричное значение в шестнадцатиричное значение
    //с учетом мах. величины значения
    static function DecToHex($Size,$DecNumber) {
        $result = "";
        $hex_value = "";
        //-------------
        for($count=0;$count<$Size;$count++) {
            $hex_value = $hex_value . "F";
        }
        //echo $hex_value."<br>\n";
        if($DecNumber < 0) {
            $result = "0";
            return  $result;
        }
        if($DecNumber > hexdec($hex_value)) {
            $result = "0";
            return  $result;
        }
        $hex_value = dechex($DecNumber);
        return  sprintf("%0".$Size."X", $DecNumber);
    //return  sprintf("%X", $DecNumber);
    }
    //Получим счетчик в HEX коде
    static function CountHex($Size,$HexNumber) {
        $result = "";
        //------------------
        $count_hex = substr($HexNumber,strlen($HexNumber)-$Size,$Size);
        $count_10 = hexdec($count_hex);
        $count_10 = $count_10 + 1;
        $count_hex = MathBox::DecToHex($Size,$count_10);
        $number_base_hex= substr($HexNumber,0,strlen($HexNumber)-$Size);
        $result = $number_base_hex . $count_hex;
        return  $result;
    }
    //==================== ВЫЗОВ МАТЕМАМИЧЕСКОЙ ОШИБКИ  ==================//
    static  function errMath($typeError, array $arrParam) {
        $myMsg = "";
        //------------------
        switch ($typeError) {
            case "ERR_DIV_ZERRO":
                $myMsg = "WEB: Ошибка деления на нуль.";
                break;
            case "ERR_OPERATION_FOR_EMPTY_ARRAY":
                $myMsg = "WEB: Невозможно выполнить операцию над пустым массивом.";
                break;
            case "ERR_NOT_THIS_OPERATION":
                $myMsg = "WEB: Нет такой операции - '%s'.";
                $myMsg = sprintf($myMsg, $arrParam[0]);
                break;
        }
        throw new Default_Plugin_MathException($typeError,$myMsg);
    }
}


    