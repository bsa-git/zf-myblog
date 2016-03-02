<?php
/**
 * Admin_ConfigController
 *
 * Controller - Config
 * configuring site
 *
 * @uses       Default_Plugin_BaseController
 * @package    Module-Admin (administration of site)
 * @subpackage Controllers
 * @author   Sergii Beskorovainyi <bsa2657@yandex.ru>
 * @license  MIT <http://www.opensource.org/licenses/mit-license.php>
 * @link     https://github.com/bsa-git/zf-myblog/
 */
class Admin_ConfigController extends Default_Plugin_BaseController {

     /**
     * Initialization controller
     *
     */
    public function init() {
        parent::init();
        $this->_breadcrumbs->addStep($this->Translate('Конфигурирование'), $this->getUrl(null, 'config', 'admin'));
    }
    
    /**
     * Action - index
     *
     * Access to the action is possible in the following paths:
     * - /admin/config/index
     * - /admin/config
     *
     * @return void
     */
    public function indexAction() {
    }

    /**
     * Action - langs
     * configuration interface languages
     *
     * Access to the action is possible in the following paths:
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
     * Action - modules
     * module configuration
     *
     * Access to the action is possible in the following paths:
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
     * Action - interface
     * user interface customization
     *
     * Access to the action is possible in the following paths:
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

