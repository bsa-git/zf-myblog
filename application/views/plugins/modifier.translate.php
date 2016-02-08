<?php
/**
 * smarty_modifier_translate
 *
 * Модификатор Smarty - translate
 * переводит текст на другой язык
 *
 * @uses       
 * @package    Module-Default
 * @subpackage Views.Plugins
 */

/**
 * Переводит текст на другой язык
 * 
 * @param string $text
 * @param Smarty $smarty
 * @return string 
 */
function smarty_modifier_translate($text, $param1=NULL, $param2=NULL, $param3=NULL) {
     $text = Zend_Registry::get('Zend_Translate')->_($text);
     return sprintf($text, $param1, $param2, $param3);
}

?>