<?php

/*
 * PGBrowser - A 'pretty good' mechanize-like php library for managing cookies and submitting forms.
 * Website: https://github.com/monkeysuffrage/pgbrowser
 *
 * <pre>
 * require 'pgbrowser.php';
 * 
 * $b = new PGBrowser();
 * $page = $b->get('http://www.google.com/');
 * echo $page->title;
 * </pre>
 * 
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @link http://code.nabla.net/doc/gantry4/class-phpQueryObject.html phpQueryObject
 * @link http://simplehtmldom.sourceforge.net/manual_api.htm SimpleHtmlDom
 *
 * @package PGBrowser
 * @author P Guardiario <pguardiario@gmail.com>
 * @version 0.4
 */

class Default_Plugin_PGBrowser {

    var $ch, $lastUrl, $parserType;

    function __construct($parserType = null) {
        $this->ch = curl_init();
        curl_setopt($this->ch, CURLOPT_USERAGENT, "PGBrowser/0.0.1 (http://github.com/monkeysuffrage/pgbrowser/)");
        curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($this->ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($this->ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($this->ch, CURLOPT_ENCODING, 'gzip,deflate,identity');
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, array(
            "Accept-Charset:	ISO-8859-1,utf-8;q=0.7,*;q=0.7",
            "Accept-Language:	en-us,en;q=0.5",
            "Connection: keep-alive",
            "Keep-Alive: 300",
            "Expect:"
        ));

        $this->parserType = $parserType;
    }

    function setProxy($host, $port, $username = '', $password = '') {
        curl_setopt($this->ch, CURLOPT_PROXY, "http://$host:$port");
        if ($username || $password) {
            curl_setopt($this->ch, CURLOPT_PROXYUSERPWD, "$username:$password");
        }
    }

    function setUserAgent($string) {
        curl_setopt($this->ch, CURLOPT_USERAGENT, $string);
    }

    function setTimeout($timeout) {
        curl_setopt($this->ch, CURLOPT_TIMEOUT_MS, $timeout);
    }

    function setCookie($filename = '', $strCookies = '') {
        if ($filename && is_file($filename)) {
            curl_setopt($this->ch, CURLOPT_COOKIEJAR, $filename);
            curl_setopt($this->ch, CURLOPT_COOKIEFILE, $filename);
        }
        if ($strCookies) {
            curl_setopt($this->ch, CURLOPT_COOKIE, $strCookies);
        }
    }

    function clean($str) {
        return preg_replace(array('/&nbsp;/'), array(' '), $str);
    }

    function getInfo($opt = 0) {
        if ($opt) {
            return curl_getinfo($this->ch, $opt);
        } else {
            return curl_getinfo($this->ch);
        }
    }

    /**
     * Получим страницу из файла или из строки
     * 
     * @param string $url Url 
     * @param string $html
     * @param string $filename
     * @return PGPage object of the page
     */
    function mock($url, $html = '', $filename = '') {
        if ($filename) {
            $response = file_get_contents($filename);
            $response = $this->clean($response);
        }
        if ($html) {
            $response = $this->clean($html);
        }
        return new PGPage($url, $response, $this);
    }

    function get($url) {
        curl_setopt($this->ch, CURLOPT_URL, $url);
        if (!empty($this->lastUrl))
            curl_setopt($this->ch, CURLOPT_REFERER, $this->lastUrl);
        curl_setopt($this->ch, CURLOPT_POST, false);
        $this->lastUrl = $url;
        $response = curl_exec($this->ch);
        return new PGPage($url, $this->clean($response), $this);
    }

    function post($url, $body) {
        curl_setopt($this->ch, CURLOPT_URL, $url);
        if (!empty($this->lastUrl))
            curl_setopt($this->ch, CURLOPT_REFERER, $this->lastUrl);
        curl_setopt($this->ch, CURLOPT_POST, true);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $body);
        $this->lastUrl = $url;
        $response = curl_exec($this->ch);
        return new PGPage($url, $this->clean($response), $this);
    }

}

class PGPage {

    var $url, $browser, $dom, $xpath, $_forms, $title, $html, $parser, $parserType;

    function __construct($url, $response, $browser) {
        $this->url = $url;
        $this->html = $response;
        $this->browser = $browser;
        $this->dom = new DOMDocument();
        @$this->dom->loadHTML($response);
        $this->xpath = new DOMXPath($this->dom);
        $this->title = ($node = $this->xpath->query('//title')->item(0)) ? $node->nodeValue : '';
        $this->forms = array();
        foreach ($this->xpath->query('//form') as $form) {
            $this->_forms[] = new PGForm($form, $this);
        }

        $this->setParser($browser->parserType, $response);
    }

    function setParser($parserType, $body) {
        switch (true) {
            case preg_match('/simple/i', $parserType):
                $this->parserType = 'simple';
                $this->parser = new Default_Plugin_SimpleHtmlDom($body);
                break;
            case preg_match('/phpquery/i', $parserType):

//                require_once("phpQuery.php");
                require_once("phpQuery-onefile.php");

                $this->parserType = 'phpquery';
                $this->parser = phpQuery::newDocumentHTML($body);
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

    function at($q, $el = null) {
        switch ($this->parserType) {
            case 'simple':
                $doc = $this->parser;
                $result = $el ? $doc->find($q, (int) $el) : $doc->find($q, 0);
                return $result;
            case 'phpquery':
                return $this->search($q, $el)->eq(0);
            default:
                return $this->xpath->query($q, $el)->item(0);
        }
    }

    function search($q, $el = null) {
        switch ($this->parserType) {
            case 'simple':
                $doc = $el ? $this->parser->find('#' . $el) : $this->parser;
                $result = $doc->find($q);
                return $result;
            case 'phpquery':
                phpQuery::selectDocument($this->parser);
                $doc = $el ? pq($el) : $this->parser;
                return $doc->find($q);
            default:
                return $this->xpath->query($q, $el);
        }
    }

}

class PGForm {

    var $dom, $page, $browser, $fields, $action, $method;

    function __construct($dom, $page) {
        $this->page = $page;
        $this->browser = $this->page->browser;
        $this->dom = $dom;
        $this->method = strtolower($this->dom->getAttribute('method'));
        if (empty($this->method))
            $this->method = 'get';
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
                return $this->browser->get($url);
            case 'post':
                return $this->browser->post($this->action, $body);
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

    public static function parse($string) {
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
