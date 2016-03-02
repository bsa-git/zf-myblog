<?php

/**
 * Default_Plugin_ViewTemplater
 * 
 * Plugin - Smarty template management
 *
 *
 * @uses       Zend_View_Abstract 
 * @package    Module-Default
 * @subpackage Plugins
 * @author   Sergii Beskorovainyi <bsa2657@yandex.ru>
 * @license  MIT <http://www.opensource.org/licenses/mit-license.php>
 * @link     https://github.com/bsa-git/zf-myblog/
 */
class Default_Plugin_ViewSmarty extends Zend_View_Abstract {

    protected $_path;
    protected $_engine;
    
    /**
     * Constructor
     * 
     * @param string $module
     */
    public function __construct($module) {

        require_once APPLICATION_BASE . '/vendor/smarty/smarty/libs/Smarty.class.php';

        $this->_engine = new Smarty();
        $this->_engine->config_dir = APPLICATION_PATH . '/configs/';
        $this->_engine->plugins_dir = array(APPLICATION_PATH . '/views/plugins/', 'plugins');

        // Установим путь к шаблонам в зависимости от модуля
        if($module == 'default'){
           $this->_engine->template_dir = APPLICATION_TEMPLATES;
        }  else {
            $this->_engine->template_dir = APPLICATION_PATH . '/modules/'. $module .'/views/templates';
        }

        // Установим путь к директории компиляции шаблонов
        // в зависимости от модуля
        $this->_engine->compile_dir = APPLICATION_DATA . '/tmp/templates_c/' . $module . '/';

        // Установим путь к кешам шаблонов
        // в зависимости от модуля
        $this->_engine->cache_dir = APPLICATION_DATA . '/cache/' . $module . '/';
    }

    public function getEngine() {
        return $this->_engine;
    }

    public function __set($key, $val) {
        $this->_engine->assign($key, $val);
    }

    public function __get($key) {
        return $this->_engine->get_template_vars($key);
    }

    public function __isset($key) {
        return $this->_engine->get_template_vars($key) !== null;
    }

    public function __unset($key) {
        $this->_engine->clear_assign($key);
    }

    public function assign($spec, $value = null) {
        if (is_array($spec)) {
            $this->_engine->assign($spec);
            return;
        }

        $this->_engine->assign($spec, $value);
    }

    public function clearVars() {
        $this->_engine->clear_all_assign();
    }

    public function render($name) {
        return $this->_engine->fetch(strtolower($name));
    }

    public function _run() {
        
    }

}