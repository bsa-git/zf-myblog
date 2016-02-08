<?php
/**
 * Smarty print modifier plugin
 *
 * Type:     modifier<br>
 * Name:     print<br>
 * Purpose:  Печатает данные (массив, обьект, xml) для отладки
 * Input:<br>
 *         - string: входные дата/время 
 *         - format: тип форматирования
 *         - $input_format: формат входных дата/время
 * @author   Бескоровайный Сергей
 * @param array|object|xml  $data    //Данные для печати
 * @param string  $forceType         //Тип переменной: array, object, xml
 * @param bool  $bCollapsed          //Признак раскрытия/закрытия узлов значений переменных
 * @param bool  $isAjax              //Признак Ajax запроса, при этом запускается буфер выходных данных
 * @return string|void
 * 
 * @package    Module-Default
 * @subpackage Views.Plugins
 */
function smarty_modifier_print($data = null, $type = "array", $bCollapsed = false, $isAjax=false)
{
    if($isAjax){
        ob_start();
    }
    new Default_Plugin_DBug($data, $type, $bCollapsed);
}
?>
