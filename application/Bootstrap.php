<?php

/**
 * Bootstrap
 * 
 * Файл начальной загрузки и инициализации приложения
 *
 * @uses    Zend_Application_Bootstrap_Bootstrap
 * @package Bootstrap
 */
class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {

    /**
     * Инициализация Autoloader, Session, Auth, Locale,
     *
     * @return void
     */
    protected function _initAutoload() {

        $_startTime = microtime(1);

        //------- Определим параметры автозагрузки ------------
        //Определим базовый префикс и базовый путь к ресурсам для модуля по умолчанию
        $autoloader = new Zend_Application_Module_Autoloader(
                array('namespace' => 'Default', 'basePath' => dirname(__FILE__)));
        
        // Add resource loader for admin module
        $resourceLoader = new Zend_Loader_Autoloader_Resource(array(
            'basePath' => APPLICATION_PATH . '/modules/admin',
            'namespace' => 'Admin',
            'resourceTypes' => array(
                'form' => array(
                    'path' => 'forms/',
                    'namespace' => 'Form',
                )
            ),
        ));

        //------- Создадим директории приложения ------------
        Default_Plugin_SysBox::createAppPaths();
        
        //--------- Запомним конфигурацию в регистре ---------
        $config = $this->_options;
        Zend_Registry::set('config', $config);

        //----------------- Установим сессию ---------------
        //Запустим сессию
        Zend_Session::start();
        //Установим опцию для того чтобы предотвратить повторное выполнение
        //Zend_Session::start() при вызове метода (new Zend_Session_Namespace)
        Zend_Session::setOptions(array('strict' => true));

        //Получим экземпляры объектов для соответствующих пространств имен
        $Zend_Auth = new Zend_Session_Namespace('Zend_Auth');

        //Запомним экземпляры объектов для соответствующих пространств имен
        Zend_Registry::set("Zend_Auth", $Zend_Auth);

        //Добавим новый тип ресурса для плагинов контроллеров
        //$autoloader->addResourceType('cplugins', 'controllers/plugins', 'Controller_Plugins');
        //---- Настройка аутентификации пользователя -----
        $auth = Zend_Auth::getInstance();
        $auth->setStorage(new Zend_Auth_Storage_Session());

        //------ Регистрация плагинов ---------
        // Запустим 'FrontController'
        $this->bootstrap('FrontController');

        $front = Zend_Controller_Front::getInstance();

        //Плагин проверка доступа пользователя к ресурсам
        $front->registerPlugin(
                new Default_Plugin_AclManager($auth));

        //------------ Сконфигурируем язык перевода -------------
        //$basePathApplication = dirname(__FILE__);
        $translate = new Zend_Translate('array', APPLICATION_PATH . '/languages/ru/My_Messages.php', 'ru');
        $translate->addTranslation(
                APPLICATION_PATH . '/languages/ru/Zend_Validate.php', 'ru');
        $translate->addTranslation(
                APPLICATION_PATH . '/languages/ru/My_Validate.php', 'ru');
        $translate->addTranslation(
                APPLICATION_PATH . '/languages/uk/My_Messages.php', 'uk');
        $translate->addTranslation(
                APPLICATION_PATH . '/languages/uk/Zend_Validate.php', 'uk');
        $translate->addTranslation(
                APPLICATION_PATH . '/languages/uk/My_Validate.php', 'uk');
        $translate->addTranslation(
                APPLICATION_PATH . '/languages/en/My_Messages.php', 'en');
        $translate->addTranslation(
                APPLICATION_PATH . '/languages/en/Zend_Validate.php', 'en');
        $translate->addTranslation(
                APPLICATION_PATH . '/languages/en/My_Validate.php', 'en');

        //------------ Сконфигурируем язык перевода для модулей -------------
        $translate->addTranslation(
                APPLICATION_PATH . '/modules/hr/languages/ru/My_Messages.php', 'ru');
        $translate->addTranslation(
                APPLICATION_PATH . '/modules/hr/languages/uk/My_Messages.php', 'uk');
        $translate->addTranslation(
                APPLICATION_PATH . '/modules/hr/languages/en/My_Messages.php', 'en');

        //Установим язык перевода по умолчанию
        if (!isset($Zend_Auth->translate_locale)) {
            $Zend_Auth->translate_locale = $config['user']['locale'];
        } else {
            $locale = $Zend_Auth->translate_locale;
            $newLocal = Default_Plugin_SysBox::updateTranslateLocale($locale);
            if (!$newLocal === FALSE) {
                $Zend_Auth->translate_locale = $newLocal;
            }
        }
        $translate->setLocale($Zend_Auth->translate_locale);

        //Запомним переводчик в регистре
        Zend_Registry::set('Zend_Translate', $translate);

        //------------ Сконфигурируем локализацию сайта -------------
        //Получим строку типа локализации
        $paramLocal = Default_Plugin_SysBox::getLocalParam(
                        $Zend_Auth->translate_locale);
        // Установим локализацию сайта
        $locale = new Zend_Locale($paramLocal);
        // Настройки локали
        date_default_timezone_set($config['user']['timezone']);
        //Запомним локализацию в регистре
        Zend_Registry::set('Zend_Locale', $locale);

        //------------ Установим цветовую схему сайта -------------
        if (!isset($Zend_Auth->user_scheme)) {
            $Zend_Auth->user_scheme = $config['user']['scheme'];
        } else {
            $scheme = $Zend_Auth->user_scheme;
            $newScheme = Default_Plugin_SysBox::getUserScheme($scheme);
            if ($newScheme !== $scheme) {
                $Zend_Auth->user_scheme = $newScheme;
            }
        }

        //---- Определение времени выполнения скрипта ----
        $infoProfiler = Default_Plugin_SysBox::Translate("Время выполнения") . " Bootstrap_initAutoload(): ";
        Default_Plugin_SysBox::profilerTime2Registry($_startTime, $infoProfiler);

        //Сохраним
        return $autoloader;
    }

    /**
     * Инициализация маршрутов
     *
     * @return void
     */
    protected function _initRouter() {

        $_startTime = microtime(1);

        $front = Zend_Controller_Front::getInstance();

        // setup the route for user home pages
        $route = new Zend_Controller_Router_Route('user/:username/:action/*', array(
            'module' => 'default',
            'controller' => 'user',
            'action' => 'index'));

        $front->getRouter()->addRoute('user', $route);

        // set up the route for viewing blog posts
        $route = new Zend_Controller_Router_Route(
                'user/:username/view/:url/*', array(
            'module' => 'default',
            'controller' => 'user',
            'action' => 'view')
        );

        $front->getRouter()->addRoute('post', $route);

        // set up the route for viewing monthly archives
        $route = new Zend_Controller_Router_Route(
                'user/:username/archive/:year/:month/*', array(
            'module' => 'default',
            'controller' => 'user',
            'action' => 'archive')
        );

        $front->getRouter()->addRoute('archive', $route);

        // set up the route for tag blog posts
        $route = new Zend_Controller_Router_Route(
                'user/:username/tag/:tag/*', array(
            'module' => 'default',
            'controller' => 'user',
            'action' => 'tag')
        );

        $front->getRouter()->addRoute('tagspace', $route);

        // set up the route for feed tag blog posts
        $route = new Zend_Controller_Router_Route(
                'user/:username/feed/:tag/*', array(
            'module' => 'default',
            'controller' => 'user',
            'action' => 'feed')
        );

        $front->getRouter()->addRoute('feed_tag', $route);

        // set up the route for images blog post
        $route = new Zend_Controller_Router_Route(
                'user/:username/post/:post_id/images/*', array(
            'module' => 'default',
            'controller' => 'user',
            'action' => 'images')
        );

        $front->getRouter()->addRoute('get_images', $route);

        // set up the route for audios blog post
        $route = new Zend_Controller_Router_Route(
                'user/:username/post/:post_id/audios/*', array(
            'module' => 'default',
            'controller' => 'user',
            'action' => 'audios')
        );

        $front->getRouter()->addRoute('get_audios', $route);

        // set up the route for videos blog post
        $route = new Zend_Controller_Router_Route(
                'user/:username/post/:post_id/videos/*', array(
            'module' => 'default',
            'controller' => 'user',
            'action' => 'videos')
        );

        $front->getRouter()->addRoute('get_videos', $route);

        // set up the route for comments blog post
        $route = new Zend_Controller_Router_Route(
                'user/:username/post/:post_id/comments/*', array(
            'module' => 'default',
            'controller' => 'user',
            'action' => 'comments')
        );

        $front->getRouter()->addRoute('get_comments', $route);

        // set up the route for all users tag blog posts
        $route = new Zend_Controller_Router_Route(
                'user/all/tag/:tag/*', array(
            'module' => 'default',
            'controller' => 'index',
            'action' => 'tag')
        );

        $front->getRouter()->addRoute('tags_all', $route);

        // set up the route for  all users  tag blog posts
        $route = new Zend_Controller_Router_Route(
                'user/all/feed/:tag/*', array(
            'module' => 'default',
            'controller' => 'index',
            'action' => 'feed')
        );

        $front->getRouter()->addRoute('feed_tag_all', $route);

        //---- Определение времени выполнения скрипта ----
        $infoProfiler = Default_Plugin_SysBox::Translate("Время выполнения") . " Bootstrap_initRouter(): ";
        Default_Plugin_SysBox::profilerTime2Registry($_startTime, $infoProfiler);
    }

    /**
     * Инициализация View
     *
     * @return void
     */
    protected function _initViews() {
        //-------------

        $_startTime = microtime(1);

        $this->bootstrap('view');
        //!!!! Инициализация Smarty выполняется в классе - Default_Plugin_AclManager
        //---- Определение времени выполнения скрипта ----
        $infoProfiler = "Время выполнения" . " Bootstrap_initViews(): ";
        Default_Plugin_SysBox::profilerTime2Registry($_startTime, $infoProfiler);
    }

    /**
     * Инициализация Базы данных
     *
     * @return void
     */
    protected function _initDb() {

        $_startTime = microtime(1);

        $arrParam = array();
        //--------------------
        try {
            
            //------- Скопируем базу данных, если нужно ------------
            $dbname = $this->_options['resources']['db']['params']['dbname'];
            Default_Plugin_SysBox::copyDataBase($dbname);
            
            // Получим параметры для подключению базы данных
            $arrParam = $this->_options['resources']['db']['params'];
            
            // Подключение к БД
            $db = Zend_Db::factory(
                            "PDO_SQLITE", $arrParam);
            $db->getConnection();
            // Задание адаптера по умолчанию для наследников класса Zend_Db_Table_Abstract
            Zend_Db_Table_Abstract::setDefaultAdapter($db);
            // Занесение объекта соединения c БД в реестр
            Zend_Registry::set('db', $db);

            //---- Определение времени выполнения скрипта ----
            $infoProfiler = Default_Plugin_SysBox::Translate("Время выполнения") . " Bootstrap_initDb(): ";
            Default_Plugin_SysBox::profilerTime2Registry($_startTime, $infoProfiler);

        } catch (Zend_Db_Adapter_Exception $e) {
            // возможно, неправильные параметры соединения или СУРБД не запущена
            throw $e;
        } catch (Zend_Exception $e) {
            // возможно, попытка загрузки требуемого класса адаптера потерпела неудачу
            throw $e;
        }
    }

    /**
     * Инициализация Log, Search, KCFinder
     *
     * @return void
     */
    protected function _initLog() {
        $params = array();
        //------------------
        $_startTime = microtime(1);

        //Получим конфигурацию
        $config = $this->_options;

        //----- Создадим обьект Zend_Log -----

        $columnMapping = array(
            'ts' => 'timestamp',
            'msg' => 'message',
            'pr' => 'priority',
            'pr_name' => 'priorityName');

        $countMsg = $config['logging']['log']['max_rows'];
        $countEx = $config['logging']['exeption']['max_rows'];
        $countStat = $config['logging']['statistics']['max_rows'];

        // Получим базу данных
        $db = Zend_Registry::get('db');
        // Установим параметры
        $params['db'] = $db;
        $params['columnMap'] = $columnMapping;

        // Создадим writer для базы данных
        $params['table'] = 'log_msg';
        $params['max_rows'] = $countMsg;
        $writerMsg = new Default_Model_Log($params);
        $params['table'] = 'log_error';
        $params['max_rows'] = $countEx;
        $writerEx = new Default_Model_Log($params);
        $params['table'] = 'log_stat';
        $params['max_rows'] = $countStat;
        $writerStat = new Default_Model_Log($params);

        //Создадим логеры
        $logMsg = new Zend_Log($writerMsg);
        $logEx = new Zend_Log($writerEx);
        $logStat = new Zend_Log($writerStat);

        // Добавим новые приоритеты для лога - $logMsg
        $logMsg->addPriority('LOGIN_OK', 8);
        $logMsg->addPriority('LOGIN_ERR', 9);
        $logMsg->addPriority('LOGOUT', 10);
        $logMsg->addPriority('REG_OK', 11);
        $logMsg->addPriority('REG_ERR', 12);
        $logMsg->addPriority('DETAILS_OK', 13);
        $logMsg->addPriority('FETCHPASS_COMPLETE_OK', 14);
        $logMsg->addPriority('FETCHPASS_COMPLETE_ERR', 15);
        $logMsg->addPriority('FETCHPASS_CONFIRM_OK', 16);
        $logMsg->addPriority('FETCHPASS_CONFIRM_ERR', 17);
        $logMsg->addPriority('MAIL_OK', 18);
        $logMsg->addPriority('MAIL_ERR', 19);
        $logMsg->addPriority('DB_SAVE_ERR', 20);
        $logMsg->addPriority('DB_DELETE_ERR', 21);
        $logMsg->addPriority('POST_EDIT', 22);
        $logMsg->addPriority('POST_SET_STATUS', 23);
        $logMsg->addPriority('ADMIN_POST_EDIT', 24);
        $logMsg->addPriority('ADMIN_ROW_UPDATE', 25);
        $logMsg->addPriority('ADMIN_ROW_INSERT', 26);
        $logMsg->addPriority('ADMIN_ROW_DELETE', 27);

        // Добавим новые приоритеты для лога - $logStat
        $logStat->addPriority('LOGIN_OK', 8);
        $logStat->addPriority('LOGIN_ERR', 9);
        $logStat->addPriority('MAIL_OK', 10);
        $logStat->addPriority('FETCHPASS_COMPLETE_OK', 11);
        $logStat->addPriority('FETCHPASS_COMPLETE_ERR', 12);
        $logStat->addPriority('FETCHPASS_CONFIRM_OK', 13);
        $logStat->addPriority('FETCHPASS_CONFIRM_ERR', 14);
        $logStat->addPriority('POST_OPEN', 15);
        $logStat->addPriority('VIDEO_PLAY', 16);
        $logStat->addPriority('AUDIO_PLAY', 17);

        $emailParams = $config['logging']['email'];
        if ($emailParams['send']) {

            $mail = Default_Plugin_SysBox::createMail($emailParams);

            $writer = new Zend_Log_Writer_Mail($mail);

            $my_request = Default_Plugin_SysBox::getUrlRequest();
            if (!$emailParams['subject'])
                $writer->setSubjectPrependText('Errors request - ' . $my_request);

            $writer->addFilter(Zend_Log::EMERG);
            $writer->addFilter(Zend_Log::ALERT);
            $writer->addFilter(Zend_Log::CRIT);
            $writer->addFilter(Zend_Log::ERR);

            $logger->addWriter($writer);
        }

        //Запомним экземпляр объекта в реестре
        Zend_Registry::set("Zend_Log", $logMsg);
        Zend_Registry::set("Zend_LogEx", $logEx);
        Zend_Registry::set("Zend_LogStat", $logStat);

        // Запомним в сессии массив результатов поиска
        $Zend_Auth = Zend_Registry::get("Zend_Auth");
        if (!$Zend_Auth->search) {
            $Zend_Auth->search = array();
        }

        //------------ Сконфигурируем поиск по умолчанию -------------
        // Установим анализатор запросов в кодировке Utf8
        Zend_Search_Lucene_Analysis_Analyzer::setDefault(
                new Zend_Search_Lucene_Analysis_Analyzer_Common_Utf8_CaseInsensitive());


        //------ Инициализация файлового менеджера -------------
        Default_Plugin_SysBox::iniKCFinder();

        //---- Определение времени выполнения скрипта ----
        $infoProfiler = Default_Plugin_SysBox::Translate("Время выполнения") . " Bootstrap_initLog(): ";
        Default_Plugin_SysBox::profilerTime2Registry($_startTime, $infoProfiler);
    }

    /**
     * Инициализация Теста
     *
     * @return void
     */
    protected function _initTest() {
        
    }
}
