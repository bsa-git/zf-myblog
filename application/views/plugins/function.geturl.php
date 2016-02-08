<?php

/**
 * smarty_function_geturl
 *
 * Функция Smarty - geturl
 * получить URL для шаблона
 *
 * @uses       
 * @package    Module-Default
 * @subpackage Views.Plugins
 */

/**
 * Получить URL для шаблона
 * 
 * @param array $params
 * @param Smarty $smarty
 * @return string 
 */
function smarty_function_geturl($params, $smarty) {
    $action = isset($params['action']) ? $params['action'] : null;
    $controller = isset($params['controller']) ? $params['controller'] : null;
    $route = isset($params['route']) ? $params['route'] : null;

    $helper = Zend_Controller_Action_HelperBroker::getStaticHelper('url');
    if (strlen($route) > 0) {
        unset($params['route']);
        $url = $helper->url($params, $route);
    } else {
        $url = $helper->simple($action, $controller);
        $url = rtrim($url, '/');
    }


    return $url;
}

?>