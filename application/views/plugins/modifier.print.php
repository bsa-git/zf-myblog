<?php

/**
 * Smarty print modifier plugin
 *
 * Type:     modifier<br>
 * Name:     print<br>
 * Purpose:  Print value (array, object, xml) for debugging
 * 
 * @param array|object|xml  $data    // Value for print
 * @param string  $forceType         // Value type: array, object, xml
 * @param bool  $bCollapsed          // Tag opening/closing values of the variables nodes
 * @param bool  $isAjax              // Tag Ajax request, and the output data buffer starts
 * @return string|void
 * 
 * @package    Module-Default
 * @subpackage Views.Plugins
 * @author   Sergii Beskorovainyi <bsa2657@yandex.ru>
 * @license  MIT <http://www.opensource.org/licenses/mit-license.php>
 * @link     https://github.com/bsa-git/zf-myblog/
 */
function smarty_modifier_print($data = null, $type = "array", $bCollapsed = false, $isAjax = false) {
    if ($isAjax) {
        ob_start();
    }
    new Default_Plugin_DBug($data, $type, $bCollapsed);
}
