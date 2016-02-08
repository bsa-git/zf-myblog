<?php

/**
 * Default_Form_Validate_UserName
 * 
 * Проверка имени пользователя
 * проверка на кол. символов и состав символов
 *
 *
 * @uses       Zend_Validate_Abstract
 * @package    Module-Default
 * @subpackage Forms.Validates
 */
class Default_Form_Validate_UserName extends Zend_Validate_Abstract
{
    /**
     * Метка ошибки
     * @var const 
     */    
    const INVALID = 'passwordInvalid';

    /**
     * Метка ошибки
     * @var const 
     */    
    const INVALID_LENGTH = 'passwordBadLength';    
    
    /**
     * Текст ошибки
     * @var array 
     */
    protected $_messageTemplates = array(
        self::INVALID => 'Введены не допустимые символы',
        self::INVALID_LENGTH => 'Введено неверное количество символов. Кол. символов должно быть больше 4 и меньше 30'
    );

    /**
     * Проверка пароля
     * 
     * @param string $value значение которое поддается валидации
     */
    public function isValid($value) 
    {
        // Благодаря этому методу значение будет автоматически подставлено в текст ошибки при необходимости
        $this->_setValue($value);
        
        // Валидатор проверки длины
        $validatorStringLength = new Zend_Validate_StringLength(5, 30);
        
        // Проверка на допустимые символы
        if (!preg_match("/^[~!@#\\$%\\^&\\*\\(\\)\\-_\\+=\\\\\/\\{\\}\\[\\].,\\?<>:;a-z0-9]*$/i", $value)) {
            // С помощью этого метода мы указываем какая именно ошибка произошла
            $this->_error(self::INVALID);
            return false;            
        }
        elseif (!$validatorStringLength->isValid($value)) {
            $this->_error(self::INVALID_LENGTH);
            return false;            
        }

        return true;
    }
}

