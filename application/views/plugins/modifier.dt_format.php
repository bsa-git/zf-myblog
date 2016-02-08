<?php
/**
 * Smarty dt_format modifier plugin
 *
 * Type:     modifier<br>
 * Name:     dt_format<br>
 * Purpose:  форматирует дату-время с помощью Zend_Date<br>
 * Input:<br>
 *         - string: входные дата/время 
 *         - format: тип форматирования
 *         - $input_format: формат входных дата/время
 * @author   Бескоровайный Сергей
 * @param string $date
 * @param string $format
 * @param string $input_format
 * @return string|void
 * 
 * @package    Module-Default
 * @subpackage Views.Plugins
 */
function smarty_modifier_dt_format($date = null, $format = Zend_Date::DATE_MEDIUM, $input_format = Zend_Date::ISO_8601)
{
    $date = new Zend_Date($date, $input_format);
    $dtFormat = $date->get($format);
    return $dtFormat;
}

/* vim: set expandtab: */

?>
