<?php

/**
 * Default_Controller_Plugin_Manager
 * 
 * Plugin для контроллера действий по умолчанию
 * Управление ресурсами модуля
 *
 *
 * @uses       Zend_Controller_Plugin_Abstract
 * @package    Module-Default
 * @subpackage Controllers.Plugins
 */
class Default_Plugin_ModuleManager extends Zend_Controller_Plugin_Abstract {

    

    /**
     * Конструктор класса
     * сдесь мы назначаем роли, ресурсы и привилегии
     * 
     * @param Zend_Auth $auth  Обьект идентификации
     */
    public function __construct() {
    }

    /**
     * preDispatch
     * Событие перед обращением к контроллеру
     * проверим доступность к ресурсам пользователя
     *
     * @param Zend_Controller_Request_Abstract $request
     */
    public function preDispatch(Zend_Controller_Request_Abstract $request) {

        // Инициализация ресурсов если модуль - default
        $module = $request->getModuleName();
        if ('default' != $module) {
            // If not in this module, return early
            return;
        }
    }

    /**
     * postDispatch
     * Событие после обращением к контроллеру
     *
     * @param Zend_Controller_Request_Abstract $request
     */
    public function postDispatch(Zend_Controller_Request_Abstract $request) {
        
    }

}