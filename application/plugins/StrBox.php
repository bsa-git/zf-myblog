<?php

/**
 * Default_Plugin_UserException
 *
 * Класс обработки ошибок пользователя
 *
 * @uses       Zend_Exception
 * @package    Module-Default
 * @subpackage Plugins
 */
class Default_Plugin_UserException extends Zend_Exception {

    private $typeError = "";

    //-------------------------------

    /**
     * Конструктор
     * @param string $typeError
     * @param string $message
     * @param int $code 
     */
    public function __construct($typeError, $message, $code = 0) {
        parent::__construct($message, $code);
        $this->typeError = $typeError;
    }

    //------------
    //toString()
    public function __toString() {
        return "$this->message\n";
    }

    //-------------------
    //Получить тип ошибки
    public function GetTypeError() {
        return $this->typeError;
    }

}

//================ КЛАСС РАБОТЫ СО СТРОКАМИ =========================//

/**
 * Default_Plugin_StrBox
 *
 * Класс для работы со строками
 *
 *
 * @uses
 * @package    Module-Default
 * @subpackage Plugins
 */
class Default_Plugin_StrBox {
    //=========================== HTML ========================================//

    /**
     * Экранирование служебных символов, что бы не было SQL инекций в запросах к базе данных
     * экранирование проходит не зависимо от кодировки
     *
     *
     * @param string $value
     * @return string
     */
    static function quoteSmart($value) {
        // Else magic_quotes_gpc on - use stripslashes
        if (get_magic_quotes_gpc()) {
            $value = stripslashes($value);
        }
        // If variable - a number, that shields its not it is necessary
        // if no - that surround her( quote, and shield
        if (!is_numeric($value)) {
            //$value = "'".mysql_escape_string($value)."'";
            $value = mysql_escape_string($value);
        }
        return $value;
    }

    /**
     * Экранирование служебных символов, что бы не было SQL инекций в запросах к базе данных
     * экранирование проходит зависимо от кодировки
     *
     *
     * @param string $value
     * @return string
     */
    static function quoteSmartReal($value) {
        // Else magic_quotes_gpc on - use stripslashes
        if (get_magic_quotes_gpc()) {
            $value = stripslashes($value);
        }
        // If variable - a number, that shields its not it is necessary
        // if no - that surround her( quote, and shield
        if (!is_numeric($value)) {
            //$value = "'" . mysql_real_escape_string($value) . "'";
            $value = mysql_real_escape_string($value);
        }
        return $value;
    }

    /**
     * Удаления всего ненужного html
     *
     *
     * @param string $text
     * @return array
     */
    static function phpdigCleanHtml($text) {
        //htmlentities
        global $spec;

        //replace blank characters by spaces
        $text = ereg_replace("[\r\n\t]+", " ", $text);

        //extracts title
        if (preg_match('/< *title *>(.*?)< *\/ *title *>/is', $text, $regs)) {
            $title = trim($regs[1]);
        } else {
            $title = "";
        }

        //delete content of head, script, and style tags
        $text = eregi_replace("<head[^>]*>.*</head>", " ", $text);
        //$text = eregi_replace("<script[^>]*>.*</script>"," ",$text); // more conservative
        $text = preg_replace("/<script[^>]*?>.*?<\/script>/is", " ", $text); // less conservative
        $text = eregi_replace("<style[^>]*>.*</style>", " ", $text);
        // clean tags
        $text = preg_replace("/<[\/\!]*?[^<>]*?>/is", " ", $text);
        //$text = strip_tags($text,"<sub>");
        // first case-sensitive and then case-insensitive
        //tries to replace htmlentities by ascii equivalent
        if ($spec) {
            foreach ($spec as $entity => $char) {
                $text = ereg_replace($entity . "[;]?", $char, $text);
                $title = ereg_replace($entity . "[;]?", $char, $title);
            }
            //tries to replace htmlentities by ascii equivalent
            foreach ($spec as $entity => $char) {
                $text = eregi_replace($entity . "[;]?", $char, $text);
                $title = eregi_replace($entity . "[;]?", $char, $title);
            }
        }

        while (eregi('&#([0-9]{3});', $text, $reg)) {
            $text = str_replace($reg[0], chr($reg[1]), $text);
        }
        while (eregi('&#x([a-f0-9]{2});', $text, $reg)) {
            $text = str_replace($reg[0], chr(base_convert($reg[1], 16, 10)), $text);
        }

        //replace foo characters by space
        $text = eregi_replace("[*{}()\"\r\n\t]+", " ", $text);
        $text = eregi_replace("<[^>]*>", " ", $text);
        $text = ereg_replace("(\r|\n|\r\n)", " ", $text);

        // replace any stranglers by space
        $text = eregi_replace("( -> | <- | > | < )", " ", $text);

        //strip characters used in highlighting with no space
        $text = str_replace("^#_", "", str_replace("_#^", "", $text));
        $text = str_replace("@@@", "", str_replace("@#@", "", $text));

        $text = ereg_replace("[[:space:]]+", " ", $text);

        $retour = array();
        $retour['content'] = $text;
        $retour['title'] = $title;
        return $retour;
    }

    /**
     * Save HTML File
     *
     *
     * @param string $file
     * @return int // кол. байтов вставленных в файл или FALSE если ошибка
     */
    static function saveHTMLFile($file) {
        $str_file = file_get_contents($file);
        $search = array("&lt;", "&gt;");
        $replace = array("<", ">");
        $str_file = str_replace($search, $replace, $str_file);
        return file_put_contents($file, $str_file);
    }

    /**
     * Удаляются пробелы и
     * Преобразуем спец. символы в HTML сущности
     *
     *
     * @param string $value
     * @return string
     */
    static function escapeSmart($value) {

        //Очистим пробелы спереди и сзади строки
        $value = trim($value);

        //Преобразуем спец. символы в HTML сущности
        //Производятся следующие преобразования:
        //'&' (амперсанд) преобразуется в '&amp;'
        //'"' (двойная кавычка) преобразуется в '&quot;' when ENT_NOQUOTES is not set.
        //''' (одиночная кавычка) преобразуется в '&#039;' только в режиме ENT_QUOTES.
        //'<' (знак "меньше чем") преобразуется в '&lt;'
        //'>' (знак "больше чем") преобразуется в '&gt;'

        $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        return $value;
    }

    /**
     * Truncate modifier
     *
     * Name:     truncate<br>
     * Purpose:  Truncate a string to a certain length if necessary,
     *           optionally splitting in the middle of a word, and
     *           appending the $etc string or inserting $etc into the middle.
     * @param string
     * @param integer
     * @param string
     * @param string
     * @param boolean
     * @param boolean
     * 
     * @return string
     */
    static function Truncate($string, $length = 80, $encoding = 'UTF-8', $etc = '...', $break_words = false, $middle = false) {
        if ($length == 0)
            return '';

        if (strlen($string) > $length) {
            $length -= min($length, strlen($etc));
            if (!$break_words && !$middle) {
                $string = mb_ereg_replace('/\s+?(\S+)?$/', '', mb_substr($string, 0, $length + 1, $encoding));
            }
            if (!$middle) {
                return mb_substr($string, 0, $length, $encoding) . $etc;
            } else {
                return mb_substr($string, 0, $length / 2, $encoding) . $etc . mb_substr($string, -$length / 2, $encoding);
            }
        } else {
            return $string;
        }
    }

    /**
     * Strip modifier
     *
     * Name:     strip<br>
     * Purpose:  Replace all repeated spaces, newlines, tabs
     *           with a single space or supplied replacement string.<br>
     * @param string
     * @param string
     * @param string
     * 
     * @return string
     */
    static function Strip($text, $replace = ' ', $encoding = 'UTF-8') {
        mb_regex_encoding($encoding);
        $s = mb_ereg_replace('\s+', $replace, $text);
        return $s;
    }

    /**
     * Функция транслита
     *
     * Name:     generate_chpu
     * Purpose:  На входе кириллица, а на выходе предложение на латинской раскладке
     * 
     * 
     * @param string $str
     * 
     * @return string
     */
    static function generate_chpu($str) {
        $converter = array(
            'а' => 'a', 'б' => 'b', 'в' => 'v',
            'г' => 'g', 'д' => 'd', 'е' => 'e',
            'ё' => 'e', 'ж' => 'zh', 'з' => 'z',
            'и' => 'i', 'й' => 'y', 'к' => 'k',
            'л' => 'l', 'м' => 'm', 'н' => 'n',
            'о' => 'o', 'п' => 'p', 'р' => 'r',
            'с' => 's', 'т' => 't', 'у' => 'u',
            'ф' => 'f', 'х' => 'h', 'ц' => 'c',
            'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sch',
            'ь' => '', 'ы' => 'y', 'ъ' => '',
            'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
            'А' => 'A', 'Б' => 'B', 'В' => 'V',
            'Г' => 'G', 'Д' => 'D', 'Е' => 'E',
            'Ё' => 'E', 'Ж' => 'Zh', 'З' => 'Z',
            'И' => 'I', 'Й' => 'Y', 'К' => 'K',
            'Л' => 'L', 'М' => 'M', 'Н' => 'N',
            'О' => 'O', 'П' => 'P', 'Р' => 'R',
            'С' => 'S', 'Т' => 'T', 'У' => 'U',
            'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C',
            'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Sch',
            'Ь' => '', 'Ы' => 'Y', 'Ъ' => '',
            'Э' => 'E', 'Ю' => 'Yu', 'Я' => 'Ya',
        );
        $str = strtr($str, $converter);
        $str = strtolower($str);
        $str = preg_replace('~[^-a-z0-9_]+~u', '-', $str);
        $str = trim($str, "-");
        return $str;
    }
    
    /**
     * Преобразуем строку в транслит
     * 
     * @return String the resulting String object
     */
    static function Translit($str) {
        $value = $str;
        //----------------
        // Массив символов
        $letters = array(
            "а" => "a", "б" => "b", "в" => "v", "г" => "g", "д" => "d", "е" => "e",
            "ё" => "e", "ж" => "zh", "з" => "z", "и" => "i", "й" => "j", "к" => "k",
            "л" => "l", "м" => "m", "н" => "n", "о" => "o", "п" => "p", "р" => "r",
            "с" => "s", "т" => "t", "у" => "u", "ф" => "f", "х" => "h", "ц" => "c",
            "ч" => "ch", "ш" => "sh", "щ" => "sh", "ы" => "i", "ь" => "", "ъ" => "",
            "э" => "e", "ю" => "yu", "я" => "ya",
            "А" => "A", "Б" => "B", "В" => "V", "Г" => "G", "Д" => "D", "Е" => "E",
            "Ё" => "E", "Ж" => "ZH", "З" => "Z", "И" => "I", "Й" => "J", "К" => "K",
            "Л" => "L", "М" => "M", "Н" => "N", "О" => "O", "П" => "P", "Р" => "R",
            "С" => "S", "Т" => "T", "У" => "U", "Ф" => "F", "Х" => "H", "Ц" => "C",
            "Ч" => "CH", "Ш" => "SH", "Щ" => "SH", "Ы" => "I", "Ь" => "", "Ъ" => "",
            "Э" => "E", "Ю" => "YU", "Я" => "YA",
        );

        // Проходим по массиву и заменяем каждый символ фильтруемого значения
        foreach ($letters as $letterVal => $letterKey) {
            $value = str_replace($letterVal, $letterKey, $value);
        }

        return $value;
    }

    /**
     * Функция tolink() принимает в качестве аргумента ваш текст и возвращает 
     * текст с уже замененными URL на активные ссылки. 
     * 
     * @param string $buf
     * @return String the resulting String object
     */
    static function tolink($buf) {
        $x = explode(" ", $buf);
        $newbuf = '';
        for ($j = 0; $j < count($x); $j++) {
            if (preg_match
                            ("/(http:\\/\\/)?([a-z_0-9-.]+\\.[a-z]{2,3}(([ \"'>\r\n\t])|(\\/([^ \"'>\r\n\t]*)?)))/", $x[$j], $ok))
                $newbuf.=str_replace($ok[2], "<a href='http://$ok[2]'>$ok[2]</a>", str_replace("http://", "", $x[$j])) . " ";
            else
                $newbuf.=$x[$j] . " ";
        }
        return $newbuf;
    }

    //=========== РАБОТА С МАССИВАМИ ==================//

    /**
     * Вывести ответ из массива данных
     *
     *
     * @param string $prefix
     * @param array $arrData
     * 
     * @return void
     */
    static function outResponseFromArray($prefix, array $arrData) {
        foreach ($arrData as $key => $value) {
            echo $prefix . "$key:=" . $value . "<br>\n";
        }
    }

    /**
     * Получим строку параметров для запроса SQL в операторе IN('val_1','val_2')
     *
     *
     * @param array $arrParam
     * 
     * @return string
     */
    static function getParamFor_IN(array $arrParam) {

        $strParam = "";
        //----------------
        foreach ($arrParam as $param) {
            if ($strParam == "")
                $strParam = "'" . $param . "'";
            else
                $strParam = $strParam . ",'" . $param . "'";
        }

        return $strParam;
    }

    
    //=========== РАБОТА С ДАТОЙ ВРЕМЕНЕМ ==================//

    /**
     * Получить текущую дату в формате - Y-m-d H:i:s
     * 
     * @param int $TimeStamp
     * @return string 
     */
    static function getCurrentDateTime($TimeStamp = 0) {

        $format = "Y-m-d H:i:s";
        //----------------
        if ($TimeStamp == 0)
            return date($format);
        else
            return date($format, $TimeStamp);
    }

    /**
     * Преобразование значений даты к временной метке
     * 
     * @param int $year
     * @param int $month
     * @param int $day
     * @return int 
     */
    static function makeTimeStamp($year = '', $month = '', $day = '') {
        if (empty($year)) {
            $year = strftime('%Y');
        }
        if (empty($month)) {
            $month = strftime('%m');
        }
        if (empty($day)) {
            $day = strftime('%d');
        }

        return mktime(0, 0, 0, $month, $day, $year);
    }

    /**
     * функция, которая заменяет стандартную функцию date()
     * для вывода форматированной даты и будет выводить русский месяц.
     * 
     * @param string $param
     * @param int $time
     * @return string
     */
    static function rdate($param, $time = 0) {
        if (intval($time) == 0)
            $time = time();
        $MonthNames = array("Января", "Февраля", "Марта", "Апреля", "Мая", "Июня", "Июля", "Августа", "Сентября", "Октября", "Ноября", "Декабря");
        if (strpos($param, 'M') === false)
            return date($param, $time);
        else
            return date(str_replace('M', $MonthNames[date('n', $time) - 1], $param), $time);
    }

    //============= РАБОТА С ОТЧЕТАМИ  ==============//

    /**
     * Получить оглавление для отчета
     * 
     * @param string $title
     * @param string $url_pic
     * 
     * 
     * @return string 
     */
    static function getTitle4Report($title, $url_pic) {

        // style='text-align: right;'

        $html = "
        <table border='0' cellpadding='3' width='100%'>
        <tbody>
            <tr class='h'>
                <td class='t' ><img src='$url_pic' alt=''><span class='t'> $title</span></td>
            </tr>
        </tbody>
        </table>
        ";

        return $html;
    }

    /**
     * Получить заголовок отчета
     * 
     * @param array $arrHeaders
     * 
     * 
     * @return atring 
     */
    static function getHead4Report(array $arrHeaders) {
        $th = '';
        foreach ($arrHeaders as $value) {
            $th .= '<th>' . $value . '</th>';
        }

        $html = "
        <thead>
            <tr class='h'>
                $th
            </tr>
        </thead>
        ";

        return $html;
    }

    /**
     * Получить нижний колонтитул отчета
     * 
     * @param array $arrHeaders // Массив строк т.е. может быть несколько строк результатов пр. сумма, среднее, макс, мин.
     * 
     * 
     * @return string 
     */
    static function getFooter4Report(array $arrFooters) {
        $foot = '';
        $tr = '';
        $th = '';
        //---------------------------------
        foreach ($arrFooters as $arrFooter) {
            $tr = "<tr>";
            foreach ($arrFooter as $value) {
                if ($th) {
                    $th .= '<td class="fv">' . $value . '</td>';
                } else {
                    $th .= '<td class="fh">' . $value . '</td>';
                }
            }
            $tr .= $th . '</tr>';
            $foot .= $tr;
            $tr = '';
            $th = '';
        }

        $html = "
        <tfoot>
            $foot
        </tfoot>
        ";

        return $html;
    }

    /**
     * Получить данные для отчета
     * 
     * @param array $params // Массив параметров
     * @param array $arrRows // Массив строк данных 
     * 
     * 
     * @return atring 
     */
    static function getBody4Report(array $arrRows, array $params = null) {
        $isRowHeader = false;
        $body = '';
        $tr = '';
        $td = '';
        $index = 1;
        //---------------------------------
        if ($params['isRowHeader']) {
            $isRowHeader = $params['isRowHeader'];
        }

        foreach ($arrRows as $arrRow) {
            $is_even = $index % 2;
            if ($is_even) {
                $tr = "<tr class='row-even'>";
            } else {
                $tr = "<tr class='row-odd'>";
            }

            foreach ($arrRow as $value) {
                if ($td) {

                    if (is_bool($value)) {
                        $td .= '<td class="v">' . $value . '</td>';
                        continue;
                    }

                    if (is_string($value)) {
                        $td .= '<td class="vl">' . $value . '</td>';
                        continue;
                    }

                    if (is_float($value) || is_int($value)) {
                        $td .= '<td class="vr" >' . $value . '</td>';
                        continue;
                    }

                    $td .= '<td class="vl">' . $value . '</td>';
                } else {
                    if ($isRowHeader) {                        //is_string($arrRow)  //is_float($arrRow) //is_int($arrRow) //is_bool($arrRow)
                        $td .= '<td class="e">' . $value . '</td>';
                    } else {

                        if (is_bool($value)) {
                            $td .= '<td class="v">' . $value . '</td>';
                            continue;
                        }

                        if (is_string($value)) {
                            $td .= '<td class="vl">' . $value . '</td>';
                            continue;
                        }

                        if (is_float($value) || is_int($value)) {
                            $td .= '<td class="vr">' . $value . '</td>';
                            continue;
                        }

                        $td .= '<td class="vl">' . $value . '</td>';
                    }
                }
            }
            $index++;
            $tr .= $td . '</tr>';
            $body .= $tr;
            $tr = '';
            $td = '';
        }

        $html = "
        <tbody>
            $body
        </tbody>
        ";

        return $html;
    }

    //============= ВЫЗОВ ПОЛЬЗОВАТЕЛЬСКОГО СООБЩЕНИЯ  ==============//

    /**
     * Вывести сообщение пользователя
     *
     *
     * @param string $typeMessage
     * @param array $arrParam
     * @return string
     */
    static function msgUser($typeMessage, array $arrParam = null) {
        $myMsg = "";
        $translate = Zend_Registry::get('Zend_Translate');
        $myMsg = $translate->_($typeMessage);
        //------------------
        switch ($typeMessage) {
            //Регистрация пользователя '%s' прошла успешно!
            case "MSG_LOGIN_CLIENT_OK":
                $myMsg = sprintf($myMsg, $arrParam[0]);
                break;
            //Регистрируясь вы должны согласится с правилами
            case "AGREE_RULES":
                break;
        }
        return $myMsg;
    }

    /**
     * Вывести сообщение пользователя при ошибке
     *
     *
     * @param string $typeMessage
     * @param array $arrParam
     * @return string
     */
    static function msgUserError($typeMessage, array $arrParam = null) {
        $myMsg = "";
        $translate = Zend_Registry::get('Zend_Translate');
        $myMsg = $translate->_($typeMessage);
        //------------------
        switch ($typeMessage) {
            //Ошибка регистрации: имя или пароль клиента заданы неверно!
            case "ERR_LOGIN_CLIENT":
                $myMsg = $myMsg;
                break;
            //Ошибка формы! Неверно введены данные в форму.
            case "ERR_FORM_VALUES":
                $myMsg = $myMsg;
                break;
            //Ошибка формы! Неверно введены данные в форму.
            case "ERR_GET_PARAM":
                $myMsg = $myMsg;
                break;
        }
        return $myMsg;
    }

    /**
     * Вывести предупреждение пользователю
     *
     *
     * @param string $typeMessage
     * @param array $arrParam
     * @return string
     */
    static function msgUserWarning($typeMessage, array $arrParam = null) {
        $myMsg = "";
        $translate = Zend_Registry::get('Zend_Translate');
        $myMsg = $translate->_($typeMessage);
        //------------------
        switch ($typeMessage) {
            //Ошибка регистрации: имя или пароль клиента заданы неверно!
            case "WARNING_LOGIN_CLIENT":
                $myMsg = $myMsg;
                break;
        }
        return $myMsg;
    }

    //==================== ВЫЗОВ ПОЛЬЗОВАТЕЛЬСКОЙ ОШИБКИ  ==================//

    /**
     * Получим сообщение об ошибке в виде:
     *  - сообщения об ошибке;
     *  - трассы появления ошибки
     *
     * @param Exception $exc
     *
     * @return string
     *
     */
    static function getMessageError(Exception $exc) {
        $message = '<em>Message:</em><br>';
        $message .= $exc->getMessage() . '<br>';
        $message .= '<em>Trace Error:</em><br>';
        $errTraceErr = explode('#', $exc->getTraceAsString());
        foreach ($errTraceErr as $value) {
            $message .= $value . '<br>';
        }
        return $message;
    }

    /**
     * Ошибка пользователя - Exception (Default_Plugin_UserException)
     *
     * @param string $typeError
     * @param array $arrParam
     *
     */
    static function errUser($typeError, array $arrParam = null) {
        $myMsg = "";
        $translate = Zend_Registry::get('Zend_Translate');
        $myMsg = $translate->_($typeError);
        //-------------------------------------------
        switch ($typeError) {
            //Системная ошибка
            case "ERR_SYS":
                $myMsg = "%s";
                $myMsg = sprintf($myMsg, $arrParam[0]);
                break;
            //Ошибка обращения к контроллеру: '%s' - неверно заданы параметры.
            case "ERR_GET_PARAM":
                $myMsg = sprintf($myMsg, $arrParam[0]);
                break;
            //Ошибка регистрации: имя или пароль клиента заданы неверно!
            case "ERR_LOGIN_CLIENT":
                break;
            //Ошибка регистрации: имя или пароль клиента заданы неверно!
            case "ERR_CREATE_PDF_REPORT":
                break;
        }
        throw new Default_Plugin_UserException($typeError, $myMsg);
    }

}
