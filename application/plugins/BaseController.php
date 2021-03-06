<?php

/**
 * Default_Plugin_BaseController
 *
 * Plugin - it implements basic operations of controllers
 *
 * @uses       Zend_Controller_Action
 * @package    Module-Default
 * @subpackage Plugins
 * @author   Sergii Beskorovainyi <bsa2657@yandex.ru>
 * @license  MIT <http://www.opensource.org/licenses/mit-license.php>
 * @link     https://github.com/bsa-git/zf-myblog/
 */
class Default_Plugin_BaseController extends Zend_Controller_Action {

    /**
     * Start time
     *
     * @var float
     */
    protected $_startTimeDispatch = 0;

    /**
     * Database  adapter
     *
     * @var Zend_Db_Adapter_Abstract
     */
    public $db;

    /**
     * FlashMessenger
     *
     * @var Zend_Controller_Action_Helper_FlashMessenger
     */
    protected $_flashMessenger = null;

    /**
     * Breadcrumbs
     *
     * @var Default_Plugin_Breadcrumbs
     */
    protected $_breadcrumbs = null;

    /**
     * Redirector
     *
     * @var Zend_Controller_Action_Helper_Redirector
     */
    protected $_redirector = null;

    /**
     * isAjaxRequest - is Ajax request
     *
     * @var bool
     */
    protected $_isAjaxRequest = false;

    /**
     * identity - user identification object
     *
     * @var object
     */
    protected $_identity = null;

    /**
     * authenticated
     *
     * @var bool
     */
    protected $_authenticated = false;

    /**
     * isAdmin
     *
     * @var bool
     */
    protected $_isAdmin = false;

    /**
     * isEditor
     *
     * @var bool
     */
    protected $_isEditor = false;

    /**
     * isMember
     *
     * @var bool
     */
    protected $_isMember = false;
    
    /**
     * isCommentator
     *
     * @var bool
     */
    protected $_isCommentator = false;

    /**
     * isGuest
     *
     * @var bool
     */
    protected $_isGuest = false;

    /**
     * request - обьект запроса
     *
     * @var Zend_Request
     */
    protected $_request = null;

    /**
     * params - request parameters
     *
     * @var array
     */
    protected $_params = null;

    /**
     * zend_version
     *
     * @var string
     */
    protected $_zend_version = '';

    /**
     * config
     *
     * @var string
     */
    protected $_config = null;

    /**
     * locales - array of locales for the site
     *
     * @var array
     */
    protected $_locales = null;

    /**
     * _url_mvc - url to the module, controller and action
     * ex. /admin/user/news
     *
     * @var string
     */
    protected $_url_mvc = '';

    /**
     * _sessZendAuth - session object
     *
     * @var object
     */
    protected $_sessZendAuth = null;

    /**
     * userAgent
     *
     * @var object
     */
    protected $_userAgent = null;

    /**
     * _serializer - Zend_Serializer
     *
     * @var Zend_Serializer
     */
    protected $_serializer = null;

    /**
     * logMsg - message log
     *
     * @var Zend_Log
     */
    protected $_logMsg = null;

    /**
     * _logStat - statistic log
     *
     * @var Zend_Log
     */
    protected $_logStat = null;

    /**
     * _logEx - error log
     *
     * @var Zend_Log
     */
    protected $_logEx = null;

    /**
     * _modules - available modules
     *
     * @var array
     */
    protected $_modules = null;

    /**
     * report - report mode
     * only output message, without header, footer, left, right
     *
     * @var bool
     */
    protected $_report = null;

    /**
     * isIE
     *
     * @var bool
     */
    protected $_isIE = false;

    /**
     * isCompatibleBrowser - the user's browser is fully compatible
     * 
     * @var bool
     */
    protected $_isCompatibleBrowser = true;

    /**
     * isForbiddenBrowser - the user's browser is forbidden to use
     * 
     * @var bool
     */
    protected $_isForbiddenBrowser = false;

    /**
     * _browser - information about the current browser
     * 
     * array(
      'userAgent' => $u_agent,
      'name' => $bname,
      'short_name' => $ub,
      'version' => $version,
      'majorver' => $majorver,
      'minorver' => $minorver,
      'platform' => $platform,
      'pattern' => $pattern
      );
     *
     * @var array
     */
    protected $_browser = null;

    //---------------------------------

    /**
     * Initialization controller
     */
    function init() {

        //Начало времени выполнения диспечеризации
        $this->_startTimeDispatch = microtime(1);

        $request = $this->getRequest();
        $params = $request->getParams();

        // Get cofig
        $config = Zend_Registry::get('config');

        //$request->
        //Получим адаптер базы данных
        $this->db = Zend_Registry::get('db');

        //Зарегистрируем плагин FlashMessenger
        $this->_flashMessenger = $this->_helper->getHelper('FlashMessenger');
        //$this->initView();
        // Создадим обьект breadcrumbs и добавим начальную точку отсчета
        $this->_breadcrumbs = new Default_Plugin_Breadcrumbs();

        if ($params['module'] == 'default') {
            $this->_breadcrumbs->addStep($this->Translate('Главная'), $this->getUrl(null, 'index'));
        } elseif ($params['module'] == 'admin') {
            $this->_breadcrumbs->addStep($this->Translate('Администрирование'), $this->getUrl(null, 'index'));
        } elseif ($params['module'] == 'hr') {
            $this->_breadcrumbs->addStep($this->Translate('Персонал'), $this->getUrl(null, 'index'));
        }

        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            $this->view->authenticated = true;
            $this->_authenticated = true;
            $this->_identity = $auth->getIdentity();
            $this->view->identity = $this->_identity;
            $this->_isAdmin = ($this->_identity->user_type == 'administrator');
            $this->view->isAdmin = $this->_isAdmin;
            $this->_isEditor = ($this->_identity->user_type == 'editor');
            $this->view->isEditor = $this->_isEditor;
            $this->_isMember = ($this->_identity->user_type == 'member');
            $this->view->isMember = $this->_isMember;
            $this->_isCommentator = ($this->_identity->user_type == 'commentator');
            $this->view->isCommentator = $this->_isCommentator;
        } else {
            $this->_authenticated = false;
            $this->view->authenticated = false;
            $this->_isGuest = true;
            $this->view->isGuest = true;
        }


        // Получим url_mvc
        $this->_url_mvc = Default_Plugin_SysBox::getRouterURL();
        $this->view->url_mvc = $this->_url_mvc;

        // Получим обьект сессии
        $this->_sessZendAuth = Zend_Registry::get("Zend_Auth");

        // Получим обьект userAgent
        $bootstrap = $this->getInvokeArg('bootstrap');
        $this->_userAgent = $bootstrap->getResource('useragent');
        $this->view->userAgent = $this->_userAgent;

        // Определим текущий броузер
        $this->_isIE = Default_Plugin_SysBox::isIE();
        $this->view->isIE = $this->_isIE;
        $this->_browser = Default_Plugin_SysBox::getBrowser();
        $this->view->browser = $this->_browser;
        $this->_isCompatibleBrowser = Default_Plugin_SysBox::checkBrowser($config['user']['incompatible_browsers']);
        $this->view->isCompatibleBrowser = $this->_isCompatibleBrowser;
        $this->_isForbiddenBrowser = !Default_Plugin_SysBox::checkBrowser($config['user']['forbidden_browsers']);
        $this->view->isForbiddenBrowser = $this->_isForbiddenBrowser;

        //Создадим обьект Redirector
        $this->_redirector = $this->_helper->getHelper('Redirector');


        // Получим обьект сериализатора
        $this->_serializer = Zend_Serializer::factory('PhpSerialize');

        // Получим обьекты для логирования
        $this->_logMsg = Zend_Registry::get('Zend_Log');
        $this->_logStat = Zend_Registry::get('Zend_LogStat');
        $this->_logEx = Zend_Registry::get('Zend_LogEx');

        // Получим доступные модули для приложения
        $strModules = trim($config['user']['modules']['enable']);
        $strModules = str_replace(' ', '', $strModules);
        $strModules = strtolower($strModules);
        $modules = explode(';', $strModules);
        $this->_modules = $modules;
        $this->view->modules = $modules;

        // Пользовательские параметры
        $Zend_Auth = Zend_Registry::get('Zend_Auth');
        $this->view->scheme = $Zend_Auth->user_scheme;
        $this->view->user_main_name = trim($config['user']['main']['name']);
        $this->view->logo_url = trim($config['user']['main']['logo_url']);

        // Check forbidden browser
        if ($this->_isForbiddenBrowser) {
            Default_Plugin_Error::catchException(new Zend_Exception($this->Translate('Данная версия браузера запрещена к использованию! Установите более новую версию браузера.'), 403));
        }
    }

    //============ EVENT HANDLING CONTROLLER ==================

    /**
     * Event controller before dispatching
     */
    public function preDispatch() {

        //Определим наличие Ajax запроса
        $request = $this->getRequest();
        $this->_request = $request;
        $params = $request->getParams();
        $this->_params = $params;

        // Определим признак запроса через AjaxRequest
        $this->_isAjaxRequest = $request->isXmlHttpRequest();
        $this->view->isAjaxRequest = $request->isXmlHttpRequest();

        // Получим язык сайта
        $this->view->language = Default_Plugin_SysBox::getTranslateLocale();
        //Запомним список языков перевода
        $list_locales = Default_Plugin_SysBox::getTranslate()->getList();
        $this->_locales = $list_locales;
        $this->view->list_locales = $list_locales;


        // получим используемую версию Zend Framework
        $this->_zend_version = Zend_Version::VERSION;
        $this->view->zend_version = Zend_Version::VERSION;

        // получим конфигуратор приложения
        $this->view->config = Zend_Registry::get('config');
        $this->_config = Zend_Registry::get('config');

        // Получить google.maps.key
        $this->view->googleMapsKey = Default_Plugin_SysBox::getGoogleMapsKey('zf-myblog');

        // Этот параметр определяет режим отчета
        // на экран выводиться только содержание сообщения
        $this->_report = (bool) ( isset($params['report']) || ( isset($params['ajax']) && $params['ajax'] == 'post'));
        $this->view->report = $this->_report;
    }

    /**
     * Events after the controller dispatching
     */
    public function postDispatch() {
        $infoProfiler = '';
        //-------------------------

        $request = $this->getRequest();
        if ($this->_breadcrumbs) {
            $this->view->breadcrumbs = $this->_breadcrumbs;
            $this->view->title = $this->_breadcrumbs->getTitle();
        }

        //Передадим признак Ajax запроса
        $this->view->isAjaxRequest = $this->_isAjaxRequest;

        //Передадим сообщения в шаблоны
        $this->view->messages = $this->_flashMessenger->getMessages();

        // Запомним время выполения цикла диспетчеризации
        $params = $request->getParams();
        foreach ($params as $key => $value) {

            if (!is_string($value)) {
                continue;
            }

            if ($infoProfiler) {
                $infoProfiler .= "$key = $value<br>";
            } else {
                $infoProfiler .= $this->Translate("Время выполнения запроса") . ":<br />$key = $value<br />";
            }
        }
        $infoProfiler .= "<br />" . $this->Translate("Равно") . " = ";
        Default_Plugin_SysBox::profilerTime2Registry($this->_startTimeDispatch, $infoProfiler);
    }

    //============ ERRORS/MESSAGES IN FORMS ==================
    /**
     * Get Errors form an array
     * 
     * array( 
     *   'element_name'=> array( 
     *                           'error_type' => 'error_message' 
     *                          )  
     *   )
     * 
     * @param Zend_Form $form 
     * @return array 
     */
    public function getFormErrors(Zend_Form $form) {
        $arrErrors = array();
        $errorMessages = array();
        //---------------------------
        $elements = $form->getElements();
        foreach ($elements as $element) {
            if ($element->getErrors()) {
                $errors = $element->getErrors();
                foreach ($errors as $error) {
                    $messages = $element->getMessages();
                    $errorMessages[$error] = $messages[$error];
                }
            }
            if (count($errorMessages)) {
                //captcha
                $name = $element->getName();
                if ($name == 'captcha') {
                    $name .= '-input';
                }
                $arrErrors[$name] = $errorMessages;
                $errorMessages = array();
            }
        }

        if (count($arrErrors) == 0) {
            $arrErrors = NULL;
        }

        return $arrErrors;
    }

    /**
     * Receive an error message in the form of an array
     * 
     * ex. array( 
     *      Error form! Invalid data in the form introduced.
     *      Email : '111' Invalid e-mail address. Enter it in the format 'name@domain'
     *      Protection from spam : Introduced invalid characters
     * )
     * @param Zend_Form $form 
     * 
     * @return array 
     */
    public function getFormMessages(Zend_Form $form) {
        $errorMessages = array();
        //---------------------------
        $elements = $form->getElements();
        foreach ($elements as $element) {
            $label = $element->getLabel();    //getLabel();
            $label = trim($label);
            $label = trim($label, ':');
            $name = $element->getName();
            if ($name == 'captcha') {
                $label = 'Сaptcha - защита от спама ';
            }
            if ($element->getErrors()) {

                //Добавим первую строку предупреждения
                if (count($errorMessages) == 0) {
                    $errorMessages[] = '<em>' . Zend_Registry::get('Zend_Translate')->_('Ошибка формы! Неверно введены данные в форму.') . '</em>';
                }

                //Добавим сообщения об ошибках
                $errors = $element->getErrors();
                foreach ($errors as $error) {
                    $messages = $element->getMessages();
                    $errorMessages[] = '<em>' . $label . ':  ' . '</em>' . $messages[$error] . ';';
                }
            }
        }

        if (count($errorMessages) == 0) {
            $errorMessages = NULL;
        }
        return $errorMessages;
    }

    /**
     * Receive an error message in the form of a string
     *
     * @param Zend_Form $form
     *
     * @return string
     */
    public function getFormMessagesToString(Zend_Form $form) {
        $strMessages = '';
        //---------------------------
        $messages = $this->getFormMessages($form);
        if (!is_null($messages)) {
            foreach ($messages as $message) {
                $strMessages .= $message . '<br />';
            }
        }
        return $strMessages;
    }

    //********* ADDITIONAL FUNCTIONS ****************
    /**
     * Get URL
     * 
     * @param string $action
     * @param string $controller
     * @return string 
     */
    public function getUrl($action, $controller = null, $module = null, array $params = null) {
        $url = $this->_helper->url->simple($action, $controller, $module, $params);
        $url = rtrim($url, '/');

        return $url;
    }

    /**
     * Get URL for resource
     * 
     * @param string $text
     * @return string 
     */
    public function getUrlRes($url_res) {
        $url = Default_Plugin_SysBox::getUrlRes($url_res);
        return $url;
    }

    /**
     * Get nonstandard URL
     * 
     * @param array $options // URL parameters
     * @param string $route  // the router
     * @return string 
     */
    public function getCustomUrl($options, $route = null) {
        $url = $this->_helper->url->url($options, $route);
        $url = rtrim($url, '/');

        return $url;
    }

    /**
     * Translate text
     *
     * @return string
     */
    public function Translate($aText, $param1 = NULL, $param2 = NULL, $param3 = NULL) {
        $text = Zend_Registry::get('Zend_Translate')->_($aText);
        return sprintf($text, $param1, $param2, $param3);
    }

    /**
     * Send data to Json format
     *
     * @param array $data
     */
    public function sendJson($data) {

        $this->_helper->viewRenderer->setNoRender();

        $response = $this->getResponse();

        // Очистим буфер, если там были данные
        if (ob_get_length()) {
            $body = ob_get_contents();

            // Определим это как неизвестные данные
            // которых не должно было быть!
            $unexpected_message = array(
                'class_message' => 'warning',
                'messages' => array(
                    '<em>' . $this->Translate('Неизвестное сообщение') . '!</em>',
                    $body
                )
            );

            // Установим заголовок, даже если он уже установлен другим сообщеним
            $response->setHeader('content-type', 'application/json', TRUE);

            // Очистим данные
            ob_end_clean();

            // Запишем это неизвестное сообщение в данные для передачи
            $data['unexpected_message'] = $unexpected_message;
        } else {
            // Установим заголовок, даже если он уже установлен другим сообщеним
            $response->setHeader('content-type', 'application/json', TRUE);
        }


        echo Zend_Json::encode($data);
    }

    /**
     * Send json data as HTML format
     *
     * @param array $data
     */
    public function sendJson_Html($data) {

        $this->_helper->viewRenderer->setNoRender();

        $response = $this->getResponse();


        // Очистим буфер, если там были данные
        if (ob_get_length()) {
            $body = ob_get_contents();
            // Определим это как неизвестные данные
            // которых не должно было быть!
            $unexpected_message = array(
                'class_message' => 'warning',
                'messages' => array(
                    '<em>' . $this->Translate('Неизвестное сообщение') . '!</em>',
                    $body
                )
            );

            // Установим заголовок, даже если он уже установлен другим сообщеним
            $response->setHeader('content-type', 'text/html', TRUE);

            // Очистим данные
            ob_end_clean();

            // Запишем это неизвестное сообщение в данные для передачи
            $data['unexpected_message'] = $unexpected_message;
        } else {
            // Установим заголовок, даже если он уже установлен другим сообщеним
            $response->setHeader('content-type', 'text/html', TRUE);
        }
        $data = Zend_Json::encode($data);
        $data = htmlspecialchars($data);
        echo $data;
    }

    /**
     * Send data to text/html format
     *
     * @param string $html
     */
    public function sendHtml($html) {

        $this->_helper->viewRenderer->setNoRender();

        $response = $this->getResponse();

        $response->setHeader('content-type', 'text/html');

        // Очистим буфер, если там были данные
        if (ob_get_length()) {
            $body = ob_get_contents();

            // Установим заголовок, даже если он уже установлен другим сообщеним
            $response->setHeader('content-type', 'text/html', TRUE);

            // Очистим данные
            ob_end_clean();
        } else {
            // Установим заголовок, даже если он уже установлен другим сообщеним
            $response->setHeader('content-type', 'text/html', TRUE);
        }

        echo $html;
    }

    /**
     * Format date
     *
     * @param string|int $data      // Date
     * @param string $format        // Output format
     * @param string $input_format  // Input format
     *
     * @return string
     */
    public function dtFormat($date = null, $format = Zend_Date::DATE_MEDIUM, $input_format = Zend_Date::ISO_8601) {
        if ($date == null) {
            $date = 0;
        }
        $date = new Zend_Date($date, $input_format);
        $dtFormat = $date->get($format);
        return $dtFormat;
    }

    /**
     * Get the parameters for the corresponding page request in the database
     *
     * @param int $count
     *
     * @return array
     */
    public function getParamsPaginator($count) {
        $options = array();
        $arrFilter = array();
        //---------------------------
        $request = $this->getRequest();
        $params = $request->getParams();
        if (isset($params['rowsByPage'])) {
            $rowsByPage = (int) $params['rowsByPage'];
            if ($rowsByPage == -1) {
                $rowsByPage = $count;
            }
        } else {
            $rowsByPage = $count;
        }

        if (isset($params['filter'])) {
            $strFilter = stripslashes($params['filter']);
            $arrFilter = Zend_Json::decode($strFilter);
        }

        // Определим вызываемую страницу
        $page = isset($params['page']) ? $params['page'] : 1;

        // Определим направление сортировки
        $ascDescFlg = isset($params['ascDescFlg']) ? $params['ascDescFlg'] : 'ASC';

        // Определим колонку по которой будем сортировать
        $sort = isset($params['sortColumn']) ? $params['sortColumn'] : '';
        if ($sort && $sort !== '_nn_') {
            $sort = $sort . ' ' . $ascDescFlg;
            $options['order'] = $sort;
        } else {
            $params['sortColumn'] = '';
        }


        // Определим таблицу, которую нужно
        // присоединить для сортировки
        $joinTableForSort = isset($params['joinTableForSort']) ? $params['joinTableForSort'] : '';

        $numberOfPages = 0;
        $from = 0;
        $to = 0;
        $offset = 0;
        $limit = $rowsByPage;

        if ($count > 0) {
            $numberOfPages = floor($count / $rowsByPage);
            if (($count % $rowsByPage) > 0)
                $numberOfPages++;
            if ($page > $numberOfPages)
                $page = $numberOfPages;
            $from = (($page - 1) * $rowsByPage);
            $offset = $from;
            $from++;
            $to = ($page * $rowsByPage);
            if ($to > $count)
                $to = $count;

            $options['offset'] = $offset;
            $options['limit'] = $limit;
            $options['currentPage'] = $page;
            $options['total'] = $count;
            $options['fromPage'] = $from;
            $options['toPage'] = $to;
            $options['pages'] = $numberOfPages;
            $options['rowsByPage'] = $rowsByPage;
            $options['sortColumn'] = $params['sortColumn'];
            $options['ascDescFlg'] = $ascDescFlg; //$params['ascDescFlg'];
            $options['joinTableForSort'] = $joinTableForSort;
            $options['filter'] = $arrFilter;
        }else {
            $options['total'] = 0;
        }
        return $options;
    }

    /**
     * Get text markdown markup file
     * file is selected according to the localization
     * 
     * @param array $params Set params for [filename, content, type]
     * @return string
     */
    public function getMarkdown($params) {
        $arBox = new Default_Plugin_ArrayBox();
        $strBox = new Default_Plugin_String();
        $filename = isset($params['filename']) ? $params['filename'] : '';
        $strFile = isset($params['content']) ? $params['content'] : '';
        $type = isset($params['type']) ? $params['type'] : 'github'; //Type of Markdown: traditional, github, extra
        $title = '';
        $locale = Default_Plugin_SysBox::getTranslateLocale();
        $locale = $locale == 'uk' ? 'ru' : $locale;
        $title = "";
        $filename = trim($filename);
        $filename = str_replace('\\', '/', $filename);
        //-------------------------------------------
        if ($filename) {
            if (is_file($filename)) {
                $lastFilename = $arBox->set($filename, "/")->getLast();
                // Set title
                $title = $lastFilename;
                // Check word in uppercase
                $upperFilename = $strBox->set($lastFilename)->toUpper()->get();
                $isUpper = ($arBox->set($lastFilename, ".")->get(0) == $arBox->set($upperFilename, ".")->get(0));
                if ($isUpper) {
                    $locale = strtoupper($locale);
                }
                // Get the name of the file to a different locale 
                $lastFilename = $arBox->set($lastFilename, ".")->get(0) . "-{$locale}.md";
                $localeFilename = $arBox->set($filename, "/")->pop()->join('/') . "/{$lastFilename}";
                // Get file content
                if (is_file($localeFilename)) {
                    // Set title
                    $title = $lastFilename;
                    $strFile = file_get_contents($localeFilename);
                } else {
                    $strFile = file_get_contents($filename);
                }
            } else {

                // Get file name
                $filename = APPLICATION_TEMPLATES . "/{$this->_params['controller']}/{$filename}";

                if (!is_file($filename)) {
                    throw new Exception("File '{$filename}' does not exist.");
                }
                $lastFilename = $arBox->set($filename, "/")->getLast();
                // Set title
                $title = $lastFilename;

                // Check word in uppercase
                $upperFilename = $strBox->set($lastFilename)->toUpper()->get();
                $isUpper = ($arBox->set($lastFilename, ".")->get(0) == $arBox->set($upperFilename, ".")->get(0));
                if ($isUpper) {
                    $locale = strtoupper($locale);
                }
                // Get the name of the file to a different locale 
                $lastFilename = $arBox->set($lastFilename, ".")->get(0) . "-{$locale}.md";
                $localeFilename = $arBox->set($filename, "/")->pop()->join('/') . "/{$lastFilename}";
                // Get file content
                if (is_file($localeFilename)) {
                    // Set title
                    $title = $lastFilename;
                    $strFile = file_get_contents($localeFilename);
                } else {
                    $strFile = file_get_contents($filename);
                }
            }
        }

        switch ($type) {
            case 'traditional':
                $markdown = new \cebe\markdown\Markdown();
                break;
            case 'github':
                $markdown = new \cebe\markdown\GithubMarkdown();
                break;
            case 'extra':
                $markdown = new \cebe\markdown\MarkdownExtra();
                break;
            default:
                break;
        }
        // Get markdown parser text
        $text = $markdown->parse($strFile);
        // Get content
        $content = array('title' => $title, 'text' => "<div class=\"markdown-body\">{$text}</div>");
        return $content;
    }

}
