<?php

/**
 * Default_Plugin_SysBox
 *
 * Класс для работы с системными задачами
 *
 *
 * @uses
 * @package    Module-Default
 * @subpackage Plugins
 */
abstract class Default_Plugin_SysBox {

    //DEBUG or PUBLIC
    static $debug = true;

    /**
     * MethodMap for Zend_Mail's headers
     *
     * @var array
     */
    static $_methodMapHeaders = array(
        'from' => 'setFrom',
        'to' => 'addTo',
        'cc' => 'addCc',
        'bcc' => 'addBcc',
    );

    /**
     * Application paths
     * 
     * @var array
     */
    static $arrAppPaths = array(
        "data" => "/data",
        "cache" => "/data/cache",
        "cache_db" => "/data/cache/db",
        "cache_file" => "/data/cache/file",
        "cache_output" => "/data/cache/output",
        "cache_page" => "/data/cache/page",
        "cache_paginator" => "/data/cache/paginator",
        "logs" => "/data/logs",
        "search_index" => "/data/search-index",
        "session" => "/data/session",
        "upload" => "/data/upload",
        "tmp" => "/data/tmp",
        "tmp_cookiejar" => "/data/tmp/cookie-jar",
        "tmp_mpdf" => "/data/tmp/mpdf",
        "tmp_mpdfttfontdata" => "/data/tmp/mpdfttfontdata",
        "tmp_mpdfttfontdata_ttf" => "/data/tmp/mpdfttfontdata/ttf",
        "tmp_mpdfttfontdata_ttfempty" => "/data/tmp/mpdfttfontdata/ttf/empty",
        "tmp_templates" => "/data/tmp/templates_c",
        "tmp_templates_admin" => "/data/tmp/templates_c/admin",
        "tmp_templates_default" => "/data/tmp/templates_c/default",
        "tmp_templates_hr" => "/data/tmp/templates_c/hr",
        "public_upload" => "/public/upload",
        "upload_users" => "/public/upload/users",
        "upload_system" => "/public/upload/system",
        "upload_system_flashplayer" => "/public/upload/system/flashplayer",
        "upload_system_flashplayer_win" => "/public/upload/system/flashplayer/win"
    );

    //========================= ERRORS ================================//
    //Выход из скрипта по ERROR!!!!
    static function errExit($aErrMsg) {
        //--------------------------
        //Заменим все переводы строки на симол решетка #
        //это нужно для правильной обработки текста ошибки на стороне клиента
        //$ErrMsg = iconv("Windows-1251", "UTF-8", $aErrMsg);
        $ErrMsg = str_replace("\n", "#", $aErrMsg);
        //Выводится признак ошибочной операции и сообщение об ошибке
        echo "result:=0" . "<br>\n" . "result_msg:=" . $ErrMsg . "<br>\n";
        //Выход из скрипта
        exit();
    }

    //=================== ПОЛУЧИТЬ ДАННЫЕ О ПЕРЕМЕННЫХ РНР ===================//
    //Получить ключи и значения массива $_GET
    static function ShowKeyValue_GET() {
        echo "GET:" . "<br />\n";
        foreach ($_GET as $key => $values) {
            echo $key . "=" . $values . "<br />\n";
        }
    }

    //Получить ключи и значения массива $_POST
    static function ShowKeyValue_POST() {
        echo "POST:" . "<br />\n";
        foreach ($_POST as $key => $values) {
            echo $key . "=" . $values . "<br />\n";
        }
    }

    //Получить ключи и значения массива $_REQUEST
    static function ShowKeyValue_REQUEST() {
        echo "REQUEST:" . "<br />\n";
        foreach ($_REQUEST as $key => $values) {
            echo $key . "=" . $values . "<br />\n";
        }
    }

    //Получить ключи и значения массива $_FILES
    static function ShowKeyValue_FILES() {
        echo "FILES:" . "<br />\n";
        foreach ($_FILES as $key => $values) {
            echo $key . "=" . $values . "<br />\n";
        }
    }

    //Получить ключи и значения массива $_SERVER
    static function ShowKeyValue_SERVER() {
        echo "SERVER:" . "<br />\n";
        foreach ($_SERVER as $key => $values) {
            echo $key . "=" . $values . "<br />\n";
        }
    }

    //Получить ключи и значения массива всех заголовков в запросе от клиента
    static function getKeyValue_HEADERS() {
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
                $headers[$name] = $value;
            } else if ($name == "CONTENT_TYPE") {
                $headers["Content-Type"] = $value;
            } else if ($name == "CONTENT_LENGTH") {
                $headers["Content-Length"] = $value;
            }
        }
        return $headers;
    }

    //================= ПЕРЕВОДЧИК ===================//
    /**
     * Получить язык перевода
     *
     * @return string           //Строка языка перевода: (en, ru, uk)
     */
    static function getTranslateLocale() {
        $sessZendAuth = Zend_Registry::get("Zend_Auth");
        $actual = $sessZendAuth->translate_locale;
        return $actual;
    }

    /**
     * Установить язык перевода
     * 
     * @param string $aLocale 
     * @return void
     */
    static function setTranslateLocale($aLocale) {
        //--------------------
        $sessZendAuth = Zend_Registry::get("Zend_Auth");
        $sessZendAuth->translate_locale = $aLocale;
        $translate = Zend_Registry::get('Zend_Translate');
        $translate->setLocale($aLocale);
    }

    /**
     * Изменить язык перевода, если он отличается от текущего языка
     *
     * @param string $locale // существующий язык перевода
     * @return bool  TRUE - если язык перевода был изменен, ELSE - если нет
     */
    static function updateTranslateLocale($locale) {
        $arr_query = array();
        $result = false;
        //--------------------
        $url = self::getServerURL();
        $querystring = parse_url($url, PHP_URL_QUERY);
        if ($querystring) {
            parse_str($querystring, $arr_query);
            if (isset($arr_query['locale'])) {
                if ($arr_query['locale'] !== $locale) {
                    $result = $arr_query['locale'];
                }
            }
        }
        return $result;
    }

    /**
     * Получить обьект переводчика
     *
     * @return string
     */
    static function getTranslate() {
        $translate = Zend_Registry::get('Zend_Translate');
        return $translate;
    }

    /**
     * Сделать перевод текста
     *
     * @return string
     */
    static function Translate($aText, $param1 = NULL, $param2 = NULL, $param3 = NULL) {
        $text = Zend_Registry::get('Zend_Translate')->_($aText);
        return sprintf($text, $param1, $param2, $param3);
    }

    //================= РАБОТА С ФАЙЛАМИ ===================//

    /**
     * Save HTML File
     * 
     * @param string $file 
     */
    static function saveHTMLFile($file) {
        $str_file = file_get_contents($file);
        $search = array("&lt;", "&gt;");
        $replace = array("<", ">");
        $str_file = str_replace($search, $replace, $str_file);
        file_put_contents($file, $str_file);
    }

    /** Получить массив имен файлов отсортированному по убыванию
     *
     * @param  string $prefix //префикс (начало файла - "my_")
     * @param  string $dir //директория, где находится файл
     * @return array
     *
     * пр. my_20100201.xml;my_20100202.xml;my_20100203.xml -> выберется файл my_20100203.xml
     * или my1.xml;my3.xml;my5.xml -> выберется файл my_5.xml
     */
    static function getNameFilesSortDesc($prefix, $dir) {
        $arrNameFiles = array();
        $name = "";
        //----------------------
        $dirdata = scandir($dir, 1);
        foreach ($dirdata as $key => $element) {
            $isFile = is_file($dir . $element);
            if (is_null($prefix) OR ( $prefix == '')) {
                if ($isFile) {
                    $arrNameFiles[] = $element;
                }
            } else {
                $isPrefix = substr_count($element, $prefix);
                if ($isFile AND $isPrefix) {
                    $arrNameFiles[] = $element;
                }
            }
        }
        return $arrNameFiles;
    }

    /**
     * Get name my script
     * 
     * @return string 
     */
    static function getNameScript() {
        $arr = array();
        $str = $_SERVER['PHP_SELF'];
        $arr = explode("/", $str);
        return $arr[count($arr) - 1];
    }

    /**
     * Get name file
     * 
     * @return string 
     */
    static function getNameFile($patch) {
        $arr = array();
        $arr = explode("/", $patch);
        return $arr[count($arr) - 1];
    }

    /**
     *  Create application paths
     * 
     * @param  int $mode 
     */
    static function createAppPaths($mode = 0777) {
        //Set DOCUMENT_ROOT
        $rootDocument = APPLICATION_BASE;
        foreach (self::$arrAppPaths as $key => $path) {
            $strPath = $rootDocument . $path;
            if (!is_dir($strPath)) {
                $trimPath = trim($path, "/");
                $arrPath = explode('/', $trimPath);
                $strPath = $rootDocument;
                foreach ($arrPath as $itemPath) {
                    $strPath .= "/{$itemPath}";
                    if (!is_dir($strPath) && !mkdir($strPath, $mode)) {
                        throw new Exception("Failed to create a directory '{$strPath}' ...");
                    }
                }
            }
        }
    }

    /**
     *  Copy data base
     * 
     * @param  string $path
     */
    static function copyDataBase($path) {
        if (!is_file($path)) {
            $arrPath = explode('/', $path);
            $file = $arrPath[count($arrPath) - 1];
            $backupPath = str_replace($file, '', $path) . "backup/{$file}";
            if (is_file($backupPath)) {
                if (!copy($backupPath, $path)) {
                    throw new Exception("Could not be copied '{$backupPath}' to '{$path}'.");
                }
            }  else {
                throw new Exception("There is no file '{$backupPath}'.");
            }
        }
    }

    //=================== ФАЙЛОВЫЙ МЕНЕЖДЕР ===================//

    /**
     * Инициализация файлового менеджера (KCFinder)
     * 
     */
    static function iniKCFinder() {
        // Инициализация файлового менеджера
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            $identity = $auth->getIdentity();

            // Создать директорию пользователя для загрузки файлов
            self::createUserUploadDir();
            $username = new Default_Plugin_String($identity->username);
            $username = (string) $username->translit();
            $uploadURL = "../../upload/users/{$username}";
            $_SESSION['KCFINDER'] = array();
            $_SESSION['KCFINDER']['disabled'] = FALSE;
            $_SESSION['KCFINDER']['uploadURL'] = $uploadURL;
            $_SESSION['KCFINDER']['uploadDir'] = "";
        } else {
            unset($_SESSION['KCFINDER']);
        }
    }

    /**
     * Создать директорию пользователя для загрузки файлов
     * 
     * @return bool 
     */
    static function createUserUploadDir() {
        $result = FALSE;
        //-------------------------
        // Получим директорию пользователя
        $config = Zend_Registry::get('config');
        $patch_dir = $config['paths']['upload']['dir'];
        $patch_dir = trim($patch_dir, '/');
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            $identity = $auth->getIdentity();
            $username = new Default_Plugin_String($identity->username);
            $username = (string) $username->translit();
            $patch_dir = $patch_dir . '/' . $username;
        } else {
            $patch_dir = $patch_dir . '/guest';
        }

        // Создадим директорию пользователя
        if (!is_dir($patch_dir)) {
            $result = mkdir($patch_dir, 0700);
            if ($result) {
                $patch_user = $patch_dir . '/images';
                $result = mkdir($patch_user, 0700);
            }
            if ($result) {
                $patch_user = $patch_dir . '/.thumbs';
                $result = mkdir($patch_user, 0700);
                if ($result) {
                    $patch_user = $patch_dir . '/.thumbs/images';
                    $result = mkdir($patch_user, 0700);
                }
            }
            if ($result) {
                $patch_user = $patch_dir . '/files';
                $result = mkdir($patch_user, 0700);
                if ($result) {
                    $patch_user = $patch_dir . '/files/mPDF';
                    $result = mkdir($patch_user, 0700);
                }
                if ($result) {
                    $patch_user = $patch_dir . '/files/audio';
                    $result = mkdir($patch_user, 0700);
                }
                if ($result) {
                    $patch_user = $patch_dir . '/files/video';
                    $result = mkdir($patch_user, 0700);
                }
            }
            if ($result) {
                $patch_user = $patch_dir . '/flash';
                $result = mkdir($patch_user, 0700);
            }
        } else {
            $result = TRUE;
        }
        return $result;
    }

    /**
     * Получить директорию пользователя для загрузки файлов
     * 
     * @return string 
     */
    static function getUserUploadDir() {
        $result = FALSE;
        //-------------------------
        // Получим директорию пользователя
        $config = Zend_Registry::get('config');
        $patch_dir = $config['paths']['upload']['dir'];
        $patch_dir = trim($patch_dir, '/');
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            $identity = $auth->getIdentity();
            $username = new Default_Plugin_String($identity->username);
            $username = (string) $username->translit();
            $patch_dir = $patch_dir . '/' . $username;
        } else {
            $patch_dir = $patch_dir . '/guest';
        }

        return $patch_dir;
    }

    /**
     * Получить директорию пользователя в виде URL
     * пр. -> /upload/users
     * 
     * @return string  
     */
    static function getUserUploadUrl() {

        // Получим директорию пользователя
        $config = Zend_Registry::get('config');
        $patch_url = $config['paths']['upload']['url'];
        $patch_url = trim($patch_url, '/');
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            $identity = $auth->getIdentity();
            $username = new Default_Plugin_String($identity->username);
            $username = (string) $username->translit();
            $patch_url = $patch_url . '/' . $username;
        } else {
            $patch_url = $patch_url . '/guest';
        }

        return $patch_url;
    }

    /**
     * Удалить директорию пользователя для закрузки файлов
     * 
     * @param string $patch_dir // Базовая директория Upload для пользователей
     * @return bool 
     */
    static function deleteUserUploadDir($username) {
        $result = FALSE;
        //-------------------------
        $config = Zend_Registry::get('config');
        $patch_dir = $config['paths']['upload']['dir'];
        $patch_dir = trim($patch_dir, '/');
        $username = new Default_Plugin_String($username);
        $username = (string) $username->translit();
        $patch_dir = $patch_dir . '/' . $username;


        if (is_dir($patch_dir)) {
            // Получим обьект построения дерева файлов
            $ft = new Default_Plugin_FileTree($patch_dir);
            // создадим дерево файлов
            $ft->readTree();
            // удалим файлы и директории
            $result = $ft->delFiles();
            if ($result) {
                // удалим пустую директорию
                $result = rmdir($patch_dir);
            }
        } else {
            $result = TRUE;
        }
        return $result;
    }

    //=============== РАБОТА С ПРОФАЙЛЕРОМ ====================//
    /**
     * profilerSumInfo2Html
     * Вывод суммарной информации из профайлера базы данных
     * в формате НТМЛ
     *
     *
     * @param string $aNameAdapterDB        //Название адаптера базы данных
     * @return string                       //Выходные данные в НТМЛ формате
     */
    static function profilerDbSumInfo2Html($aNameAdapterDB) {

        $strEcho = '';
        //-----------------------------
        $db = Zend_Registry::get($aNameAdapterDB);
        $profiler = $db->getProfiler();

        if ($profiler->getEnabled() == false) {
            return $strEcho;
        }

        //Суммарная информация
        $totalTime = $profiler->getTotalElapsedSecs();
//        $totalTime = number_format($totalTime, 5, '.', '');
        $queryCount = $profiler->getTotalNumQueries();
        $longesTime = 0;
        $longesQuery = null;

        foreach ($profiler->getQueryProfiles() as $query) {
            if ($query->getElapsedSecs() > $longesTime) {
                $longesTime = $query->getElapsedSecs();
                $longesQuery = $query->getQuery();
            }
        }

        $longesTime = number_format($longesTime, 5, '.', ' ');

        $strEcho .= "<b>--- " . self::Translate("Суммарная информация о времени выполнения запросов к базе данных") . " ---</b><br /><br />";
        $strEcho .= self::Translate("Выполнено запросов %s за %s sec.", $queryCount, number_format($totalTime, 5, '.', '')) . "<br /><br />";
        if ($queryCount) {
            $strEcho .= self::Translate('Средняя длительность запроса') . ': ' . number_format($totalTime / $queryCount, 5, '.', '') . ' sec.' . "<br /><br />";
        }
        if ($totalTime) {
            $strEcho .= self::Translate('Может быть выполнено запросов за sec.') . ': ' . number_format($queryCount / $totalTime, 0, '.', ' ') . "<br /><br />";
        }

        $strEcho .= self::Translate('Максимальное время выполнения запроса') . ': ' . $longesTime . ' sec.' . "<br /><br />";
        $strEcho .= self::Translate("Запрос с самым большим временем исполнения") . ": <br>" . $longesQuery . "<br /><br />";

        return $strEcho;
    }

    /**
     * profilerSumTimeQueries
     * Вывод суммарного времени выполнения всех запросов
     *
     * @param string $aNameAdapterDB        //Название адаптера базы данных
     * @return int                         //Cуммарное времени выполнения всех запросов
     */
    static function profilerSumTimeQueries($aNameAdapterDB) {
        $totalTime = 0;
        //---------------------
        $db = Zend_Registry::get($aNameAdapterDB);
        $profiler = $db->getProfiler();

        if ($profiler->getEnabled() == false) {
            return $totalTime;
        }

        $totalTime = $profiler->getTotalElapsedSecs();
        $totalTime = number_format($totalTime, 5, '.', '');

        return $totalTime;
    }

    /**
     * profilerQueriesInfo2Html
     * Вывод информации по запросам из профайлера базы данных
     * в формате НТМЛ
     *
     * @param string $aNameAdapterDB        //Название адаптера базы данных
     * @return string                       //Выходные данные в НТМЛ формате
     */
    static function profilerQueriesInfo2Html($aNameAdapterDB) {
        $strEcho = '';
        $longTime = 0;
        //-----------------------------
        $db = Zend_Registry::get($aNameAdapterDB);
        $profiler = $db->getProfiler();

        if ($profiler->getEnabled() == false) {
            return $strEcho;
        }

        $strEcho .= "<b>---" . self::Translate("Информация о времени выполнения каждого запроса к базе данных") . " ---</b><br /><br />";
        foreach ($profiler->getQueryProfiles() as $query) {

            $longTime = $query->getElapsedSecs();
            $longTime = number_format($longTime, 5, '.', '');
            $myQuery = $query->getQuery();
            $arrParams = $query->getQueryParams();

            foreach ($arrParams as $param) {
                $param = $db->quote($param);
                $pos = strpos($myQuery, '?');
                $myQuery = substr_replace($myQuery, $param, $pos, 1);
            }
            $strEcho .= self::Translate("Запрос") . ": " . $myQuery . "<br />";
            $strEcho .= self::Translate('Время выполнения запроса') . ': ' . $longTime . ' sec.' . "<br /><br />";
        }
        return $strEcho;
    }

    /**
     * profilerTime2Registry
     * Запомнить время выполнения кода в регистре
     *
     * @param float $aStartTime        //Время начала измерения
     * @param string $aMessage        //Описание измеряемого участка кода
     * @return float
     */
    static function profilerTime2Registry($aStartTime, $aMessage) {
        $infoProfiler = '';
        //--------------------------
        $endTime = microtime(1);
        $totalTime = $endTime - $aStartTime;
        $totalTime = number_format($totalTime, 5, '.', '');
        //Запомним информацию о выполнении скрипта

        if (Zend_Registry::isRegistered("Result_Profiler")) {
            $infoProfiler = Zend_Registry::get("Result_Profiler");
        }
        $infoProfiler .= $aMessage . "$totalTime sec.<br />";
        $memoryUsage = self::showMemoryUsage('kb');
        $infoProfiler .= self::Translate("Используемая скриптом память") . ': ' . $memoryUsage . ' kb.<br /><br />';
        Zend_Registry::set("Result_Profiler", $infoProfiler);
        return $totalTime;
    }

    /**
     * profilerTotalReport2Html
     * Суммарный отчет времени выполнения запроса
     *
     * @param float $aStartTime        //Время начала измерения
     * @param float $aNameAdapterDB    //Название адаптера базы данных
     * @return string
     */
    static function profilerTotalReport2Html($aStartTime, $aNameAdapterDB) {
        $_resultProfiler = '';
        //--------------------------
        // Получим признак авторизации администратора
        $auth = Zend_Auth::getInstance();
        $identity = $auth->getIdentity();
        $isAdmin = $identity && ($identity->user_type == 'administrator');

        //Получим данные конфигурации Profiler
        $config = Zend_Registry::get('config');
        // Получим признак включения профайлера
        $profilerEnable = (bool) $config['profiler']['runtime']['enable'];
        // Получим кол. записей инф. о быстродействии в сессиии
        $rowsCount = (int) $config['profiler']['count']['rows'];

        // Если да, то пишем инф о быстродействиии в сессию
        // в переменную $Zend_Auth->results_profiler -> array()
        if ($profilerEnable && $isAdmin) {
            $_endTimeProfiler = microtime(1);


            // Получим инф. о времени работе скрипта
            $dispatchTime = number_format($_endTimeProfiler - $aStartTime, 5, '.', ' ');

            $durationStartApplication = number_format(Zend_Registry::get("Duration_StartApplication"), 5, '.', ' ');
            $durationBootstrap = number_format(Zend_Registry::get("Duration_Bootstrap"), 5, '.', ' ');
            $totalTime = number_format($dispatchTime + $durationStartApplication + $durationBootstrap, 5, '.', ' ');

            // Получим инф. о используемой памяти при работе скрипта
            $memoryUsage_StartIndexPHP = Zend_Registry::get("MemoryUsage_StartIndexPHP");
            $memoryUsage_StartApplication = Zend_Registry::get("MemoryUsage_StartApplication");
            $memoryUsage_Bootstrap = Zend_Registry::get("MemoryUsage_Bootstrap");

            // Максимально используемая память при выполнении скрипта
            $usePeakMemory = self::showPeakMemoryUsage('kb');

            // Используемая память в конце выполнения скрипта
            $useEndMemory = self::showMemoryUsage('kb');

            $_resultProfiler .= '<br /><br /><b>--- ' . self::Translate('Используемая память при выполнении скрипта') . ' ---</b><br /><br />';
            $_resultProfiler .= self::Translate("Максимальное кол. используемой памяти скриптом") . ": $usePeakMemory kb.";

            $_resultProfiler .= '<br /><br /><b>--- ' . self::Translate('Общее время выполнения скрипта') . ' ---</b><br /><br />';

            $_resultProfiler .= self::Translate("Общее время выполнения скрипта") . ": $totalTime sec.<br /><br />";

            $_resultProfiler .= $totalTime . " = (durationStartApplication($durationStartApplication) + durationBootstrap($durationBootstrap) + dispatchLoopTime($dispatchTime))" . " sec.<br /><br />";


            // Получим профайлер для базы данных
            $db = Zend_Registry::get('db');
            $dbProfiler = $db->getProfiler();
            if ($dbProfiler->getEnabled()) {
                $queriesTime = number_format(self::profilerSumTimeQueries('db'), 5, '.', ' ');
                $_resultProfiler .= self::Translate("Время выполнения запросов к базе данных") . ": $queriesTime sec.<br /><br />";

                //Суммарная информация о времени выполнения запросов  к базе данных
                $_resultProfiler .= self::profilerDbSumInfo2Html($aNameAdapterDB);

                //Информация о времени выполнения каждого запроса к базе данных
                $_resultProfiler .= self::profilerQueriesInfo2Html($aNameAdapterDB);
            }

            $_resultProfiler .= '<b>--- ' . Default_Plugin_SysBox::Translate('Информация о выполнении отдельных частей скрипта') . ' ---</b><br /><br />';

            $_resultProfiler .= self::Translate("Кол. используемой памяти вначале скрипта") . ": $memoryUsage_StartIndexPHP kb.<br /><br />";

            $_resultProfiler .= self::Translate("Время выполнения") . "  (index.php + new Zend_Application): $durationStartApplication sec.<br />";
            $_resultProfiler .= self::Translate("Кол. используемой памяти после") . " (new Zend_Application): $memoryUsage_StartApplication kb.<br /><br />";

            $_resultProfiler .= self::Translate("Время выполнения") . "  (Bootstrap): $durationBootstrap sec.<br />";
            $_resultProfiler .= self::Translate("Кол. используемой памяти после") . " (Bootstrap): $memoryUsage_Bootstrap kb.<br /><br />";


            //Получим инф. о действии контроллера
            $front = Zend_Controller_Front::getInstance();
            $request = $front->getRequest();
            $module = $request->getModuleName();
            $controller = $request->getControllerName();
            $action = $request->getActionName();

            $strRequest = "$module/$controller/$action";

            //Определим нужно ли обновлять инф. о профайлере в сессии
            if ($strRequest !== 'admin/tools/profiler' &&
                    $strRequest !== 'admin/tools/clearhist') {
                //Получим информацию о выполнении скрипта
                $infoProfiler = Zend_Registry::get("Result_Profiler");
                $_resultProfiler .= $infoProfiler;

                $_resultProfiler .= self::Translate("Кол. используемой памяти в конце скрипта") . ": $useEndMemory kb.";

                // Очистим инф. о выполнении скрипта
                Zend_Registry::set("Result_Profiler", '');


                $Zend_Auth = Zend_Registry::get("Zend_Auth");
                if (!$Zend_Auth->results_profiler) {
                    $Zend_Auth->results_profiler = array();
                }

                $strRequestFrm = "<span style='color: green;'>$strRequest</span>";
                $totalTimeFrm = "<span style='color: green;'>$totalTime</span>";
                list($msec, $sec) = explode(" ", microtime());
                $msecs = explode('.', number_format($msec, 3, '.', ''));
                $currentTime = date('H:i:s', $sec) . '.' . $msecs[1];
                $currentTimeFrm = "<span style='color: green;'>$currentTime</span>";

                $key = 'Tm' . ': ' . $currentTimeFrm . ' ' .
                        'Req' . ': ' . $strRequestFrm . ' ' .
                        'Rtime' . ': ' . $totalTimeFrm . ' sec.';
                $results_profiler = $Zend_Auth->results_profiler;
                // Ограничим число записей
                $count = count($results_profiler);
                if ($count == $rowsCount) {
                    array_shift($results_profiler);
                }
                // Запомним результат
                $results_profiler[$key] = $_resultProfiler;
                $Zend_Auth->results_profiler = $results_profiler;
            }
        }
    }

    /**
     * Public static method,
     * Вычисление максимально используемой памяти скриптом
     * @static
     * @param String $string you should choose the format you want, 'mb'/'kb'/'bytes' default if bytes!
     * @param integer $round set how much numbers you want after Zero, default is 3
     * @return double amount of memory your script consume
     */
    static function showPeakMemoryUsage($string = 'bytes', $round = 3) {
        $result = null;
        switch ($string) {
            case 'mb': $result = round(memory_get_peak_usage() / 1048576, $round);
                $result = number_format($result, $round, '.', ' ');
                break;
            case 'kb': $result = round(memory_get_peak_usage() / 1024, $round);
                $result = number_format($result, $round, '.', ' ');
                break;
            default: $result = memory_get_peak_usage();
                break;
        }
        return $result;
    }

    /**
     * Public static method,
     * Вычисление используемой памяти скриптом
     * @static
     * @param String $string you should choose the format you want, 'mb'/'kb'/'bytes' default if bytes!
     * @param integer $round set how much numbers you want after Zero, default is 3
     * @return double amount of memory your script consume
     */
    static function showMemoryUsage($string = 'bytes', $round = 3) {
        $result = null;
        switch ($string) {
            case 'mb': $result = round(memory_get_usage() / 1048576, $round);
                $result = number_format($result, $round, '.', ' ');
                break;
            case 'kb': $result = round(memory_get_usage() / 1024, $round);
                $result = number_format($result, $round, '.', ' ');
                break;
            default: $result = memory_get_usage();
                break;
        }
        return $result;
    }

    //============== Работа с Zend_Cache ============//

    /**
     * isCleanCache
     * Проверка очистки кеша
     *
     * @return bool
     */
    static function isCleanCache() {
        $config = Zend_Registry::get('config');
        return (bool) $config['paginator']['clearCache'];
    }

    /**
     * startZendCache_Page
     * Запустить Cache для frontend = 'Page'
     * это кеш, с помощью которого можно
     * кешировать выходные HTML страницы
     *
     * @return bool // TRUE -> попали в кеш, FALSE -> не попали в кеш
     */
    static function startZendCache_Page() {
        $result = false;
        $clearPageCache = false;
        //-------------------
        // Определим текущий URL
        $front = Zend_Controller_Front::getInstance();
        $request = $front->getRequest();


        $module = $request->getModuleName();
        $controller = $request->getControllerName();
        $action = $request->getActionName();
        $pathURL = "$module/$controller/$action";

        // Очистим кеш для некоторых путей URL
        $arrClearCacheForURL = array(
            //----- Модуль=default; Контроллер=error -----
            'default/error/error', // Ошибка приложения
            //----- Модуль=default; Контроллер=blogmanager -----
            'default/blogmanager/edit', // Редактировать сообщение
            'default/blogmanager/setstatus', // Установить статус сообщения
            'default/blogmanager/tags', // Добавление/удаление меток
            'default/blogmanager/images', // Загрузка/выгрузка/упорядочивание изображений
            'default/blogmanager/audio', // Загрузка/выгрузка/упорядочивание файлов музыки
            'default/blogmanager/video', // Загрузка/выгрузка/упорядочивание файлов видео
            'default/blogmanager/locationsmanage', // Управление географическими координатами
            'admin/user/save', // Админ панель. Добавить/Изменить пользователя сайта
            'admin/user/delete', // Админ панель. Удалить пользователя сайта
            'admin/blog/save', // Админ панель. Добавить/Изменить сообщение пользователя
            'admin/blog/delete', // Админ панель. Удалить сообщение пользователя
        );
        //Разрешим кеш для некоторых путей URL
        $arrYesCacheForURL = array(
            //----- Модуль=default; Контроллер=account -----
            'default/account/index', // Заходим в профиль автора
            //------ Модуль=default; Контроллер=index -----
            'default/index/index', // Сообщения всех пользователей
            'default/index/tag', // Сообщения всех пользователей по конкретной метке
            'default/index/modules', // Меню для дополнительных модулей
            //----- Модуль=default; Контроллер=user -----
            'default/user/index', // Сообщения конкретного пользователя
            'default/user/tag', // Сообщения конкретного пользователя по конкретной метке
            'default/user/archive', // Сообщения конкретного пользователя по месяцу
            'default/user/view', // Просмотр конкретного сообщения
            //----- Модуль=default; Контроллер=blogmanager -----
            'default/blogmanager/index', // Сообщения конкретного пользователя
            'default/blogmanager/details', // Получить подробную инф. по геокоординате
            'default/blogmanager/preview'     // Просмотр сообщения в режиме редактирования
        );

        // Определим конфигурирование кеша
        $config = Zend_Registry::get('config');
        $frontendCacheOptions = $config['resources']['cachemanager']['page']['frontend']['options'];
        $enablePageCache = (bool) $frontendCacheOptions['default_options']['cache'];
        $userCacheOptions = $config['resources']['cachemanager']['page']['cache'];
        $notEnableBrowser = strtolower($userCacheOptions['not_enable_browser']);
        $isDebugHeaderCache = (bool) $userCacheOptions['debug_header'];

        // Определим доступность кеша
        $enablePageCache = $enablePageCache && in_array($pathURL, $arrYesCacheForURL);
        if ($notEnableBrowser) {
            $enablePageCache = $enablePageCache && self::checkBrowser($notEnableBrowser);
        }


        // Определим очистку кеша   get
        foreach ($arrClearCacheForURL as $value) {
            switch ($value) {
                case 'default/blogmanager/edit':
                    if ($pathURL == $value && $request->isPost()) {
                        $clearPageCache = TRUE;
                    }
                    break;
                case 'default/blogmanager/setstatus':
                    if ($pathURL == $value && !$request->getPost('edit')) {
                        $clearPageCache = TRUE;
                    }
                    break;
                case 'default/blogmanager/images':
                    if ($pathURL == $value && !$request->getPost('download_images')) {
                        $clearPageCache = TRUE;
                    }
                    break;
                case 'default/blogmanager/audio':
                    if ($pathURL == $value && !$request->getPost('download_images')) {
                        $clearPageCache = TRUE;
                    }
                    break;
                case 'default/blogmanager/video':
                    if ($pathURL == $value && !$request->getPost('download_images')) {
                        $clearPageCache = TRUE;
                    }
                    break;
                case 'default/blogmanager/locationsmanage':
                    $action = $request->getPost('action');
                    if ($pathURL == $value && !($action == 'get_details') && !($action == 'get')) {
                        $clearPageCache = TRUE;
                    }
                    break;
                default:
                    if ($pathURL == $value) {
                        $clearPageCache = TRUE;
                    }
                    break;
            }
        }

        // Установим автора
        $auth = Zend_Auth::getInstance();
        $hasIdentity = $auth->hasIdentity();
        $identity = $auth->getIdentity();

        // Получим сам кеш
        $pageCache = self::getCache('page');

        // Очистим кеш
        if (self::isCleanCache()) {
            $pageCache->clean(Zend_Cache::CLEANING_MODE_ALL);
        } else {
            if ($clearPageCache) {
                $pageCache->clean(Zend_Cache::CLEANING_MODE_ALL);
                $dbCache = self::getCache('db');
                $dbCache->clean(Zend_Cache::CLEANING_MODE_ALL);
            }
        }

        // Старт кеша
        if ($enablePageCache) {

            // Установим параметр тип пользователя,
            // чтобы разделить кеш для разных пользователей
            if ($hasIdentity) {
                $_GET['_userType_'] = $identity->user_type;
            } else {
                $_GET['_userType_'] = 'guest';
            }

            // Старт кеша
            $result = $pageCache->start(false, TRUE);

            // Если есть признак вывода кеша - выведем этот признак
            if ($result && $isDebugHeaderCache) {
                echo '<p id="isDebugHeaderCache" style="display: none">DEBUG HEADER : This is a cached page !<br /></p>';
            }
        }
        return $result;
    }

    /**
     * getCache
     * Получить обьект Cache с помощью Zend_Cache_Manager
     * из конфигуратора -> application.ini
     *
     * @param string $aNameCache     //Имя кэша в Zend_Cache_Manager
     * @return Zend_Cache
     */
    static function getCache($aNameCache) {
        $cache = Zend_Controller_Front::getInstance()
                ->getParam('bootstrap')
                ->getResource('cachemanager')
                ->getCache($aNameCache);
        return $cache;
    }

    //=========== РАБОТА С URL ==================//

    /**
     * Получить URL
     *
     * @param array $aParams -> array('controller' => 'utility', 'action' => 'image');
     * @return string
     */
    static function getURL(array $aParams) {
        //----------------
        $helperUrl = new Zend_View_Helper_Url();
        $strLink = $helperUrl->url($aParams, null, true);
        return $strLink;
    }

    /**
     * Проверить правильность URL
     *
     * @param string $aURL //URL
     * @param bool $allow_unwise //Позволить применять в URL "неумные символы" -> "{", "}", "|", "\", "^", "`" 
     * @return bool
     */
    static function checkValid_URL($aURL, $allow_unwise = false) {
        //----------------
        Zend_Uri::setConfig(array('allow_unwise' => $allow_unwise));
        return Zend_Uri::check($aURL);
    }

    /**
     * Получить URL для ресурса
     * 
     * @param string $text
     * @return string 
     */
    static function getUrlRes($url_res) {
        $url_res = trim($url_res, '/');
        $url = self::getHostPortBaseURL();

        $url = str_replace('/index.php', '', $url);
        $url = rtrim($url, '/');

        if ($url_res) {
            $url .= '/';
            $url .= $url_res;
        }
        return $url;
    }

    /**
     * Получить URL без базового пути и параметров
     * как принимает роутер
     * 
     * пр. /user/user111/tag/flash
     *
     * @return string
     */
    static function getRouterURL() {

        // Получим URL запроса
        $_url = $_SERVER['REQUEST_URI'];

        // Удалим из URL базовый путь
        $_urlBase = self::getUrlRes('/');
        $_url = str_replace($_urlBase, '', $_url);

        // Удалим из URL параметры
        $arrURL = explode('?', $_url);
        $_url = $arrURL[0];

        // Добавим "/"
        $_url = ltrim($_url, '/');
        $_url = rtrim($_url, '/');
        $_url = '/' . $_url;

        return $_url;
    }

    /**
     * Получить URL из серверных параметров
     * 
     *
     * @return string
     */
    static function getServerURL() {

        // Получим URL запроса
        $url = $_SERVER['REQUEST_URI'];

        // Получим HOST
        $host = $_SERVER['HTTP_HOST'];

        // Получим схему
        $s = new Default_Plugin_String($_SERVER['SERVER_PROTOCOL']);
        $s = $s->toUpper();
        if ($s->beginsWith('HTTP')) {
            $scheme = 'http';
        } elseif ($s->beginsWith('HTTPS')) {
            $scheme = 'https';
        } elseif ($s->beginsWith('FTP')) {
            $scheme = 'ftp';
        } elseif ($s->beginsWith('MAILTO')) {
            $scheme = 'mailto';
        }

        $url = "$scheme://$host" . $url;
        return $url;
    }

    /**
     * Получить базовый путь URL
     * пр. /zf-myblog/public
     *
     * @return string
     */
    static function getBaseURL() {
        $request = new Zend_Controller_Request_Http();
        $basePath = $request->getBasePath();
        //$request->getHttpHost()
        return $basePath;
    }

    /**
     * Получить Host
     * пр. azot.cherkassy.net
     *
     * @return string
     */
    static function getHttpHost() {
        $request = new Zend_Controller_Request_Http();
        $httpHost = $request->getHttpHost();
        return $httpHost;
    }

    /**
     * Получить Host
     * пр. azot.cherkassy.net:8080
     *
     * @return string
     */
    static function getHttpHostAndPort() {

        $port = self::getPort();
        $httpHost = str_replace($port, '', self::getHttpHost());

        $strLink = $httpHost . $port;
        return $strLink;
    }

    /**
     * Получить Port
     * пр. http://mysite.com:8080/zf-azot_m5/public
     *
     * @return string
     */
    static function getPort() {
        $port = $_SERVER['SERVER_PORT'] != 80 ? ":{$_SERVER['SERVER_PORT']}" : '';
        return $port;
    }

    /**
     * Получить HOST and Port and BaseURL
     * пр. https://localhost:8080/zf-myblog/public
     *
     * @param array $aParams
     * @return string
     */
    static function getHostPortBaseURL() {
        $protocol = isset($_SERVER['HTTPS']) ? 'https' : 'http';
        $port = self::getPort();
        $httpHost = str_replace($port, '', self::getHttpHost());

        $strLink = "$protocol://" . $httpHost . $port . self::getBaseURL();
        return $strLink;
    }

    /**
     * Получить полный путь - URL
     * пр. http://localhost:8080/zf-myblog/public/user/login
     *
     * @param array $aParams
     * @return string
     */
    static function getFullURL(array $aParams) {
        $protocol = $_SERVER['HTTPS'] ? 'https' : 'http';
        $port = self::getPort();
        $httpHost = str_replace($port, '', self::getHttpHost());

        $strLink = "$protocol://" . $httpHost . $port . self::getURL($aParams);
        return $strLink;
    }

    /**
     * Получить полный путь к ресурсу - URL
     * пр. http://localhost:8080/zf-myblog/public/images/system/PHPLogo.gif
     *
     * @param array $aParams
     * @return string
     */
    static function getFullURL_Res($url_res) {
        $strLink = self::getUrlRes($url_res);
        return $strLink;
    }

    /**
     * Получить полный путь - URL для текущего запроса
     * пр. http://localhost:8080/zf-myblog/public/user/login?post_id=33&tag_id=123
     *
     * @return string
     */
    static function getUrlRequest() {
        $protocol = $_SERVER['HTTPS'] ? 'https' : 'http';
        $server = $_SERVER['HTTP_HOST'];
        $port = $_SERVER['SERVER_PORT'] != 80 ? ":{$_SERVER['SERVER_PORT']}" : '';
        $path = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . '/';
        $params = '';
        foreach ($_GET as $key => $value) {
            if ($params) {
                $params .= '&' . $key . '=' . $value;
            } else {
                $params = '?' . $key . '=' . $value;
            }
        }
        return "$protocol://$server$port$path$params";
    }

    //=========== СОЗДАНИЕ ПАРОЛЯ ==================  

    /**
     * Получить пароль 
     * характеристики пароля можно задать 
     * с помощью параметров
     * 
     * @param array $aConfig    Default  array('length'		=>	8,
      'alpha_upper_include'	=>	TRUE,
      'alpha_lower_include'	=>	TRUE,
      'number_include'	=>	TRUE,
      'symbol_include'	=>	TRUE,)
     * 
     * @return string 
     */
    static function createPassword(array $aConfig) {
        $password = new Default_Plugin_PasswordGenerator($aConfig);
        return $password->get_password();
    }

    //=========== РАБОТА С ПОЧТОЙ ==================//

    /**
     * Отправить почту
     * 
     * @param array $aMailParams 
     * @return void 
     */
    static function sendMail(array $aMailParams) {
        $to_email = $aMailParams['to']['email'];
        $to_name = $aMailParams['to']['name'];
        $mail_subject = $aMailParams['subject'];
        //--------------------------    
        try {
            //Получим данные конфигурации почты
            $config = Zend_Registry::get('config');

            //Отправим сообщение по почте
            $tr = new Zend_Mail_Transport_Smtp($config['email']['smtp'], array('port' => 25));
            Zend_Mail::setDefaultTransport($tr);
            $mail = new Zend_Mail($config['email']['charset']);
            $mail->setSubject($aMailParams['subject']);
            $mail->setBodyText($aMailParams['body']);

            if (!$aMailParams['from']) {
                $aMailParams['from'] = $config['email']['from'];
            }

            $headerAddresses = array_intersect_key($aMailParams, self::$_methodMapHeaders);
            if (count($headerAddresses)) {
                foreach ($headerAddresses as $header => $address) {
                    $method = self::$_methodMapHeaders[$header];
                    if (is_array($address) && isset($address['name']) && !is_numeric($address['name'])
                    ) {
                        $params = array(
                            $address['email'],
                            $address['name']
                        );
                    } else if (is_array($address) && isset($address['email'])) {
                        $params = array($address['email']);
                    } else {
                        $params = array($address);
                    }
                    call_user_func_array(array($mail, $method), $params);
                }
            }

            $mail->send();

            // Запомним в логе сообщений
            $message = "Params of email: to-\"$to_email\"; name-\"$to_name\"; subject-\"$mail_subject\";";
            $logMsg = Zend_Registry::get('Zend_Log');
            $logMsg->mail_ok($message);

            // Запомним в логе статистики
            $logStat = Zend_Registry::get('Zend_LogStat');
            $serializer = Zend_Serializer::factory('PhpSerialize');
            $serialized = $serializer->serialize(array(
                'to_email' => $to_email,
                'to_name' => $to_name,
                'subject' => $mail_subject
            ));
            $logStat->mail_ok($serialized);
        } catch (Exception $e) {// Ошибка передачи почты
            // Запомним в логе сообщений
            $message = "Params of email: to-\"$to_email\"; name-\"$to_name\"; subject-\"$mail_subject\";";
            $message .= "\n\n" . $e->getMessage();
            $logMsg = Zend_Registry::get('Zend_Log');
            $logMsg->mail_err($message);

            // Запомним в логе ошибок
            $logEx = Zend_Registry::get('Zend_LogEx');
            $message .= "\n\n" . $e->getTraceAsString();
            $logEx->err($message);

            throw $e;
        }
    }

    /**
     * Создать почтовое сообщение
     *
     * @param array $aMailParams
     * @return Zend_Mail
     */
    static function createMail(array $aMailParams) {
        try {
            //Получим данные конфигурации почты
            $config = Zend_Registry::get('config');

            //Отправим сообщение по почте
            $tr = new Zend_Mail_Transport_Smtp($config['email']['smtp'], array('port' => 25));
            Zend_Mail::setDefaultTransport($tr);
            if ($aMailParams['charset'])
                $charset = $aMailParams['charset'];
            else
                $charset = $config['email']['charset'];
            $mail = new Zend_Mail($charset);
            if ($aMailParams['body'])
                $mail->setBodyText($aMailParams['body']);
            if ($aMailParams['subject'])
                $mail->setSubject($aMailParams['subject']);

            $headerAddresses = array_intersect_key($aMailParams, self::$_methodMapHeaders);
            if (count($headerAddresses)) {
                foreach ($headerAddresses as $header => $address) {
                    $method = self::$_methodMapHeaders[$header];
                    if (is_array($address) && isset($address['name']) && !is_numeric($address['name'])) {
                        $params = array(
                            $address['email'],
                            $address['name']
                        );
                    } else if (is_array($address) && isset($address['email'])) {
                        $params = array($address['email']);
                    } else {
                        $params = array($address);
                    }
                    call_user_func_array(array($mail, $method), $params);
                }
            }
            return $mail;
        } catch (Exception $e) {// Ошибка передачи почты
            // Запомним в логе сообщений
            $to_email = $aMailParams['to']['email'];
            $to_name = $aMailParams['to']['name'];
            $mail_subject = $aMailParams['subject'];
            $message = "Params of e-mail: to-\"$to_email\"; name-\"$to_name\"; subject-\"$mail_subject\";";
            $logMsg = Zend_Registry::get('Zend_Log');
            $logMsg->mail_err($message);

            throw $e;
        }
    }

    //=========== РАБОТА С ШАБЛОНИЗАТОРОМ СМАРТИ ==================//

    /**
     * Создание обьекта для инициализации шаблонизатора Smarty
     *
     * @return Default_Plugin_ViewSmarty
     */
    static function createViewSmarty() {
        $request = Zend_Controller_Front::getInstance()->getRequest();
        if ($request->getModuleName()) {
            $module = $request->getModuleName();
        } else {
            $module = 'default';
        }
        return new Default_Plugin_ViewSmarty($module);
    }

    //=========== ЛОКАЛИЗАЦИЯ САЙТА ==================//
    /*
     * Получить параметр для локализации сайта
     *
     * @param  string $aLanguage - язык перевода (en,ru,uk)
     * @return string - язык_страна(en -> en_US, ru -> ru_RU, uk -> uk_UA)
     */
    static function getLocalParam($aLanguage) {

        $strParam = "";
        //----------------
        switch ($aLanguage) {
            // Английский язык
            case "en":
                $strParam = "en_US";
                break;
            // Русский язык
            case "ru":
                $strParam = "ru_RU";
                break;
            // Украинский язык
            case "uk":
                $strParam = "uk_UA";
                break;
        }

        return $strParam;
    }

    //=========== ЦВЕТОВАЯ СХЕМА САЙТА ==================//
    /*
     * Получить цветовую схему сайта
     *
     * @param  string $aScheme Цветовая схема из конфигурации
     * @return string  Цветовая схема
     */
    static function getUserScheme($aScheme) {
        $arr_query = array();
        $result = $aScheme;
        //--------------------
        $url = self::getServerURL();
        $querystring = parse_url($url, PHP_URL_QUERY);
        if ($querystring) {
            parse_str($querystring, $arr_query);
            if (isset($arr_query['scheme']) && ($arr_query['scheme'] !== $aScheme)) {
                $result = $arr_query['scheme'];
            }
        }
        return $result;
    }

    //=========== Работа с PDF ==================//

    /*
     * Преобразовать HTML -> PDF файл
     *
     * @param array $params - параметры для преобразования HTML -> PDF
     *   - report (имя отчета);
     *   - isCommonFont (признак использования стандартных фонтов);
     *   - html (html строка);
     *   - pathStylesheet (путь к файлу CSS)
     * @return void
     */

    static function mpdfGenerator_Html2PDF($params = array()) {

        //-----------------------------
        if ((!$params['pdfReport']) || (!$params['html'])) {
            Default_Plugin_StrBox::errUser('ERR_CREATE_PDF_REPORT');
        }

        $mode = '';
        // Получим URLLogoReport
        $config = Zend_Registry::get('config');
        $urlLogoReport = $config['user']['main']['logo_report'];
        $urlLogoReport = self::getFullURL_Res($urlLogoReport);
        //------------------------------------------------------
        // Установим значения по умолчанию
        $defaults = array(
            'pdfReport' => '',
            'html' => '',
            'isCommonFont' => FALSE,
            'pathStylesheet' => 'css/report/mpdf-style.css',
            'isHeaders' => TRUE,
            'isFooters' => TRUE,
            'mirrorMargins' => TRUE,
            'headerLeftMargin' => '',
            'headerCentreMargin' => $urlLogoReport,
            'headerRightMargin' => '{PAGENO}/{nbpg}',
            'footerLeftMargin' => '{DATE Y-m-j}',
            'footerCentreMargin' => '',
            'footerRightMargin' => '',
            'pageFormat' => 'A4', //Возможные форматы: пр. A3, A3-L, A4, A4-L ...
        );

        // Обьединим два массива
        $params = array_merge($defaults, $params);

        if (!$params['footerRightMargin']) {
            $params['footerRightMargin'] = self::getFullUrl_For_FilePDF($params['pdfReport']);
        }

        // Установим значения переменных из массива
//        extract($params, EXTR_OVERWRITE);


        try {
            // Изменим параметры PHP 
            self::iniSetConfig_PHP(array(
                "memory_limit" => "500M", //"256M",
                "max_execution_time" => "300"//"240"
            ));

//            require_once("mpdf_source.php");
            require_once("mpdf.php");

            if ($params['isCommonFont']) {
                $mode = 'c';
            }

            $isHeaders = (bool) $params['isHeaders'];
            $isFooters = (bool) $params['isFooters'];

            if ($isHeaders || $isFooters) {
                $mpdf = new mPDF($mode, $params['pageFormat'], '', '', 15, 15, 32, 20, 10, 10);
            } else {
                $mpdf = new mPDF($mode, $params['pageFormat']);
            }

            // Установим параметры для оптимизации (уменьшим время испольнения и используемую память)
            $mpdf->useOnlyCoreFonts = true;
            $mpdf->useSubstitutions = false;
            $mpdf->simpleTables = true; // Уменьшает время выполнения
            $mpdf->packTableData = true; // Уменьшает используемую память
            $mpdf->use_kwt = true; //Keep-with-table  Оставить заголовок таблицы вместе с маблицей на одной странице
//            $mpdf->shrink_tables_to_fit=0;
//            $mpdf->hyphenate = true;
//            $mpdf->SHYlang = 'ru';
//            $mpdf->SHYleftmin = 3;
            // Определим заголовок страницы
            $header = " 
            <table width=\"100%\" style=\"border-bottom: 1px solid #000000; vertical-align: bottom;  font-weight: bold; font-size: 14pt; color: #000088;\"><tr>
            <td width=\"33%\"><span style=\"\">{$params['headerLeftMargin']}</span></td>
            <td width=\"33%\" align=\"center\"><img src=\"{$params['headerCentreMargin']}\" /></td>
            <td width=\"33%\" style=\"text-align: right;\"><span style=\"\">{$params['headerRightMargin']}</span></td>
            </tr></table>
            ";
            // Определим подвал страницы
            $footer = "
            <table width=\"100%\" style=\"vertical-align: bottom;  font-size: 14pt; color: #000088; font-weight: bold; font-style: italic;\"><tr>
            <td width=\"33%\"><span style=\"\">{$params['footerLeftMargin']}</span></td>
            <td width=\"33%\" align=\"center\" style=\"\">{$params['footerCentreMargin']}</td>
            <td width=\"33%\" style=\"text-align: right; \">{$params['footerRightMargin']}</td>
            </tr></table>
            ";

            if ($mirrorMargins) {

                $headerE = "
                <table width=\"100%\" style=\"border-bottom: 1px solid #000000; vertical-align: bottom;  font-weight: bold; font-size: 14pt; color: #000088;\"><tr>
                <td width=\"33%\"><span style=\"\"><span style=\"\">{$params['headerRightMargin']}</span></span></td>
                <td width=\"33%\" align=\"center\"><img src=\"{$params['headerCentreMargin']}\" /></td>
                <td width=\"33%\" style=\"text-align: right;\"><span style=\"\">{$params['headerLeftMargin']}</span></td>
                </tr></table>
                ";

                $footerE = "
                <table width=\"100%\" style=\"vertical-align: bottom;  font-size: 14pt; color: #000088; font-weight: bold; font-style: italic;\"><tr>
                <td width=\"33%\"><span style=\"\">{$params['footerRightMargin']}</span></td>
                <td width=\"33%\" align=\"center\" style=\"\">{$params['footerCentreMargin']}</td>
                <td width=\"33%\" style=\"text-align: right; \">{$params['footerLeftMargin']}</td>
                </tr></table>
                ";
                if ($isHeaders) {
                    $mpdf->mirrorMargins = TRUE; // Use different Odd/Even headers and footers and mirror margins
                    $mpdf->SetHTMLHeader($headerE, 'E');
                }

                if ($isFooters) {
                    $mpdf->mirrorMargins = TRUE; // Use different Odd/Even headers and footers and mirror margins
                    $mpdf->SetHTMLFooter($footerE, 'E');
                }
            }

            if ($isHeaders) {
                $mpdf->SetHTMLHeader($header);
            }

            if ($isFooters) {
                $mpdf->SetHTMLFooter($footer);
            }


            $html = $params['html'];
            //$html = '';
            //$params['pathStylesheet'] = '';

            if ($params['pathStylesheet']) {
                $stylesheet = file_get_contents($params['pathStylesheet']);
                $mpdf->WriteHTML($stylesheet, 1);
                $mpdf->WriteHTML($html, 2);
            } else {
                $mpdf->WriteHTML($html);
            }

            // Получим директорию сохранения файлов
            $dirFilePDF = self::getPath_For_FilePDF($params['pdfReport']);

            // Сохраним файл на серверном ресурсе пользователя 
            $mpdf->Output($dirFilePDF, 'F');
        } catch (Exception $exc) {
            throw new Exception($exc->getMessage());
        }
        // Возвратим URL путь, полученного файла PDF
        return Default_Plugin_SysBox::getFullUrl_For_FilePDF($params['pdfReport']);
    }

    /*
     * Получить URL для файла PDF
     *
     * @param  string $report - название отчета для PDF
     * @return string
     */

    static function getUrl_For_FilePDF($report) {
        $pdfFile = '/files/mPDF/' . $report . '.pdf';
        // Получим URL сохраненного файла PDF
        $urlFilePDF = self::getUserUploadUrl() . $pdfFile;
        return $urlFilePDF;
    }

    /*
     * Получить URL для файла PDF
     *
     * @param  string $report - название отчета для PDF
     * @return string
     */

    static function getFullUrl_For_FilePDF($report) {
        $pdfFile = '/files/mPDF/' . $report . '.pdf';
        // Получим URL сохраненного файла PDF
        $urlFilePDF = self::getUserUploadUrl() . $pdfFile;
        //self::getUrlRes($urlFilePDF);
        $urlFilePDF = self::getFullURL_Res($urlFilePDF);
        return $urlFilePDF;
    }

    /*
     * Получить путь для файла PDF
     *
     * @param  string $report - название отчета для PDF
     * @return string
     */

    static function getPath_For_FilePDF($report) {
        if ($report) {
            $pdfFile = '/files/mPDF/' . $report . '.pdf';
        } else {
            $pdfFile = '/files/mPDF';
        }

        // Получим URL сохраненного файла PDF
        $dirFilePDF = self::getUserUploadDir() . $pdfFile;
        return $dirFilePDF;
    }

    //=========== Информация о броузере ==================//

    /*
     * Получить информацию о текущем броузере
     *
     * @return array
     * array(
      'userAgent' => $u_agent,
      'name' => $bname,
      'short_name' => $ub,
      'version' => $version,
      'majorver' => $majorver,
      'minorver' => $minorver,
      'platform' => $platform,
      'pattern' => $pattern
      );
     * 
     */

    static function getBrowser() {
        $u_agent = $_SERVER['HTTP_USER_AGENT'];
        $bname = 'Unknown';
        $platform = 'Unknown';
        $version = "";
        $majorver = '?';
        $minorver = '?';
        //-------------------------
        //First get the platform?
        if (preg_match('/linux/i', $u_agent)) {
            $platform = 'linux';
        } elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
            $platform = 'mac';
        } elseif (preg_match('/windows|win32/i', $u_agent)) {
            $platform = 'windows';
        }

        // Next get the name of the useragent yes seperately and for good reason
        if (preg_match('/MSIE/i', $u_agent) && !preg_match('/Opera/i', $u_agent)) {
            $bname = 'Internet Explorer';
            $ub = "IE";
        } elseif (preg_match('/Firefox/i', $u_agent)) {
            $bname = 'Mozilla Firefox';
            $ub = "Firefox";
        } elseif (preg_match('/Chrome/i', $u_agent)) {
            $bname = 'Google Chrome';
            $ub = "Chrome";
        } elseif (preg_match('/Safari/i', $u_agent)) {
            $bname = 'Apple Safari';
            $ub = "Safari";
        } elseif (preg_match('/Opera/i', $u_agent)) {
            $bname = 'Opera';
            $ub = "Opera";
        } elseif (preg_match('/Netscape/i', $u_agent)) {
            $bname = 'Netscape';
            $ub = "Netscape";
        } elseif (preg_match('/Gecko/i', $u_agent)) {
            $bname = 'Gecko';
            $ub = "Gecko";
        }

        // finally get the correct version number
        $known = array('Version', $ub, 'other');
        $pattern = '#(?<browser>' . join('|', $known) .
                ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
        if (!preg_match_all($pattern, $u_agent, $matches)) {
            // we have no matching number just continue
        }

        // see how many we have
        $i = count($matches['browser']);
        if ($i != 1) {
            //we will have two since we are not using 'other' argument yet
            //see if version is before or after the name
            if (strripos($u_agent, "Version") < strripos($u_agent, $ub)) {
                $version = $matches['version'][0];
            } else {
                $version = $matches['version'][1];
            }
        } else {
            $version = $matches['version'][0];
        }

        // check if we have a number
        if ($version == null || $version == "") {
            $version = "?";
        } else {
            $versions = explode('.', $version);
            if (count($versions) > 1) {
                $majorver = (int) $versions[0];
                $minorver = (int) $versions[1];
            } else {
                $majorver = (int) $versions[0];
                $minorver = 0;
            }
        }

        return array(
            'userAgent' => $u_agent,
            'name' => $bname,
            'short_name' => $ub,
            'version' => $version,
            'majorver' => $majorver,
            'minorver' => $minorver,
            'platform' => $platform,
            'pattern' => $pattern
        );
    }

    /*
     * Проверить информацию о текущем броузере
     *
     * @param  string $aBrowsers // Броузеры и их majorver версии пр. "IE;firefox/3"
     * @return bool
     * 
     */

    static function checkBrowser($aBrowsers) {
        //-------------------
        if ($aBrowsers) {
            $browsers = explode(';', strtolower($aBrowsers));
            $currentBrowser = self::getBrowser();
            $short_name = strtolower($currentBrowser['short_name']);
            $majorver = $currentBrowser['majorver'];
            // Сравним ожидаемый броузер и его версию 
            // с текущим броузером и его версией
            foreach ($browsers as $browser) {
                $arrBrowser = explode('/', $browser);
                if (count($arrBrowser) > 1) {
                    $result = ($short_name == $arrBrowser[0]);
                    if ($result) {
                        $aMajorver = (int) $arrBrowser[1];
                        $result = ($majorver <= $aMajorver);
                        if ($result) {
                            return FALSE;
                        }
                    }
                } else {
                    $result = ($short_name == $arrBrowser[0]);
                    if ($result) {
                        return FALSE;
                    }
                }
            }
        }
        return TRUE;
    }

    /*
     * Признак использования IE
     *
     * @return bool
     * 
     */

    static function isIE() {
        $u_agent = $_SERVER['HTTP_USER_AGENT'];
        $ub = False;
        if (preg_match('/MSIE/i', $u_agent)) {
            $ub = True;
        }
        return $ub;
    }

    //=========== Доп. ф-ии ==================//

    /*
     * Получить информацию о РНР и др. инф. о системе
     *
     * @param  string $aLanguage - язык перевода (en,ru,uk)
     * @return string - язык_страна(en -> en_US, ru -> ru_RU, uk -> uk_UA)
     */
    static function getPHPInfo() {
        $str_xml_begin = '<?xml version="1.0" encoding="utf-8"?>';
        //---------------------------
        // Получить инф. о PHP
        ob_start();
        phpinfo();
        $info = ob_get_contents();
        ob_end_clean();

        // Создадим обьект парсера
        // и получим содержание тега <BODY>...</BODY>
        $obj = new Default_Plugin_Domparser();
        $obj->setUrl($obj, $info);
        $info = $obj->getBody();

        // Получим строку в формате XML
        $html = $str_xml_begin . $info;
        $html = str_replace('&nbsp;', '&#160;', $html);

        //Проверка на корректость XML
        $domDoc = new DOMDocument('1.0', 'utf-8');
        $success = $domDoc->loadXML($html);
        if (!$success) {
            return self::Translate('Ошибка документа - DOM') . '!';
        }

        // Создадим новый документ
        $domNew = new Default_Plugin_DomBox('1.0', 'utf-8');
        // Добавим корневой тег
        $root = $domNew->createElement('div');
        $root->setAttribute('class', 'report-content');
        $root = $domNew->appendChild($root);

        // Найдем нужную инф-ию и вставим ее в новый документ
        $domQuery = new Zend_Dom_Query();
        $domQuery->setDocumentXml($html, "utf-8");

        // Заменим сетевые адреса изображений на локальные адреса
        $images = $domQuery->query('img');
        $count = $images->count();
        if ($count) {
            foreach ($images as $image) {
                $alt = $image->getAttribute('alt');
                if ($alt == 'PHP Logo') {
                    $url = self::getFullURL_Res('/images/system/PHPLogo.gif');
                    $image->setAttribute('src', $url);
                }
                if ($alt == 'Zend logo') {
                    $url = self::getFullURL_Res('/images/system/ZendLogo.gif');
                    $image->setAttribute('src', $url);
                }
            }
        }
        $domDoc = $images->getDocument();
        $html = $domDoc->saveXml();
        $domQuery->setDocumentXml($html, "utf-8");

        // Обернем таблицы в DIV и установим некоторые атрибуты
        $listElements = $domQuery->query('div.center > *');
        $count = $listElements->count();

        $count_tables = 10;
        foreach ($listElements as $el) {
            $nodeName = $el->nodeName;

            // Если это таблица обернем ее в DIV
            if ($nodeName == 'table') {

                if ($count_tables > 0) {
                    $count_tables--;
                } else {
//                    continue;
                }

                $el->setAttribute('width', '100%');

                $tableContainer = $domNew->createElement('div');
                $tableContainer->setAttribute('class', 'table-container');
                $tableContainer = $root->appendChild($tableContainer);
                $domNew->appendChilds($tableContainer, $el);
            } else {
                $domNew->appendChilds($root, $el);
            }
        }

        $html = $domNew->saveXML();
        $html = str_replace($str_xml_begin, '', $html);
        return $html;
    }

    /*
     * Получить значение в байтах 
     * из других форматов: гига байт, мега байт, кило байт
     *
     * @param  string $str
     * @return int
     */

    static function toBytes($str) {
        $val = trim($str);
        $last = strtolower($str[strlen($str) - 1]);
        switch ($last) {
            case 'g': $val *= 1024;
            case 'm': $val *= 1024;
            case 'k': $val *= 1024;
        }
        return $val;
    }

    /*
     * Установить новые значения для ini файла PHP
     *
     * @param  array $arrParams
     * @return void
     */

    static function iniSetConfig_PHP(array $arrParams) {
        foreach ($arrParams as $key => $value) {
            ini_set($key, $value);
        }
    }

    /**
     * Print for debug
     *
     * @param array|object|xml  $var     //Переменная для печати
     * @param string  $forceType         //Тип переменной: array, object, xml
     * @param bool  $bCollapsed          //Признак раскрытия/закрытия узлов значений переменных
     * @param bool  $isAjax              //Признак Ajax запроса, при этом запускается буфер выходных данных
     * 
     */
    static function printR($var, $forceType = "array", $bCollapsed = false, $isAjax = false) {
        if ($isAjax) {
            ob_start();
        }
        new Default_Plugin_DBug($var, $forceType, $bCollapsed);
    }

    /**
     * Print for debug
     *
     * @param array|object|xml  $var     //Переменная для печати
     * @param bool  $isAjax              //Признак Ajax запроса, при этом запускается буфер выходных данных
     * 
     */
    static function varDump($var, $isAjax = false) {
        if ($isAjax) {
            ob_start();
        }
        var_dump($var);
    }

    /**
     * Get key for google maps
     *
     * @param string $host 
     * @return string Key for google maps
     */
    static function getGoogleMapsKey($aHost) {
        $config = Zend_Registry::get('config');
        $httpHost = self::getHttpHost();
        //--------------------
        if ($aHost) {
            $host = $aHost;
        } else {
            $arrHost = explode(':', $httpHost);
            $host = str_replace('.', '-', $arrHost[0]);
        }

        if (isset($config['google']['maps']['key'][$host])) {
            $key = $config['google']['maps']['key'][$host];
        } else {
            $key = '';
        }
        return $key;
    }

}
