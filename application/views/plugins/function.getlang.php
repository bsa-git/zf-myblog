<?php

/**
 * smarty_function_getlang
 *
 * Function Smarty - geturl
 * get locale
 *
 * @uses       
 * @package    Module-Default
 * @subpackage Views.Plugins
 * @author   Sergii Beskorovainyi <bsa2657@yandex.ru>
 * @license  MIT <http://www.opensource.org/licenses/mit-license.php>
 * @link     https://github.com/bsa-git/zf-myblog/
 */

/**
 * 
 * Get locale
 * 
 * @param Smarty $smarty
 * @return string 
 */
function smarty_function_getlang($smarty) {
    return Default_Plugin_SysBox::getTranslateLocale();
}