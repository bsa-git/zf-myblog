<?php
/**
 * smarty_modifier_split
 *
 * Модификатор Smarty - split
 * преобразует строку в массив
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
function smarty_modifier_split($source, $pattern=' ', $limit=NULL) {
    $arrSource;
    //----------------- 
    if($limit){
        $arrSource = explode($pattern, $source, $limit); 
    }else{
        $arrSource = explode($pattern, $source); 
    }
    return $arrSource;
}

?>