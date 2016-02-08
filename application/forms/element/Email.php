<?php

/**
 * Default_Form_Element_Email
 * 
 * Декоратор отображения javascript календаря
 *
 *
 * @uses       Zend_Form_Element_Text
 * @package    Module-Default
 * @subpackage Forms.Elements
 */
class Default_Form_Element_Email extends Zend_Form_Element_Text
{
    /**
     * Инициализация элемента
     * 
     * return void
     */  
    public function init()
    {
        //$this->setLabel(Zend_Registry::get('Zend_Translate')->_('Электронная почта').':');
        $this->setLabel('Электронная почта');
        $this->setAttrib('maxlength', 150);
        $this->setAttrib('size', 60);
        $this->addValidator('EmailAddress', true);
//        $this->addValidator('NoDbRecordExists', true, array('users', 'email'));
        $this->addFilter('StringTrim');
    }
}