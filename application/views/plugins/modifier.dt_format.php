<?php
/**
 * Smarty dt_format modifier plugin
 *
 * Type:     modifier<br>
 * Name:     dt_format<br>
 * Purpose:  date-time format using Zend_Date<br>
 * 
 * @param string $date
 * @param string $format
 * @param string $input_format
 * @return string|void
 * 
 * @package    Module-Default
 * @subpackage Views.Plugins
 * @author   Sergii Beskorovainyi <bsa2657@yandex.ru>
 * @license  MIT <http://www.opensource.org/licenses/mit-license.php>
 * @link     https://github.com/bsa-git/zf-myblog/
 */
function smarty_modifier_dt_format($date = null, $format = Zend_Date::DATE_MEDIUM, $input_format = Zend_Date::ISO_8601)
{
    $date = new Zend_Date($date, $input_format);
    $dtFormat = $date->get($format);
    return $dtFormat;
}