<?php

/**
 * Default_Form_Element_Email
 * 
 * Element - Email
 *
 *
 * @uses       Zend_Form_Element_Text
 * @package    Module-Default
 * @subpackage Forms.Elements
 * @author   Sergii Beskorovainyi <bsa2657@yandex.ru>
 * @license  MIT <http://www.opensource.org/licenses/mit-license.php>
 * @link     https://github.com/bsa-git/zf-myblog/
 */
class Default_Form_Element_Email extends Zend_Form_Element_Text
{
    /**
     * Initialization element
     * 
     * return void
     */  
    public function init()
    {
        $this->setLabel('Электронная почта');
        $this->setAttrib('maxlength', 150);
        $this->setAttrib('size', 60);
        $this->addValidator('EmailAddress', true);
        $this->addFilter('StringTrim');
    }
}