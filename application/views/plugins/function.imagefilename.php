<?php
/**
 * smarty_function_imagefilename
 *
 * Функция Smarty - imagefilename
 * получить файл изображения
 *
 * @uses
 * @package    Module-Default
 * @subpackage Views.Plugins
 */

/**
 * Получить файл изображения
 *
 * @param array $params
 * @param Smarty $smarty
 * @return string
 */
function smarty_function_imagefilename($params, $smarty) {
    if (!isset($params['id']))
        $params['id'] = 0;

    if (!isset($params['w']))
        $params['w'] = 0;

    if (!isset($params['w']))
        $params['h'] = 0;

    require_once $smarty->_get_plugin_filepath('function', 'geturl');

    $hash = Default_Model_DbTable_BlogPostImage::GetImageHash(
                    $params['id'],
                    $params['w'],
                    $params['h']
    );

    $options = array(
        'controller' => 'utility',
        'action' => 'image'
    );
    

    return sprintf(
            '%s?username=%s&id=%d&w=%d&h=%d&hash=%s',
            smarty_function_geturl($options, $smarty),
            $params['username'],
            $params['id'],
            $params['w'],
            $params['h'],
            $hash
    );
}

?>