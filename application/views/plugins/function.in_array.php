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
 * Проверить, есть ли значение в массиве?
 * 
 * @param mixed $aNeedle
 * @param array $aArray
 * @param Smarty $smarty
 * @return void 
 */
function smarty_function_in_array($params,$smarty) {
    extract($params);// Преобразуем массив параметров в переменные
    $result =  in_array($aNeedle, $aArray);
    $smarty->assign($assign, $result);
}

?>