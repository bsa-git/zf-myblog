<?php

/**
 * Admin_Form_Info
 * 
 * Form - info
 * validation information help for site
 *
 *
 * @uses       Admin_Form_MyForm
 * @package    Module-Admin
 * @subpackage Forms
 * @author   Sergii Beskorovainyi <bsa2657@yandex.ru>
 * @license  MIT <http://www.opensource.org/licenses/mit-license.php>
 * @link     https://github.com/bsa-git/zf-myblog/
 */
class Admin_Form_Info extends Default_Form_MyForm {

    /**
     * Initialization form
     */
    public function init() {
        // Вызываем родительский метод
        parent::init();


        //============ Форма ==============
        // Признак активности сообщения
        $actual = new Zend_Form_Element_Select('actual', array(
                    'label' => 'Актуальный',
                    'description' => 'Актуальность сообщения позволяет пользователям видеть это сообщение на сайте',
                    'multiOptions' => array('Да', 'Нет'),
                    'filters' => array('StringTrim'),
                ));

        $this->addElement($actual);

        // title_info Text элемент "Название информации". Проверяется на алфавитные символы и цифры, а также на длину
        // Валидатор Alnum использует установленную локаль для определения алфавита символов.
        $title_info = new Zend_Form_Element_Text('title_info', array(
                    'required' => FALSE,
                    'label' => $this->Translate('Название информации'),
                    'maxlength' => '255',
                    'validators' => array(
                        array('Alnum', true, array(true)),
                        array('NotEmpty'),
                        array('StringLength', true, array(0, 255)),
                        array('NoDbRecordExists', true, array('blog_info', 'title_info')),
                    ),
                    'filters' => array('StringTrim'),
                ));

        $this->addElement($title_info);

        // title_info Text элемент "Уникальный ключ для инф. помощи". Проверяется на алфавитные символы и цифры, а также на длину
        // Валидатор Alnum использует установленную локаль для определения алфавита символов.
        $info_key = new Zend_Form_Element_Text('info_key', array(
                    'required' => FALSE,
                    'label' => $this->Translate('Уникальный ключ для инф. помощи'),
                    'maxlength' => '255',
                    'validators' => array(
                        array('NotEmpty'),
                        array('StringLength', true, array(0, 255)),
                        array('NoDbRecordExists', true, array('blog_info', 'info_key')),
                    ),
                    'filters' => array('StringTrim'),
                ));
        $info_key->addValidator(new Zend_Validate_Regex(array('pattern' => '/^[a-z0-9-]+$/')));
        $this->addElement($info_key);
        
    }

}