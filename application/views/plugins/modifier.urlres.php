<?php

/**
 * smarty_modifier_urlres
 *
 * Modifier Smarty - url
 * get the URL for the resource
 *
 * @uses       
 * @package    Module-Default
 * @subpackage Views.Plugins
 * @author   Sergii Beskorovainyi <bsa2657@yandex.ru>
 * @license  MIT <http://www.opensource.org/licenses/mit-license.php>
 * @link     https://github.com/bsa-git/zf-myblog/
 */

/**
 * Get the URL for the resource
 * 
 * @param string $url_res
 * @param string $type
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