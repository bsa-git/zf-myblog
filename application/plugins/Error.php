<?php

/**
 * Error
 * 
 * Ручной обработчик ошибок
 * 
 * @author Александр Махомет aka San для http://zendframework.ru
 */
class Default_Plugin_Error {

    /**
     * Управление ошибками
     *
     * @param exception $exception Перехватываемое исключение
     */
    public static function catchException(Exception $exception) {
        $params = '';
        // Получение текста ошибки
        $code = $exception->getCode();
        $file = $exception->getFile();
        $line = $exception->getLine() . "\n\n";
        $message = $exception->getMessage() . "\n\n";
        $trace = $exception->getTraceAsString() . "\n\n";
        //-----------------------
        // Получим строку ошибки
        $strMessage = 'EXCEPTION: ' . $code . '; FILE: ' . $file . '; LINE: ' . $line . 'MESSAGE: ' . $message;


        //Получим параметры запроса
        $front = Zend_Controller_Front::getInstance();
        if ($front) {
            $request = $front->getRequest();
            if ($request) {
                $params = var_export($request->getParams(), true);
            }
        }

        // Получим обьекты логеров
        $logMsg = Zend_Registry::get('Zend_Log');
        $logEx = Zend_Registry::get('Zend_LogEx');

        // Запомним ошибку в лог файле - Messages.log
        $logMsg->emerg($strMessage);
        $strMessage .= $trace . 'PARAMS: ' . $params;
        // Запомним ошибку в лог файле - Exceptions.log
        $logEx->emerg($strMessage);

        // Очистим кеш, что бы ошибка не запомнилась
        $pageCache = Default_Plugin_SysBox::getCache('page');
        $pageCache->clean(Zend_Cache::CLEANING_MODE_ALL);
        $dbCache = Default_Plugin_SysBox::getCache('db');
        $dbCache->clean(Zend_Cache::CLEANING_MODE_ALL);

        //Получим переменную окружения - (production, testing, development)
        $env = APPLICATION_ENV;

        // Если включен режим отладки отображаем сообщение о ошибке на экран
        echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
                <html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
                <head>
                    <title>This site is in Maintenance</title>
                    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                </head>
                <body>
                        <h1>Application Error</h1>';

        if ($env == 'development') {
            Zend_Debug::dump($strMessage);
        } else {// Иначе выводим сообщение об ошибке
            echo '<h2>System error! Please try again later.</h2>';
            echo 'EXCEPTION: ' . $code . '; MESSAGE: ' . $message;
        }
        echo '</body></html>';
        exit();
    }

}