<?php

/**
 * smarty_modifier_urlquery
 *
 * Modifier Smarty - urlquery
 * add key=value to query URL
 *
 * @uses       
 * @package    Module-Default
 * @subpackage Views.Plugins
 * @author   Sergii Beskorovainyi <bsa2657@yandex.ru>
 * @license  MIT <http://www.opensource.org/licenses/mit-license.php>
 * @link     https://github.com/bsa-git/zf-myblog/
 */

/**
 * Add key=value to query URL
 * 
 * @param string $key
 * @param string $value
 * @return string 
 */
function smarty_modifier_urlquery($url, $key, $value) {
    $result = '';
    $arr_query = array();
    //-----------------
    $arrURL = parse_url($url);
    $querystring = $arrURL['query'];
    if ($querystring) {
        parse_str($querystring, $arr_query);
        $arr_query[$key] = $value;
        $querystring = http_build_query($arr_query);
        $querystring = html_entity_decode($querystring);
        $arrURL['query'] = $querystring;
        $url = $arrURL['path'] . '?' . $arrURL['query'];
    } else {
        $url .= "?{$key}={$value}";
    }
    return $url;
}