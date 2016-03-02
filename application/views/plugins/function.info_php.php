<?php

/**
 * smarty_function_info_php
 *
 * Function Smarty - info_php
 * get information about PHP
 *
 * @uses       
 * @package    Module-Default
 * @subpackage Views.Plugins
 * @author   Sergii Beskorovainyi <bsa2657@yandex.ru>
 * @license  MIT <http://www.opensource.org/licenses/mit-license.php>
 * @link     https://github.com/bsa-git/zf-myblog/
 */

/**
 * Get information about PHP
 * 
 * @param Smarty $smarty
 * @return string 
 */
function smarty_function_info_php($smarty) {
    return Default_Plugin_SysBox::getPHPInfo();
}