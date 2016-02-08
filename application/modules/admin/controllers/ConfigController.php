<?php
/**
 * Admin_ConfigController
 *
 * Контроллер - Config
 * Конфигурирование сайта
 *
 * @uses       Default_Plugin_BaseController
 * @package    Module-Admin (Администрирование сайта)
 * @subpackage Controllers
 */
class Admin_ConfigController extends Default_Plugin_BaseController {

     /**
     * Инициализация контроллера
     *
     */
    public function init() {
        parent::init();
        $this->_breadcrumbs->addStep($this->Translate('Конфигурирование'), $this->getUrl(null, 'config', 'admin'));
    }
    
    /**
     * Действие по умолчанию
     *
     * Доступ к действию возможем по следующим путям urls:
     * - /admin/config/index
     * - /admin/config
     *
     * @return void
     */
    public function indexAction() {
    }

    /**
     * Действие - langs
     * конфигурирование языков интерфейса
     *
     * Доступ к действию возможем по следующим путям urls:
     * - /admin/config/langs
     *
     * @return void
     */
    public function langsAction() {
        $this->_breadcrumbs->addStep($this->Translate('Языки интерфейса'));
        $this->view->message = $this->Translate('Раздел сайта находится в разработке').'!';
        $this->view->class_message = 'caution';
    }
    
    /**
     * Действие - modules
     * настройка модулей
     *
     * Доступ к действию возможем по следующим путям urls:
     * - /admin/config/modules
     *
     * @return void
     */
    public function modulesAction() {
        $this->_breadcrumbs->addStep($this->Translate('Настройка модулей'));
        $this->view->message = $this->Translate('Раздел сайта находится в разработке').'!';
        $this->view->class_message = 'caution';
    }
    
    /**
     * Действие - interface
     * настройка интерфейса пользователя
     *
     * Доступ к действию возможем по следующим путям urls:
     * - /admin/config/interface
     *
     * @return void
     */
    public function interfaceAction() {
        $this->_breadcrumbs->addStep($this->Translate('Настройка интерфейса пользователя'));
        $this->view->message = $this->Translate('Раздел сайта находится в разработке').'!';
        $this->view->class_message = 'caution';
    }
}

