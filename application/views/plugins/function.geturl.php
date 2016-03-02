<?php

/**
 * smarty_function_geturl
 *
 * Function Smarty - geturl
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