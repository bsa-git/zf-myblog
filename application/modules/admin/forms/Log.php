<?php

/**
 * Admin_Form_Log
 * 
 * Форма проверки данных логирования
 *
 *
 * @uses       Admin_Form_MyForm
 * @package    Module-Admin
 * @subpackage Forms
 */
class Admin_Form_Log extends Default_Form_MyForm {

    /**
     * Создание формы
     */
    public function init() {
        // Вызываем родительский метод
        parent::init();


        //============ Форма ==============
        //Дата создания записи
        $ts = new Zend_Form_Element_Text('ts', array(
                    'label' => 'Дата записи',
                    'maxlength' => '10',
                    'required' => FALSE,
                    'validators' => array(array('Date', true, array('format' => 'yyyy-MM-dd HH:mm:ss'))),
                    'filters' => array('StringTrim'),
                ));
        $this->addElement($ts);

        // Сообщение лога
        // Валидатор Alnum использует установленную локаль для определения алфавита символов.
        $msg = new Zend_Form_Element_Text('msg', array(
                    'label' => 'Сообщение лога',
                    'maxlength' => '1024',
                    'validators' => array(
                        array('Alnum', true, array(true)),
                        array('NotEmpty'),
                        array('StringLength', true, array(0, 1024)),
                    ),
                    'filters' => array('StringTrim'),
                ));

        $this->addElement($msg);
        
        // Номер приоритета
        $pr = new Zend_Form_Element_Text('pr', array(
                    'label' => 'Номер приоритета',
                    'maxlength' => '10',
                    'size' => '10',
                    'validators' => array(
                        array('Int'),
                        array('StringLength', true, array(0, 10))
                    ),
                    'filters' => array('StringTrim'),
                ));
        $this->addElement($pr);

        // Название приоритета
         $pr_name = new Zend_Form_Element_Text('pr_name', array(
                    'label' => 'Название приоритета',
                    'maxlength' => '20',
                    'validators' => array(
                        array('Alpha', true, array(false)),
                        array('NotEmpty'),
                        array('StringLength', true, array(0, 20)),
                    ),
                    'filters' => array('StringTrim'),
                ));
        $info_key->addValidator(new Zend_Validate_Regex(array('pattern' => '/^[a-zA-Z0-9-]+$/')));
        $this->addElement($pr_name);
    }

}