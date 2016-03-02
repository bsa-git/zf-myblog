<?php
/**
 * smarty_modifier_split
 *
 * Modifier Smarty - split
 * converts a string to an array
 *
 * @uses       
 * @package    Module-Default
 * @subpackage Views.Plugins
 */

/**
 * converts a string to an array
 * 
 * @param string $source
 * @param string $delimiter
 * @param int $limit
 * 
 * @return array
 */
function smarty_modifier_split($source, $delimiter=' ', $limit=NULL) {
    $arrSource;
    //----------------- 
    if($limit){
        $arrSource = explode($delimiter, $source, $limit); 
    }else{
        $arrSource = explode($delimiter, $source); 
    }
    return $arrSource;
}