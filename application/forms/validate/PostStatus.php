<?php

/**
 * Default_Form_Validate_PostStatus
 * 
 * Проверка статуса сообщения
 * 
 *
 *
 * @uses       Zend_Validate_Abstract
 * @package    Module-Default
 * @subpackage Forms.Validates
 */
class Default_Form_Validate_PostStatus extends Zend_Validate_Abstract {
    /**
     * Метка ошибки
     * @var const 
     */
    const INVALID = 'poststatusInvalid';


    /**
     * Текст ошибки
     * @var array 
     */
    protected $_messageTemplates = array(
        self::INVALID => 'Неверное значение статуса сообщения',
    );

    /**
     * Проверка значения
     * 
     * @param string $value значение которое поддается валидации
     */
    public function isValid($value) {
        // Благодаря этому методу значение будет автоматически подставлено в текст ошибки при необходимости
        $this->_setValue($value);

        // Проверка значения
        if (Default_Model_DbTable_BlogPost::STATUS_DRAFT == $value || Default_Model_DbTable_BlogPost::STATUS_LIVE == $value ) {
            return true;
        }  else {
            // С помощью этого метода мы указываем какая именно ошибка произошла
            $this->_error(self::INVALID);
            return false;
        }

        
    }

}

