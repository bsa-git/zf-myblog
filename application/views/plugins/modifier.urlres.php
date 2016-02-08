<?php

/**
 * smarty_modifier_urlres
 *
 * Модификатор Smarty - url
 * получить URL для ресурса
 *
 * @uses       
 * @package    Module-Default
 * @subpackage Views.Plugins
 */

/**
 * Получить URL для ресурса
 * 
 * @param string $text
 * @return string 
 */
function smarty_modifier_urlres($url_res, $type = 'normalize') {
    $responseTime = 0;
    $result = '';
    //-----------------
    if ($type == 'normalize') {
        $url = Default_Plugin_SysBox::getUrlRes($url_res);
        $result = $url;
    }elseif ($type == 'check') {
        $urlTool = new Default_Plugin_UrlTool();
        $result = $urlTool->checkUrl($url_res, $responseTime);
    }else{
        $result = $url_res;
    }
    return $result;
}

?>