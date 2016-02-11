<?php

/**
 * Bootstrap
 * 
 * File boot and application initialization
 *
 * @uses    Zend_Application_Bootstrap_Bootstrap
 * @package Bootstrap
 */
class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {

    /**
     * Initialization Autoloader, Session, Auth, Locale,
     *
     * @return void
     */
    protected function _initAutoload() {

        $_startTime = microtime(1);

        //------- Define the autoloader parameters ------------
        // Define basic prefix and the base path to the resources for the default module
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

        //------- Create an application directory ------------
        Default_Plugin_SysBox::createAppPaths();
        
        //--------- Remember the configuration to register ---------
        $config = $this->_options;
        Zend_Registry::set('config', $config);

        //----------------- Set session ---------------
        // Start session
        Zend_Session::start();
        // Install option in order to prevent re-execution
        // Zend_Session::start() when calling (new Zend_Session_Namespace)
        Zend_Session::setOptions(array('strict' => true));

        // Obtain an instance session object for the appropriate namespace
        $Zend_Auth = new Zend_Session_Namespace('Zend_Auth');

        // Save to Registry
        Zend_Registry::set("Zend_Auth", $Zend_Auth);

        // Add a new type of resource controllers for plug-ins
        //$autoloader->addResourceType('cplugins', 'controllers/plugins', 'Controller_Plugins');
        //---- Configuring user authentication -----
        $auth = Zend_Auth::getInstance();
        $auth->setStorage(new Zend_Auth_Storage_Session());

        //------ Registering plugins ---------
        // Start 'FrontController'
        $this->bootstrap('FrontController');

        $front = Zend_Controller_Front::getInstance();

        // The plugin checks the user's access to resources
        $front->registerPlugin(
                new Default_Plugin_AclManager($auth));

        //------------ Configure language translation -------------
        
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

        //------------ Configure language translation modules -------------
        $translate->addTranslation(
                APPLICATION_PATH . '/modules/hr/languages/ru/My_Messages.php', 'ru');
        $translate->addTranslation(
                APPLICATION_PATH . '/modules/hr/languages/uk/My_Messages.php', 'uk');
        $translate->addTranslation(
                APPLICATION_PATH . '/modules/hr/languages/en/My_Messages.php', 'en');

        // Set the default translation language
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

        // Save to Registry
        Zend_Registry::set('Zend_Translate', $translate);

        //------------ Configure site localization -------------
        
        // Get type localization
        $paramLocal = Default_Plugin_SysBox::getLocalParam(
                        $Zend_Auth->translate_locale);
        // Set localization
        $locale = new Zend_Locale($paramLocal);
        // Set timezone
        date_default_timezone_set($config['user']['timezone']);
        // Save to Registry
        Zend_Registry::set('Zend_Locale', $locale);

        //------------ Set the color scheme of the site -------------
        if (!isset($Zend_Auth->user_scheme)) {
            $Zend_Auth->user_scheme = $config['user']['scheme'];
        } else {
            $scheme = $Zend_Auth->user_scheme;
            $newScheme = Default_Plugin_SysBox::getUserScheme($scheme);
            if ($newScheme !== $scheme) {
                $Zend_Auth->user_scheme = $newScheme;
            }
        }

        //---- Defining script execution time ----
        $infoProfiler = Default_Plugin_SysBox::Translate("Время выполнения") . " Bootstrap_initAutoload(): ";
        Default_Plugin_SysBox::profilerTime2Registry($_startTime, $infoProfiler);

        return $autoloader;
    }

    /**
     * Initialization routes
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

        //---- Defining script execution time ----
        $infoProfiler = Default_Plugin_SysBox::Translate("Время выполнения") . " Bootstrap_initRouter(): ";
        Default_Plugin_SysBox::profilerTime2Registry($_startTime, $infoProfiler);
    }

    /**
     * Initialization View
     *
     * @return void
     */
    protected function _initViews() {
        //-------------

        $_startTime = microtime(1);

        $this->bootstrap('view');
        //!!!! Initialization Smarty. It is performed in the class - Default_Plugin_AclManager
        //---- Defining script execution time ----
        $infoProfiler = "Время выполнения" . " Bootstrap_initViews(): ";
        Default_Plugin_SysBox::profilerTime2Registry($_startTime, $infoProfiler);
    }

    /**
     * Initialization DB
     *
     * @return void
     */
    protected function _initDb() {

        $_startTime = microtime(1);

        $arrParam = array();
        //--------------------
        try {
            
            //------- Copy the database, if needed ------------
            $dbname = $this->_options['resources']['db']['params']['dbname'];
            Default_Plugin_SysBox::copyDataBase($dbname);
            
            // Get parameters for the database connection
            $arrParam = $this->_options['resources']['db']['params'];
            
            // Connection to DB
            $db = Zend_Db::factory(
                            "PDO_SQLITE", $arrParam);
            $db->getConnection();
            // Setting the default adapter class heirs Zend_Db_Table_Abstract
            Zend_Db_Table_Abstract::setDefaultAdapter($db);
            // Save to Registry
            Zend_Registry::set('db', $db);

            //---- Defining script execution time ----
            $infoProfiler = Default_Plugin_SysBox::Translate("Время выполнения") . " Bootstrap_initDb(): ";
            Default_Plugin_SysBox::profilerTime2Registry($_startTime, $infoProfiler);

        } catch (Zend_Db_Adapter_Exception $e) {
            throw $e;
        } catch (Zend_Exception $e) {
            throw $e;
        }
    }

    /**
     * Initialization Log, Search, KCFinder
     *
     * @return void
     */
    protected function _initLog() {
        $params = array();
        //------------------
        $_startTime = microtime(1);

        //Получим конфигурацию
        $config = $this->_options;

        //----- Create Zend_Log object -----
        $columnMapping = array(
            'ts' => 'timestamp',
            'msg' => 'message',
            'pr' => 'priority',
            'pr_name' => 'priorityName');

        $countMsg = $config['logging']['log']['max_rows'];
        $countEx = $config['logging']['exeption']['max_rows'];
        $countStat = $config['logging']['statistics']['max_rows'];

        // Get DB
        $db = Zend_Registry::get('db');
        // Set params
        $params['db'] = $db;
        $params['columnMap'] = $columnMapping;

        // Create writer for DB
        $params['table'] = 'log_msg';
        $params['max_rows'] = $countMsg;
        $writerMsg = new Default_Model_Log($params);
        $params['table'] = 'log_error';
        $params['max_rows'] = $countEx;
        $writerEx = new Default_Model_Log($params);
        $params['table'] = 'log_stat';
        $params['max_rows'] = $countStat;
        $writerStat = new Default_Model_Log($params);

        // Create logers
        $logMsg = new Zend_Log($writerMsg);
        $logEx = new Zend_Log($writerEx);
        $logStat = new Zend_Log($writerStat);

        // Adding new priorities for the $logMsg
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

        // Adding new priorities for the $logStat
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

        // Save to Registry
        Zend_Registry::set("Zend_Log", $logMsg);
        Zend_Registry::set("Zend_LogEx", $logEx);
        Zend_Registry::set("Zend_LogStat", $logStat);

        // Remember in the session array of search results
        $Zend_Auth = Zend_Registry::get("Zend_Auth");
        if (!$Zend_Auth->search) {
            $Zend_Auth->search = array();
        }

        //------------ Configure default search -------------
        
        // Establish a query analyzer in the coding Utf8
        Zend_Search_Lucene_Analysis_Analyzer::setDefault(
                new Zend_Search_Lucene_Analysis_Analyzer_Common_Utf8_CaseInsensitive());


        //------ Initialization file manager -------------
        Default_Plugin_SysBox::iniKCFinder();

        //---- Defining script execution time ----
        $infoProfiler = Default_Plugin_SysBox::Translate("Время выполнения") . " Bootstrap_initLog(): ";
        Default_Plugin_SysBox::profilerTime2Registry($_startTime, $infoProfiler);
    }

    /**
     * Initialization test
     *
     * @return void
     */
    protected function _initTest() {
        
    }
}
