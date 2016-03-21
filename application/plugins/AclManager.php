<?php

/**
 * Default_Controller_Plugins_AclManager
 * 
 * Plugin - access to resources check
 *
 *
 * @uses       Zend_Controller_Plugin_Abstract
 * @package    Module-Default
 * @subpackage Controllers.Plugins
 * @author   Sergii Beskorovainyi <bsa2657@yandex.ru>
 * @license  MIT <http://www.opensource.org/licenses/mit-license.php>
 * @link     https://github.com/bsa-git/zf-myblog/
 */
class Default_Plugin_AclManager extends Zend_Controller_Plugin_Abstract {

    /**
     * Start time
     *
     * @var float
     */
    protected $_startTimeProfiler = 0;

    /**
     * List of roles
     *
     * @var array
     */
    static $roles = array(
        'admin' => 'administrator',
        'editor' => 'editor',
        'member' => 'member',
        'commentator' => 'commentator',
        'guest' => 'guest'
    );

    /**
     * Default user role
     *
     * @var string
     */
    private $_defaultRole = 'guest';

    /**
     * Default Module
     *
     * @var string
     */
    private $_defaultModule = 'default';

    /**
     * Action 'login' controller 'account', 
     * if user does not have appropriate privileges on this resource
     *
     * @var array
     */
    private $_authController = array(
        'module' => 'default',
        'controller' => 'account',
        'action' => 'login');
    /**
     * Action 'message' controller 'error'
     *
     * @var array
     */
    private $_errorController = array(
        'module' => 'default',
        'controller' => 'error',
        'action' => 'message');

    /**
     * Constructor
     * Here we assign roles, resources and privileges
     * 
     * @param Zend_Auth $auth  Обьект идентификации
     */
    public function __construct(Zend_Auth $auth) {
        
        $config = Zend_Registry::get('config');
        $strModules = trim($config['user']['modules']['enable']);
        $strModules = str_replace(' ', '', $strModules);
        $strModules = strtolower($strModules);
        $modules = explode(';', $strModules);
        
        //Создадим обьект авторизации
        $this->auth = $auth;
        $this->acl = new Zend_Acl();

        // Добавим пользовательские роли
        $this->acl->addRole(new Zend_Acl_Role(self::$roles['guest'])); //$this->_defaultRole
        // роль 'commentator' наследует права от роли 'guest'
        $this->acl->addRole(new Zend_Acl_Role(self::$roles['commentator']), self::$roles['guest']);
        // роль 'member' наследует права от роли 'commentator'
        $this->acl->addRole(new Zend_Acl_Role(self::$roles['member']), self::$roles['commentator']);
        // роль 'editor' наследует права от роли 'member'
        $this->acl->addRole(new Zend_Acl_Role(self::$roles['editor']), self::$roles['member']);
        // роль 'administrator' наследует права от роли 'editor'
        $this->acl->addRole(new Zend_Acl_Role(self::$roles['admin']), self::$roles['editor']);

        //--- Добавим ресурсы ---
        // Модуль по умолчанию - default
        $this->acl->add(new Zend_Acl_Resource('default.index'));
        $this->acl->add(new Zend_Acl_Resource('default.error'));
        $this->acl->add(new Zend_Acl_Resource('default.account'));
        $this->acl->add(new Zend_Acl_Resource('default.blogmanager'));
        $this->acl->add(new Zend_Acl_Resource('default.user'));
        $this->acl->add(new Zend_Acl_Resource('default.search'));

        // Модуль администрирования сайтом - Admin
        $this->acl->add(new Zend_Acl_Resource('admin.index'));
        $this->acl->add(new Zend_Acl_Resource('admin.user'));
        $this->acl->add(new Zend_Acl_Resource('admin.blog'));
        $this->acl->add(new Zend_Acl_Resource('admin.info'));
        $this->acl->add(new Zend_Acl_Resource('admin.config'));
        $this->acl->add(new Zend_Acl_Resource('admin.tools'));
        $this->acl->add(new Zend_Acl_Resource('admin.performance'));

        // Модуль управления персоналом - HR
        $this->acl->add(new Zend_Acl_Resource('hr.index'));
        
        // Разрешим доступ для всех пользователей ко всем ресурсам
        // кроме управления учетными записями пользователей и администрирования 
        $this->acl->allow();
        $this->acl->deny(null, 'default.account');
        $this->acl->deny(null, 'default.blogmanager');
        $this->acl->deny(null, 'default.search');
        $this->acl->deny(null, 'admin.index');
        $this->acl->deny(null, 'admin.user');
        $this->acl->deny(null, 'admin.blog');
        $this->acl->deny(null, 'admin.info');
        $this->acl->deny(null, 'admin.config');
        $this->acl->deny(null, 'admin.tools');
        $this->acl->deny(null, 'admin.performance');
        $this->acl->deny(null, 'hr.index');


        // Добавим некоторые привилегии для гостя 
        // для ресурса - 'account'
        $this->acl->allow(self::$roles['guest'], 'default.account', array(
            'login',
            'fetchpassword',
            'register',
            'registercomplete',
        ));

        // Добавим некоторые привилегии для гостя
        // для ресурса - 'search'
        $this->acl->allow(self::$roles['guest'], 'default.search', array(
            'index',
            'suggestion',
        ));

        // Добавим некоторые привилегии для гостя
        // для ресурса - 'blogmanager'
        $this->acl->allow(self::$roles['guest'], 'default.blogmanager', array(
            'getlocations',
            'details',
        ));

        // Добавим некоторые привилегии для гостя
        // для ресурса - 'admin.info'
        $this->acl->allow(self::$roles['guest'], 'admin.info', array(
            'view',
            'hint'
        ));

        // Позволим commentator доступ к регистрации
        $this->acl->allow(self::$roles['commentator'], 'default.account');
        
        // Позволим members доступ к регистрации и созданию своих блогов
//        $this->acl->allow(self::$roles['member'], 'default.account');
        $this->acl->allow(self::$roles['member'], 'default.blogmanager');


        // Позволим редакторам редактировать чужие блоги
        $this->acl->allow(self::$roles['editor'], 'admin.index');
        $this->acl->allow(self::$roles['editor'], 'admin.blog');
        // Позволим редакторам работать с котроллерами модуля упр. персоналом
        if(in_array('hr', $modules)){
            $this->acl->allow(self::$roles['editor'], 'hr.index');
        }

        // Позволим редакторам выполнять действие 'login' для 'admin.user'
        $this->acl->allow(self::$roles['editor'], 'admin.user', array(
            'login',
        ));

        // Позволим администратору управлять сайтом через ресурс администрирования
        $this->acl->allow(self::$roles['admin'], 'default.search');
        $this->acl->allow(self::$roles['admin'], 'admin.user');
        $this->acl->allow(self::$roles['admin'], 'admin.info');
        $this->acl->allow(self::$roles['admin'], 'admin.config');
        $this->acl->allow(self::$roles['admin'], 'admin.tools');
        $this->acl->allow(self::$roles['admin'], 'admin.performance');
    }

    /**
     * Initialization Smarty templating
     *
     * @param string $module
     */
    private function iniViewSmarty($module) {
        // Установим шаблоны для модуля
        $vr = new Zend_Controller_Action_Helper_ViewRenderer();
        $vr->setView(new Default_Plugin_ViewSmarty($module));
        $vr->setViewSuffix('tpl');
        Zend_Controller_Action_HelperBroker::addHelper($vr);
    }

    /**
     * Event is called before Zend_Controller_Front calls on the router
     *
     * @param Zend_Controller_Request_Abstract $request
     */
    public function routeStartup(Zend_Controller_Request_Abstract $request) {
        $this->_startTimeProfiler = microtime(1);
        
        $isAjaxRequest = $request->isXmlHttpRequest();
        // Если это AJAX запрос запустим буфер выдачи дынных клиенту
        // это нужно для того, что при AJAX запросе устаналивается заголовок ответа
        // поэтому, чтобы не произошла ошибка запускается буфер
        if ($isAjaxRequest) {
            ob_start();
        }
    }

    /**
     * Event before accessing the controller will check the availability to the user's resources
     *
     * @param Zend_Controller_Request_Abstract $request
     */
    public function preDispatch(Zend_Controller_Request_Abstract $request) {

        $startTime = microtime(1);

        //Определим роль пользователя
        // Если это зарегистрированный пользователь, назначим его роль.
        // Если нет, то назначим роль по умолчанию
        if ($this->auth->hasIdentity()) {
            $identity = $this->auth->getIdentity();
            $role = $identity->user_type;
        } else {
            $role = $this->_defaultRole;
        }

        //Проверим правильность назначенной роли
        if (!$this->acl->hasRole($role))
            $role = $this->_defaultRole;

        // Получим название ресурса (имя контроллера)

        if ($request->getModuleName()) {
            $module = $request->getModuleName();
        } else {
            $module = $this->_defaultModule;
        }

        $resource = $module . '.' . $request->getControllerName();

        // Получим название привилегии (имя действия контроллера)
        $privilege = $request->getActionName();

        // Если полученный ресурс не назначен в "ACL",
        // то установим его в NULL
        if (!$this->acl->has($resource)) {
            $resource = null;
        }

        // Проверим доспупность ресурса, запрашиваемого пользователем
        // если, доступ запрещен, то перенаправим запрос на 
        // соответствующий обработчик ошибки доспупа к ресурсу
        if (!$this->acl->isAllowed($role, $resource, $privilege)) {
            $arrMessage = array();
            if ($role == $this->_defaultRole) {
                $request->setModuleName($this->_authController['module']);
                $request->setControllerName($this->_authController['controller']);
                $request->setActionName($this->_authController['action']);
                $arrMessage[] = '<em>' . Zend_Registry::get('Zend_Translate')->_('Ошибка авторизации') . '!</em>';
                $arrMessage[] = Zend_Registry::get('Zend_Translate')->_('У вас недостаточно прав на этот ресурс') . '. ' .
                        Zend_Registry::get('Zend_Translate')->_('Пожалуйста авторизируйтесь') . '!';
                $request->setParam('message', $arrMessage);
                $request->setParam('class_message', 'warning');
                $this->iniViewSmarty('default');
            } else {
                $request->setModuleName($this->_errorController['module']);
                $request->setControllerName($this->_errorController['controller']);
                $request->setActionName($this->_errorController['action']);
                $arrMessage[] = '<em>' . Zend_Registry::get('Zend_Translate')->_('Ошибка доступа к ресурсу') . '!</em>';
                $arrMessage[] = Zend_Registry::get('Zend_Translate')->_('У вас недостаточно прав на этот ресурс') . '. ';
                $request->setParam('message', $arrMessage);
                $request->setParam('class_message', 'warning');
                $this->iniViewSmarty('default');
            }
        } else {
            $this->iniViewSmarty($module);
        }

        //-------- Установим время выполнения проверки доступа к ресурсу --------
        $infoProfiler = "";
        $infoProfiler .= "Время выполнения проверки доступа к ресурсу: ";
        Default_Plugin_SysBox::profilerTime2Registry($startTime, $infoProfiler);
    }

    /**
     * Called after Zend_Controller_Router exits.
     *
     * Called after Zend_Controller_Front exits from the router.
     *
     * @param  Zend_Controller_Request_Abstract $request
     * @return void
     */
    public function routeShutdown(Zend_Controller_Request_Abstract $request) {
        
        // Запишем в лог
        Default_Model_Log::toLog($request);
        
        // Использование кеша
        $result = Default_Plugin_SysBox::startZendCache_Page();
        if ($result) {// Попали в кеш
            // Сформируем суммарный отчет времени выполнения запроса
            Default_Plugin_SysBox::profilerTotalReport2Html($this->_startTimeProfiler, 'db');
            die();// выход из скрипта
        }
    }

    /**
     * Events after the appeal to the controller
     *
     * @param Zend_Controller_Request_Abstract $request
     */
    public function postDispatch(Zend_Controller_Request_Abstract $request) {
        
    }

    /**
     * The event is called after Zend_Controller_Front of its dispatch loop.
     *
     * @param Zend_Controller_Request_Abstract $request
     */
    public function dispatchLoopShutdown() {

        // Сформируем суммарный отчет времени выполнения запроса
        Default_Plugin_SysBox::profilerTotalReport2Html($this->_startTimeProfiler, 'db');
    }

}