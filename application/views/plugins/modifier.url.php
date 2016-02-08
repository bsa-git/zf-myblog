<?php

/**
 * smarty_modifier_url
 *
 * Модификатор Smarty - url
 * получить URL для шаблона
 *
 * @uses       
 * @package    Module-Default
 * @subpackage Views.Plugins
 */

/**
 * Получить URL для шаблона
 * 
 * @param string $text
 * @param Smarty $smarty
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

?>