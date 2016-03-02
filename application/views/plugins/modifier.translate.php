<?php
/**
 * smarty_modifier_translate
 *
 * Modifier Smarty - translate
 * translates the text into another language
 *
 * @uses       
 * @package    Module-Default
 * @subpackage Views.Plugins
 * @author   Sergii Beskorovainyi <bsa2657@yandex.ru>
 * @license  MIT <http://www.opensource.org/licenses/mit-license.php>
 * @link     https://github.com/bsa-git/zf-myblog/
 */

/**
 * Translates the text into another language
 * 
 * @param string $text
 * @param any $param1
 * @param any $param2
 * @param any $param3
 * @return string 
 */
function smarty_modifier_translate($text, $param1=NULL, $param2=NULL, $param3=NULL) {
     $text = Zend_Registry::get('Zend_Translate')->_($text);
     return sprintf($text, $param1, $param2, $param3);
}