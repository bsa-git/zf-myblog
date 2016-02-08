<?php
/**
 * Admin_IndexController
 *
 * Контроллер - Index
 *
 * @uses       Default_Plugin_BaseController
 * @package    Module-Admin (Администрирование сайта)
 * @subpackage Controllers
 */
class Admin_IndexController extends Default_Plugin_BaseController {

     /**
     * Инициализация контроллера
     *
     */
    public function init() {
        parent::init();
    }
    
    /**
     * Действие - index
     * отобразим все операции модуля администрирования
     *
     */
    public function indexAction() {
        
    }

    /**
     * Действие - out
     * выход из модуля администрирования
     *
     */
    public function outAction() {
        $this->_redirect('/index');
    }

}

