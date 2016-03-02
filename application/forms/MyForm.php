<?php

/**
 * Default_Form_MyForm
 * 
 * All our forms will inherit from this class.
 *
 *
 * @uses       Zend_Form
 * @package    Module-Default
 * @subpackage Forms
 * @author   Sergii Beskorovainyi <bsa2657@yandex.ru>
 * @license  MIT <http://www.opensource.org/licenses/mit-license.php>
 * @link     https://github.com/bsa-git/zf-myblog/
 */
class Default_Form_MyForm extends Zend_Form {

    /**
     * Constructor
     *
     * @param mixed $options
     * @return void
     */
    public function __construct($options = null)
    {
        parent::__construct($options);
    }
    
    /**
     * Decorators for the form element
     * decoration in the form of a table row ([label] [element])
     * 
     * @var array 
     */
    public $elTableDecorators = array(
        'ViewHelper',
        'Errors',
        //array('Description', array('tag' => 'td', 'class' => 'description')),
        array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element')),
        array('Label', array('tag' => 'td')),
        array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
    );
    
    /**
     * Decorators form to display the label group elements
     * decoration as a table heading row (tag = th)
     * 
     * @var array 
     */
    public $groupTableDecorators = array(
        //'ViewHelper',
        array('Label', array('tag' => 'strong', 'class' => 'caption_group')),
        array(array('th' => 'HtmlTag'), array('tag' => 'th', 'colspan'=>'2')),
        array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
    );
    /**
     * Decorators for form buttons
     * decoration in the form of a table row ([] [button])
     * 
     * @var array 
     */
    public $btnTableDecorators = array(
        'ViewHelper',
        array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element')),
        array(array('label' => 'HtmlTag'), array('tag' => 'td', 'placement' => 'prepend')),
        array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
    );
    /**
     * Decorators for form
     * as table decoration
     * 
     * @var array 
     */
    public $frmTableDecorators = array(
        'FormElements',
        array('HtmlTag', array('tag' => 'table')),
        'Form',
    );
    /**
     * Language of site
     *
     * @var string
     */
    protected  $_language = '';

    /**
     * Initialization form
     * 
     * return void
     */
    public function init() {

        parent::init();

        // Получим объект переводчика
        $translate = Zend_Registry::get('Zend_Translate');
        // Задаем объект переводчика для формы
        $this->setTranslator($translate);

        /* Specifies the prefix for samopisnyh elements, validators, filters, and decorators.
          This Zend Form will know where to find our custom items */

        $this->addElementPrefixPath('Default_Form_Validate', APPLICATION_PATH . '/forms/validate/', 'validate');
        $this->addElementPrefixPath('Default_Form_Filter', APPLICATION_PATH . '/forms/filter/', 'filter');
        $this->addElementPrefixPath('Default_Form_Decorator', APPLICATION_PATH . '/forms/decorator/', 'decorator');
        $this->addPrefixPath('Default_Form_Element', APPLICATION_PATH . '/forms/element/', 'element');

        // Получим язык сайта
        $this->_language = Default_Plugin_SysBox::getTranslateLocale();
    }

    /**
     * Get URL
     * 
     * @param string $action
     * @param string $controller
     * @param string $module
     * @param array $params
     * @return string 
     */
    public function getUrl($action = NULL, $controller = NULL, $module = NULL, array $params = NULL) {

        $helper = Zend_Controller_Action_HelperBroker::getStaticHelper('url');

        $url = $helper->simple($action, $controller, $module, $params);

        $url = rtrim($url, '/');

        return $url;
    }
    
    /**
     * Get Route URL
     * 
     * @param string $route
     * @param array $params
     * @return string 
     */
    public function getRouteUrl($route, $params) {

        $helper = Zend_Controller_Action_HelperBroker::getStaticHelper('url');

        $url = $helper->url($params, $route);

        $url = rtrim($url, '/');

        return $url;
    }

    /**
     * Translate text
     *
     * @return string
     */
    public function Translate($aText, $param1=NULL, $param2=NULL, $param3=NULL) {
        $text = Zend_Registry::get('Zend_Translate')->_($aText);
        return sprintf($text, $param1, $param2, $param3);
    }

}