<?php

/**
 * Default_Form_MyForm
 * 
 * Класс наследник Zend_Form, помогает настроить окружение.
 * Все наши формы будут наследоваться от этого класса.
 *
 *
 * @uses       Zend_Form
 * @package    Module-Default
 * @subpackage Forms
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
     * Декораторы для элемента формы
     * оформление в виде строки таблицы ([метка] [элемент])
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
     * Декораторы формы для отображения метки группы элементов 
     * оформление в виде строки заголовка таблицы (tag = th)
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
     * Декораторы для кнопки формы
     * оформление в виде строки таблицы ([] [кнопка])
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
     * Декораторы для формы
     * оформление в виде таблицы
     * 
     * @var array 
     */
    public $frmTableDecorators = array(
        'FormElements',
        array('HtmlTag', array('tag' => 'table')),
        'Form',
    );
    /**
     * Язык сайта
     *
     * @var string
     */
    protected  $_language = '';

    /**
     * Инициализация формы
     * 
     * return void
     */
    public function init() {
        // Вызов родительского метода
        parent::init();

        // Получим объект переводчика
        $translate = Zend_Registry::get('Zend_Translate');
        // Задаем объект переводчика для формы
        $this->setTranslator($translate);

        /* Задаем префиксы для самописных элементов, валидаторов, фильтров и декораторов.
          Благодаря этому Zend_Form будет знать где искать наши самописные элементы */

        $this->addElementPrefixPath('Default_Form_Validate', APPLICATION_PATH . '/forms/validate/', 'validate');
        $this->addElementPrefixPath('Default_Form_Filter', APPLICATION_PATH . '/forms/filter/', 'filter');
        $this->addElementPrefixPath('Default_Form_Decorator', APPLICATION_PATH . '/forms/decorator/', 'decorator');
        $this->addPrefixPath('Default_Form_Element', APPLICATION_PATH . '/forms/element/', 'element');

        // Получим язык сайта
        $this->_language = Default_Plugin_SysBox::getTranslateLocale();
    }

    /**
     * Получить URL
     * 
     * @param string $action
     * @param string $controller
     * @return string 
     */
    public function getUrl($action = NULL, $controller = NULL, $module = NULL, array $params = NULL) {

        $helper = Zend_Controller_Action_HelperBroker::getStaticHelper('url');

        $url = $helper->simple($action, $controller, $module, $params);

        $url = rtrim($url, '/');

        return $url;
    }
    
    /**
     * Получить Route URL
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
     * Сделать перевод текста
     *
     * @return string
     */
    public function Translate($aText, $param1=NULL, $param2=NULL, $param3=NULL) {
        $text = Zend_Registry::get('Zend_Translate')->_($aText);
        return sprintf($text, $param1, $param2, $param3);
    }

}