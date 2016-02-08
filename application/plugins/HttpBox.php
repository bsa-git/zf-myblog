<?php

/*
 * Класс - Default_Plugin_HttpBox
 * 
  Удаленный доступ HTTP по URL
 * данный класс выполняет следующие действия:
 * - получение содержимого ресурса по запросу к URL
 * - парсинг, полученного ресурса (Forms, Links ...)
 * - разные виды парсингов: simple(Default_Plugin_SimpleHtmlDom),
 * phpquery(phpQuery.php), zend(Zend_Dom_Query)
 * - работа с формами, заполнение полей и  submit
 * 
  $url = 'http://zf-myblog:8080/account/login';
  $http = new Default_Plugin_HttpBox('zend');
  $page = $http->get($url);
  $form = $page->form();
  if ($form) {
  $form->set('username', 'user2');
  $form->set('password', '222222');
  $page = $form->submit();
  $title = $page->title;
  }

  $parserType = $http->parserType;

  $elList = $page->search('div.box');
  if ($parserType == 'zend') {
  foreach ($elList as $el) {
  $html = $el->nodeValue;
  }
  }
  if ($parserType == 'simple') {
  foreach ($elList as $el) {
  $html = $el->innertext;
  }
  }
  if ($parserType == 'phpquery') {
  foreach ($elList as $el) {
  $html = pq($el)->html();
  }
  }

 * 
 * 
 * @uses       
 * @package    Module-Default
 * @subpackage Plugins
 */

class Default_Plugin_HttpBox {

    /**
     * Клиент HTTP
     *
     * @var Zend_Http_Client
     */
    public $client = null;

    /**
     * The last URL, as string
     *
     * @var string
     */
    public $last_url = null;

    /**
     * The last request, as string
     *
     * @var string
     */
    public $last_request = null;

    /**
     * The last response, as Zend_Http_Response
     *
     * @var Zend_Http_Response
     */
    public $last_response = null;

    /**
     * The last cookies, as array
     * 
     * 
     * Array storing cookies
     *
     * Cookies are stored according to domain and path:
     * $cookies
     *  + www.mydomain.com
     *    + /
     *      - cookie1
     *      - cookie2
     *    + /somepath
     *      - othercookie
     *  + www.otherdomain.net
     *    + /
     *      - alsocookie
     *
     * @var array
     */
    public $last_cookies = null;

    /**
     * The last config, as array
     *
     * @var array
     */
    protected $last_config = null;

    /**
     * The parser type
     *
     * @var array
     */
    public $parserType = null;

    /**
     * The charset 
     *
     * @var string
     */
    public $charset = "utf-8";

    /*
     * Конструктор класса
     * 
     * @param string $parserType //simple(Default_Plugin_SimpleHtmlDom), phpquery(phpQuery.php), zend(Zend_Dom_Query)
     * @param array $config
     *  $config["adapter"] = "Zend_Http_Client_Adapter_Proxy" ;socket(default),proxy,curl,test
     *  $config["maxredirects"] = 5 ;Максимальное количество последующих перенаправлений (0 = ни одного перенаправления)
     *  $config["timeout"] = 10 ; Таймаут соединения в секундах
     *  $config["strictredirects"] = false ; Строгое следование спецификации RFC при перенаправлениях 
     *  $config["useragent"] = "Zend_Http_Client" ; Идентификатор агента пользователя (отправляется в заголовке запроса)
     *  $config["httpversion"] = 1.1 ; Версия протокола HTTP
     *  $config["keepalive"] = false ;Включение поддержки соединения keep-alive с сервером - повышает поизводительность.
     *  $config["headers"]["Referer"]="http://test.com" ; URI ресурса, после которого клиент сделал текущий запрос
     *  $config["headers"]["X-Requested-With"]="XMLHttpRequest" ; AJAX запрос 
     * 
     * @param string $url
     * 
     * 
     */

    function __construct($parserType, $config = array(), $url = null) {

        // Создадим обьект Zend_Http_Client
//        $this->client = new Default_Plugin_HttpClientCli($url);
        $this->client = new Zend_Http_Client($url);

        // attach a new cookie jar to your Zend_Http_Client object
        $this->client->setCookieJar();

        // Сохраним последнюю конфигурацию
        $this->last_config = $this->_setConfig($config);

        // Запомним последний URL
        if ($url) {
            $this->last_url = $url;
        }

        // Установим тип парсера
        if ($parserType) {
            $this->parserType = $parserType;
        }
    }

    function setParser($parserType) {
        $this->parserType = $parserType;
    }

    private function _setConfig($params = array()) {
        $config = array();
        //----------------
        if ($this->last_config) {
            $config = $this->last_config;
        } else {
            // Получим файл конфигурации
            $ini = Zend_Registry::get('config');
            $adapter = $ini['http']['adapter'];

            // Set the configuration parameters
            if ($adapter == 'proxy') {
                $config['adapter'] = 'Zend_Http_Client_Adapter_Proxy';
                $config['proxy_host'] = $ini['proxy']['host'];
                $config['proxy_port'] = $ini['proxy']['port'];
                $config['proxy_user'] = $ini['proxy']['user'];
                $config['proxy_pass'] = $ini['proxy']['pass'];
            } elseif ($adapter == 'curl') {
                $config['adapter'] = 'Zend_Http_Client_Adapter_Curl';
            } elseif ($adapter == 'test') {
                $config['adapter'] = 'Zend_Http_Client_Adapter_Test';
            } else {
                $config['adapter'] = 'Zend_Http_Client_Adapter_Socket';
            }
            $config['maxredirects'] = (int) $ini['http']['maxredirects'];
            $config['timeout'] = (int) $ini['http']['timeout'];
            $config['strictredirects'] = (bool) $ini['http']['strictredirects'];
            $config['useragent'] = (string) $ini['http']['useragent'];
            $config['httpversion'] = floatval($ini['http']['httpversion']);
            $config['keepalive'] = (bool) $ini['http']['keepalive'];
        }


        // Получим окончателные параметры для запроса
        $config = $params + $config;

        // Установим параметры для клиента
        $this->client->setConfig($config);

        // Установим заголовки 
        if ($config['headers']) {
            $this->client->setHeaders($config['headers']);
        }
        return $config;
    }

    function clean($str) {
        return preg_replace(array('/&nbsp;/'), array(' '), $str);
    }

    function get($url = null, $params = null, $config = array()) {
        $_config = array();
        $arrParams = array();
        //-----------------
        // Установим URL
        if ($url) {
            $this->client->setUri($url);
        } else {
            $url = $this->client->getUri(TRUE);
        }

        // Установим параметры запроса
        if ($params) {
            if (is_string($params)) {
                // Преобразуем строку запроса в массив
                parse_str($params, $arrParams);
                $this->client->setParameterGet($arrParams);
            } else {
                $this->client->setParameterGet($params);
            }
        }

        // Установим заголовок Referer
        if (!empty($this->last_url)) {
            $_config['headers']['Referer'] = $this->last_url;
        }
        // Запомним последний URL
        $this->last_url = $url;

        // Обьединим два массива
        $config = $_config + $config;

        // Сохраним последнюю конфигурацию
        $this->last_config = $this->_setConfig($config);

        // Выполним запрос
        $response = $this->client->request();
        $html = $response->getBody();

        // Запомним последний запрос в виде строки
        $this->last_request = $this->client->getLastRequest();

        // Запомним последний ответ в виде Zend_Http_Response
        $this->last_response = $this->client->getLastResponse();

        // Запомним последние полученные Сookies
        $this->last_cookies = $this->client->getCookieJar()->getAllCookies();

        if ($response->isSuccessful()) {
            return new PGPage($this->client->getUri(TRUE), $this->clean($html), $this);
        } else {
            throw new Zend_Exception($response->getMessage(), $response->getStatus());
        }
    }

    function post($url = null, $params = null, $config = array()) {
        $_config = array();
        $arrParams = array();
        //-----------------
        // Установим URL
        if ($url) {
            $this->client->setUri($url);
        } else {
            $url = $this->client->getUri(TRUE);
        }

        // Установим параметры запроса
        if ($params) {
            if (is_string($params)) {
                // Преобразуем строку запроса в массив
                parse_str($params, $arrParams);
                $this->client->setParameterPost($arrParams);
            } else {
                $this->client->setParameterPost($params);
            }
        }

        // Установим заголовок Referer
        if (!empty($this->last_url)) {
            $_config['headers']['Referer'] = $this->last_url;
        }
        // Запомним последний URL
        $this->last_url = $url;

        // Обьединим два массива
        $config = $_config + $config;

        // Сохраним последнюю конфигурацию
        $this->last_config = $this->_setConfig($config);

        // Выполним запрос
        $response = $this->client->request("POST");
        $html = $response->getBody();

        // Запомним последний запрос в виде строки
        $this->last_request = $this->client->getLastRequest();

        // Запомним последний запрос в виде Zend_Http_Response
        $this->last_response = $this->client->getLastResponse();

        // Запомним последние полученные Сookies
        $this->last_cookies = $this->client->getCookieJar()->getAllCookies();

        return new PGPage($this->client->getUri(TRUE), $this->clean($html), $this);
    }

    /**
     * Получим страницу из файла или из строки
     * 
     * @param string $source
     * @param string $url Url 
     * @return PGPage object of the page
     */
    function getPage($source, $url = '') {
        if (is_file($source)) {
            $html = file_get_contents($source);
            $html = $this->clean($html);
        } else {
            $html = $this->clean($source);
        }
        //is_file($filename)
        //is_link($filename)
        return new PGPage($url, $html, $this);
    }

    /**
     * Set the raw (already encoded) POST data.
     *
     * This function is here for two reasons:
     * 1. For advanced user who would like to set their own data, already encoded
     * 2. For backwards compatibilty: If someone uses the old post($data) method.
     *    this method will be used to set the encoded data.
     *
     * $data can also be stream (such as file) from which the data will be read.
     *
     * @param string|resource $data
     * @param string $enctype
     * @return Default_Plugin_HttpBox
     */
    function postRawData($data, $enctype = null) {
        $this->client->setRawData($data, $enctype)->request('POST');
        return $this;
    }

    /**
     * Set the raw (already encoded) PUT data.
     *
     * This function is here for two reasons:
     * 1. For advanced user who would like to set their own data, already encoded
     * 2. For backwards compatibilty: If someone uses the old post($data) method.
     *    this method will be used to set the encoded data.
     *
     * $data can also be stream (such as file) from which the data will be read.
     * 
     * $fp = fopen("mybigfile.zip", "r"); 
     * $this->client->putStreamData($fp, 'application/zip');
     * 
     * @param resource $data
     * @param string $enctype
     * @return Default_Plugin_HttpBox
     */
    function putStreamData($data, $enctype = null) {
        $this->client->setRawData($data, $enctype)->request('PUT');
        return $this;
    }

    /**
     * Get Stream Data.
     *
     *
     * $data can also be stream (such as file) to which the data will be save.
     * 
     * $client->setStream(); // will use temp file
     * $response = $client->request('GET');
     * // copy file
     * copy($response->getStreamName(), "my/downloads/file");
     * // use stream
     * $fp = fopen("my/downloads/file2", "w");
     * stream_copy_to_stream($response->getStream(), $fp);
     * // Also can write to known file
     * $client->setStream("my/downloads/myfile")->request('GET');
     * 
     * @param resource $data
     * @param string $enctype
     * @return Default_Plugin_HttpBox
     */
    function getStreamData($filename = null) {

        if (is_string($filename)) {
            $this->client->setStream($filename)->request('GET');
        } else {
            $this->client->setStream(); // will use temp file
            $this->client->request('GET');
        }

        // Запомним последний запрос в виде строки
        $this->last_request = $this->client->getLastRequest();

        // Запомним последний запрос в виде Zend_Http_Response
        $this->last_response = $this->client->getLastResponse();
        return $this;
    }

    /**
     * Set a file to upload (using a POST request)
     *
     * Can be used in two ways:
     *
     * 1. $data is null (default): $filename is treated as the name if a local file which
     *    will be read and sent. Will try to guess the content type using mime_content_type().
     * 2. $data is set - $filename is sent as the file name, but $data is sent as the file
     *    contents and no file is read from the file system. In this case, you need to
     *    manually set the Content-Type ($ctype) or it will default to
     *    application/octet-stream.
     *
     * @param string $filename Name of file to upload, or name to save as
     * @param string $formname Name of form element to send as
     * @param string $data Data to send (if null, $filename is read and sent)
     * @param string $ctype Content type to use (if $data is set and $ctype is
     *     null, will be application/octet-stream)
     * @return Default_Plugin_HttpBox
     * @throws Zend_Http_Client_Exception
     */
    function upload($filename, $formname, $data = null, $ctype = null) {
        $this->client->setFileUpload($filename, $formname, $data, $ctype);
        return $this;
    }

    /**
     * Set HTTP authentication parameters
     *
     * $type should be one of the supported types - see the self::AUTH_*
     * constants.
     *
     * To enable authentication:
     * <code>
     * $this->setAuth('shahar', 'secret', Zend_Http_Client::AUTH_BASIC);
     * </code>
     *
     * To disable authentication:
     * <code>
     * $this->setAuth(false);
     * </code>
     *
     * @see http://www.faqs.org/rfcs/rfc2617.html
     * @param string|false $user User name or false disable authentication
     * @param string $password Password
     * @param string $type Authentication type
     * @return Default_Plugin_HttpBox
     * @throws Zend_Http_Client_Exception
     */
    function httpAuth($user, $password = '', $type = Zend_Http_Client::AUTH_BASIC) {
        $this->client->setAuth($user, $password, $type);
        return $this;
    }

    /**
     * Clear all GET and POST parameters
     *
     * Should be used to reset the request parameters if the client is
     * used for several concurrent requests.
     *
     * clearAll parameter controls if we clean just parameters or also
     * headers and last_*
     *
     * @param bool $clearAll Should all data be cleared?
     * @return Default_Plugin_HttpBox
     */
    function resetParameters($clearAll = false) {
        $this->last_url = null;
        $this->last_request = null;
        $this->last_response = null;
        $this->last_cookies = null;
        $this->last_config = null;

        $this->client->resetParameters($clearAll);
        return $this;
    }
    
    /**
     * Проверка является ли URL отладочным
     * 
     * @params string $url
     * @return bool 
     */
    public static function isDebugUrl($url) {
        $arrQuery = array();
        $debug = FALSE;
        //--------------------
        // Установим признак отладки. Если обращение к "localhost"
        // и есть параметр в запросе "XDEBUG_SESSION_START"
        $arrUrl = parse_url($url);
        if ($arrUrl["query"]) {
            $query = $arrUrl["query"];
            parse_str($query, $arrQuery);
            $debug = $arrQuery["XDEBUG_SESSION_START"];
        }
        return $debug;
    }

    /**
     * Returns User IP Address
     * @params
     *        IN:  NONE
     *        OUT: ip address(0.0.0.0)
     */
    public static function getUserIP() {
        $ip = null;
        if ((isset($_SERVER['HTTP_X_FORWARDED_FOR'])) &&
                (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif ((isset($_SERVER['HTTP_CLIENT_IP'])) &&
                (!empty($_SERVER['HTTP_CLIENT_IP']))) {
            $ip = explode(".", $_SERVER['HTTP_CLIENT_IP']);
            $ip = "{$ip[3]}.{$ip[2]}.{$ip[1]}.{$ip[0]}";
        } elseif ((!isset($_SERVER['HTTP_X_FORWARDED_FOR'])) &&
                (empty($_SERVER['HTTP_X_FORWARDED_FOR'])) &&
                (!isset($_SERVER['HTTP_CLIENT_IP'])) &&
                (empty($_SERVER['HTTP_CLIENT_IP'])) &&
                (isset($_SERVER['REMOTE_ADDR']))) {
            $ip = ($_SERVER['REMOTE_ADDR']);
        } else {
            // ip is null
        }
        return ($ip);
    }

    /**
     * Получить содержание буфера INPUT для PHP
     * 
     */
    public static function getInputPHP() {
        return file_get_contents("php://input");
    }

}

class PGPage {

    var $url, $client, $dom, $xpath, $_forms, $_links, $title, $html, $parser, $parserType;

    function __construct($url, $html, $client) {
        $this->url = $url;
        $this->html = $html;
        $this->client = $client;
        $this->dom = new Default_Plugin_DomBox();
        @$this->dom->loadHTML($html);
        $this->xpath = new DOMXPath($this->dom);
        $this->title = ($node = $this->xpath->query('//title')->item(0)) ? $node->nodeValue : '';
        $this->_forms = array();
        $this->_links = array();

        // Найдем формы
        foreach ($this->xpath->query('//form') as $form) {
            $this->_forms[] = new PGForm($form, $this);
        }
        // Найдем ссылки
        foreach ($this->xpath->query('//a') as $link) {
            $this->_links[] = $link;
        }

        $this->setParser($client->parserType, $html);
    }

    function setParser($parserType, $html) {
        switch (true) {
            case preg_match('/zend/i', $parserType):
                $this->parserType = 'zend';
                $this->parser = new Zend_Dom_Query();
                @$this->parser->setDocumentHtml($html);
                break;
            case preg_match('/simple/i', $parserType):
                $this->parserType = 'simple';
                $this->parser = new Default_Plugin_SimpleHtmlDom($html);
                break;
            case preg_match('/phpquery/i', $parserType):

//                require_once("phpQuery.php");
                require_once("phpQuery-onefile.php");
                
                $this->parserType = 'phpquery';
                $this->parser = phpQuery::newDocumentHTML($html, $this->client->charset);
                phpQuery::selectDocument($this->parser);
                break;
            default:
                $this->parserType = 'xpath';
                $this->parser = $this->xpath;
                break;
        }
    }

    function forms() {
        if (func_num_args())
            return $this->_forms[func_get_arg(0)];
        return $this->_forms;
    }

    function form() {
        return $this->_forms[0];
    }

    function links() {
        if (func_num_args())
            return $this->_links[func_get_arg(0)];
        return $this->_links;
    }

    function link() {
        return $this->_links[0];
    }

    function at($q, $el = null) {
        $result = NULL;
        if (isset($el)) {
            $el = (int) $el;
        } else {
            $el = 0;
        }
        //--------------------
        switch ($this->parserType) {
            case 'zend':
                $doc = $this->parser;
                $items = $doc->query($q);
                if (count($items)) {
                    $index = 0;
                    foreach ($items as $item) {
                        if ($el == $index) {
                            $result = $item;
                            break;
                        }
                        $index++;
                    }
                }
                return $result;
            case 'simple':
                $doc = $this->parser;
                $result = $el ? $doc->find($q, (int) $el) : $doc->find($q, 0);
                return $result;
            case 'phpquery':
                $items = pq($q);
                if (count($items)) {
                    $index = 0;
                    foreach ($items as $item) {
                        if ($el == $index) {
                            $result = pq($item);
                            break;
                        }
                        $index++;
                    }
                }
                return $result;
            default:
                return $this->xpath->query($q)->item($el);
        }
    }

    function search($q) {
        switch ($this->parserType) {
            case 'zend':
                return $this->parser->query($q);
            case 'simple':
                return $this->parser->find($q);
            case 'phpquery':
                return pq($q);
            default:
                return $this->xpath->query($q);
        }
    }

}

class PGForm {

    var $dom, $page, $client, $fields, $action, $method;

    function __construct($dom, $page) {
        $this->page = $page;
        $this->client = $this->page->client;
        $this->dom = $dom;
        $this->method = strtolower($this->dom->getAttribute('method'));
        if (empty($this->method)) {
            $this->method = 'get';
        }
        $this->action = phpUri::parse($this->page->url)->join($this->dom->getAttribute('action'));
        $this->initFields();
    }

    function set($key, $value) {
        $this->fields[$key] = $value;
    }

    function submit() {
        $body = http_build_query($this->fields);

        switch ($this->method) {
            case 'get':
                $url = $this->action . '?' . $body;
                return $this->client->get($url);
            case 'post':
                return $this->client->post($this->action, $body);
            default: echo "Unknown form method: $this->method\n";
        }
    }

    function initFields() {
        $this->fields = array();
        foreach ($this->page->xpath->query('.//input|.//select', $this->dom) as $input) {
            $set = true;
            $value = $input->getAttribute('value');
            $type = $input->getAttribute('type');
            $name = $input->getAttribute('name');
            $tag = $input->tagName;
            switch (true) {
                case $type == 'submit':
                case $type == 'button':
                    continue 2;
                    break;
                case $type == 'checkbox':
                    if (!$input->getAttribute('checked')) {
                        continue 2;
                        break;
                    }
                    $value = empty($value) ? 'on' : $value;
                    break;
                case $tag == 'select':
                    if ($input->getAttribute('multiple')) {
                        // what to do here?
                        $set = false;
                    } else {
                        if ($selected = $this->page->xpath->query('.//option[@selected]', $input)->item(0)) {
                            $value = $selected->getAttribute('value');
                        } else {
                            $value = $this->page->xpath->query('.//option', $input)->item(0)->getAttribute('value');
                        }
                    }
            }
            if ($set)
                $this->fields[$name] = $value;
        }
    }

    function doPostBack($attribute) {
        preg_match_all("/'([^']*)'/", $attribute, $m);
        $this->set('__EVENTTARGET', $m[1][0]);
        $this->set('__EVENTARGUMENT', $m[1][1]);
        $this->set('__ASYNCPOST', 'true');
        return $this->submit();
    }

}

class phpUri {

    var $scheme, $authority, $path, $query, $fragment;

    function __construct($string) {
        preg_match_all('/^(([^:\/?#]+):)?(\/\/([^\/?#]*))?([^?#]*)(\?([^#]*))?(#(.*))?$/', $string, $m);
        $this->scheme = $m[2][0];
        $this->authority = $m[4][0];
        $this->path = $m[5][0];
        $this->query = $m[7][0];
        $this->fragment = $m[9][0];
    }

    static function parse($string) {
        $uri = new phpUri($string);
        return $uri;
    }

    function join($string) {
        $uri = new phpUri($string);
        switch (true) {
            case!empty($uri->scheme): break;
            case!empty($uri->authority): break;
            case empty($uri->path):
                $uri->path = $this->path;
                if (empty($uri->query))
                    $uri->query = $this->query;
            case strpos($uri->path, '/') === 0: break;
            default:
                $base_path = $this->path;
                if (strpos($base_path, '/') === false) {
                    $base_path = '';
                } else {
                    $base_path = preg_replace('/\/[^\/]+$/', '/', $base_path);
                }
                if (empty($base_path) && empty($this->authority))
                    $base_path = '/';
                $uri->path = $base_path . $uri->path;
        }
        if (empty($uri->scheme)) {
            $uri->scheme = $this->scheme;
            if (empty($uri->authority))
                $uri->authority = $this->authority;
        }
        return $uri->to_str();
    }

    function normalize_path($path) {
        if (empty($path))
            return '';
        $normalized_path = $path;
        $normalized_path = preg_replace('`//+`', '/', $normalized_path, -1, $c0);
        $normalized_path = preg_replace('`^/\\.\\.?/`', '/', $normalized_path, -1, $c1);
        $normalized_path = preg_replace('`/\\.(/|$)`', '/', $normalized_path, -1, $c2);
        $normalized_path = preg_replace('`/[^/]*?/\\.\\.(/|$)`', '/', $normalized_path, -1, $c3);
        $num_matches = $c0 + $c1 + $c2 + $c3;
        return ($num_matches > 0) ? $this->normalize_path($normalized_path) : $normalized_path;
    }

    function to_str() {
        $ret = "";
        if (!empty($this->scheme))
            $ret .= "$this->scheme:";
        if (!empty($this->authority))
            $ret .= "//$this->authority";
        $ret .= $this->normalize_path($this->path);
        if (!empty($this->query))
            $ret .= "?$this->query";
        if (!empty($this->fragment))
            $ret .= "#$this->fragment";
        return $ret;
    }

}

