<?php

/**
 * index.php
 * 
 * Точка входа в систему управления сайтом
 * 
 * @package public
 * 
 */
$_startTime = microtime(1);

// Set APPLICATION_ENV
putenv("APPLICATION_ENV=development");

// Ensure zf library/ is on include_path /../library
set_include_path(
        implode(PATH_SEPARATOR, array(realpath(dirname(__FILE__) . '/../library'), get_include_path())));

// Установим путь к файлам mPDF
set_include_path(
        implode(PATH_SEPARATOR, array(realpath(dirname(__FILE__) . '/../library/mPDF'), get_include_path())));

// Установим путь к файлам phpQuery
set_include_path(
        implode(PATH_SEPARATOR, array(realpath(dirname(__FILE__) . '/../library/phpQuery'), get_include_path())));

// Определим путь к временным файлам и кешированию для MPDF
defined('_MPDF_TEMP_PATH') || define('_MPDF_TEMP_PATH', realpath(dirname(__FILE__) . '/../data/tmp/mpdf'));

// Определим путь к кешированию шрифтов для MPDF 
defined('_MPDF_TTFONTDATAPATH') || define('_MPDF_TTFONTDATAPATH', realpath(dirname(__FILE__) . '/../data/tmp/mpdfttfontdata/ttf/empty'));

// Define path to application public directory
defined('APPLICATION_PUBLIC') || define('APPLICATION_PUBLIC', realpath(dirname(__FILE__) . '/../public'));

// Define path to application directory
defined('APPLICATION_PATH') || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Define path to application data directory
defined('APPLICATION_DATA') || define('APPLICATION_DATA', realpath(dirname(__FILE__) . '/../data'));

// Define path to application temp directory
defined('APPLICATION_TEMP') || define('APPLICATION_TEMP', realpath(dirname(__FILE__) . '/../data/tmp'));

// Define path to application base directory
defined('APPLICATION_BASE') || define('APPLICATION_BASE', realpath(dirname(__FILE__) . '/../'));

// Define path to application template directory
defined('APPLICATION_TEMPLATES') || define('APPLICATION_TEMPLATES', realpath(dirname(__FILE__) . '/../application/views/templates'));

// Define path to application config directory
defined('APPLICATION_CONFIG') || define('APPLICATION_CONFIG', realpath(dirname(__FILE__) . '/../application/configs'));

// Define application environment
defined('APPLICATION_ENV') || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

// Получим используемую память в начале скрипта
$memoryUsage_StartIndexPHP = memory_get_usage();
$memoryUsage_StartIndexPHP = round($memoryUsage_StartIndexPHP / 1024, 3);
$memoryUsage_StartIndexPHP = number_format($memoryUsage_StartIndexPHP, 3, '.', ' ');

try {
    
    //Set timezone
    date_default_timezone_set("UTC");

    // Проверим наличие файла mpdf.php
    // Если нет, то выдадим ошибку!
    $path = APPLICATION_BASE . '/library/Zend/Application.php';
    if (!is_file($path)) {
        echo  'Not installed the library Zend Framework 1.<br> The library should be placed in a folder \'/library/Zend\'.<br> The latest version of the library can be downloaded at - <a href="http://framework.zend.com/downloads/latest">here</a>';
        return;
    }

    /** Zend_Application */
    require_once 'Zend/Application.php';
    require_once APPLICATION_PATH . '/plugins/Error.php';

    

    // Автозагрузчик для composer
    require_once APPLICATION_BASE . '/vendor/autoload.php';

    // Create application, bootstrap, and run
    $application = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini');

    //Запомним время выполнения (index.php + new Zend_Application)
    $_startTime2 = microtime(1);
    $totalTime_StartApplication = $_startTime2 - $_startTime;

    // Получим используемую память в начале скрипта
    $memoryUsage_StartApplication = memory_get_usage();
    $memoryUsage_StartApplication = round($memoryUsage_StartApplication / 1024, 3);
    $memoryUsage_StartApplication = number_format($memoryUsage_StartApplication, 3, '.', ' ');

    // Выполним загрузчик (инициализацию)
    $application->bootstrap();

    //------------ Установим время выполнения Bootstrap -------------
    //Запомним информацию о выполнении скрипта
    $_startTime3 = microtime(1);
    $totalTime_Bootstrap = $_startTime3 - $_startTime2; //Default_Plugin_SysBox::profilerTime2Registry($_startTime2, $infoProfiler);
    // Получим используемую память после Bootstrap
    $memoryUsage_Bootstrap = Default_Plugin_SysBox::showMemoryUsage('kb');

    //Сохраним времена выполнения в регистре
    Zend_Registry::set("Duration_StartApplication", $totalTime_StartApplication);
    Zend_Registry::set("Duration_Bootstrap", $totalTime_Bootstrap);
    Zend_Registry::set("MemoryUsage_StartIndexPHP", $memoryUsage_StartIndexPHP);
    Zend_Registry::set("MemoryUsage_StartApplication", $memoryUsage_StartApplication);
    Zend_Registry::set("MemoryUsage_Bootstrap", $memoryUsage_Bootstrap);

    // Выполним цикл деспетчеризации
    $application->run();
} catch (Zend_Exception $e) {
    // Перехват исключений 
    Default_Plugin_Error::catchException($e);
} catch (Exception $e) {
    // Перехват исключений 
    Default_Plugin_Error::catchException($e);
}