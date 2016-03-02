<?php

/**
 * Default_Form_Validate_Password
 * 
 * Validate - password
 *
 *
 * @uses       Zend_Validate_Abstract
 * @package    Module-Default
 * @subpackage Forms.Validates
 * @author   Sergii Beskorovainyi <bsa2657@yandex.ru>
 * @license  MIT <http://www.opensource.org/licenses/mit-license.php>
 * @link     https://github.com/bsa-git/zf-myblog/
 */
class Default_Form_Validate_Password extends Zend_Validate_Abstract
{
    /**
     * Error tag
     * @var const 
     */    
    const INVALID = 'passwordInvalid';

    /**
     * Error tag
     * @var const 
     */    
    const INVALID_LENGTH = 'passwordBadLength';    
    
    /**
     * Error text
     * @var array 
     */
    protected $_messageTemplates = array(
        self::INVALID => 'Введены не допустимые символы',
        self::INVALID_LENGTH => 'Введено неверное количество символов. Кол. символов должно быть больше 4 и меньше 30'
    );

    /**
     * Validate
     * 
     * @param string $value
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

