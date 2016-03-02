<?php

/**
 * smarty_function_get_type_value
 *
 * Function Smarty - get_type_value
 * get type of a variable:
 * - string;
 * - float;
 * - int;
 * - bool;
 *
 * @uses       
 * @package    Module-Default
 * @subpackage Views.Plugins
 * @author   Sergii Beskorovainyi <bsa2657@yandex.ru>
 * @license  MIT <http://www.opensource.org/licenses/mit-license.php>
 * @link     https://github.com/bsa-git/zf-myblog/
 */

/**
 * Get type of a variable
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