<?php

/**
 * smarty_function_getlang
 *
 * Функция Smarty - geturl
 * получить язык сайта
 *
 * @uses       
 * @package    Module-Default
 * @subpackage Views.Plugins
 */

/**
 * Получить язык сайта
 * 
 * @param Smarty $smarty
 * @return string 
 */
function smarty_function_getlang($smarty) {
    return Default_Plugin_SysBox::getTranslateLocale();
}

?>