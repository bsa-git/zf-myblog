<?php

/**
 * smarty_function_info_php
 *
 * Функция Smarty - info_php
 * получить информацию о PHP
 *
 * @uses       
 * @package    Module-Default
 * @subpackage Views.Plugins
 */

/**
 * Получить информацию о PHP
 * 
 * @param Smarty $smarty
 * @return string 
 */
function smarty_function_info_php($smarty) {
    return Default_Plugin_SysBox::getPHPInfo();
}

?>