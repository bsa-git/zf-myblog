<?php

/**
 * smarty_function_in_array
 *
 * Функция Smarty - in_array
 * Check whether there is a value in the array
 *
 * @uses       
 * @package    Module-Default
 * @subpackage Views.Plugins
 * @author   Sergii Beskorovainyi <bsa2657@yandex.ru>
 * @license  MIT <http://www.opensource.org/licenses/mit-license.php>
 * @link     https://github.com/bsa-git/zf-myblog/
 */

/**
 * Check whether there is a value in the array?
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