<?php

/**
 * smarty_function_get_type_value
 *
 * Функция Smarty - get_type_value
 * получить тип переменной:
 * - string;
 * - float;
 * - int;
 * - bool;
 *
 * @uses       
 * @package    Module-Default
 * @subpackage Views.Plugins
 */

/**
 * Получить тип переменной
 * 
 * @param string|int|float|bool $my_value
 * @param Smarty $smarty
 * 
 * @return string 
 */
function smarty_function_get_type_value($params, $smarty) {

    if (is_bool($params['value'])) {
        $smarty->assign($params['assign'], 'bool');
        return;
    }
    if (is_float($params['value'])) {
        $smarty->assign($params['assign'], 'float');
        return ;
    }
    if (is_int($params['value'])) {
        $smarty->assign($params['assign'], 'int');
        return ;
    }
    if (is_string($params['value'])) {
        $smarty->assign($params['assign'], 'string');
        return ;
    }

}

?>