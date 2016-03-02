<?php

/**
 * smarty_modifier_url
 *
 * Modifier Smarty - url
 * get the URL for the template
 *
 * @uses       
 * @package    Module-Default
 * @subpackage Views.Plugins
 * @author   Sergii Beskorovainyi <bsa2657@yandex.ru>
 * @license  MIT <http://www.opensource.org/licenses/mit-license.php>
 * @link     https://github.com/bsa-git/zf-myblog/
 */

/**
 * Get the URL for the template
 * 
 * @param string $text
 * @return string 
 */
function smarty_modifier_url($text) {
    $count = 2;
    //------------------
    
    // Получим массив значений MVC URL
    $text = trim($text, '/');
    $params = explode('/', $text);
    if (is_array($params)) {
        $count = count($params);
        if ($count == 1) {
            if ($params[0] == '') {
                $params[0] = NULL;
            }
            $params[] = NULL;
            $count = 2;
        }
    } else {
        $params = array(NULL, NULL);
    }

    // Установим конкретные значения $module/$controller/$action
    if ($count == 3) {
        $module = isset($params[0]) ? $params[0] : null;
        $controller = isset($params[1]) ? $params[1] : null;
        $action = isset($params[2]) ? $params[2] : null;
    } else {
        $module = NULL;
        $controller = isset($params[0]) ? $params[0] : null;
        $action = isset($params[1]) ? $params[1] : null;
    }
    
    // Получим помощника URL
    $helper = Zend_Controller_Action_HelperBroker::getStaticHelper('url');

    $url = $helper->simple($action, $controller, $module);
    $url = rtrim($url, '/');

    return $url;
}