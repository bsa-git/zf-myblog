<?php

/**
 * Default_Form_Validate_UserType
 * 
 * Проверка типа пользователя (роли)
 * 
 *
 *
 * @uses       Zend_Validate_Abstract
 * @package    Module-Default
 * @subpackage Forms.Validates
 */
class Default_Form_Validate_UserType extends Zend_Validate_Abstract {
    /**
     * Метка ошибки
     * @var const 
     */
    const INVALID = 'usertypeInvalid';


    /**
     * Текст ошибки
     * @var array 
     */
    protected $_messageTemplates = array(
        self::INVALID => 'Неверное значение роли пользователя',
    );

    /**
     * Проверка пароля
     * 
     * @param string $value значение которое поддается валидации
     */
    public function isValid($value) {
        // Благодаря этому методу значение будет автоматически подставлено в текст ошибки при необходимости
        $this->_setValue($value);

        $roles = Default_Plugin_AclManager::$roles;
        // Проверка значения
        if (!in_array($value, array_values($roles))) {
            // С помощью этого метода мы указываем какая именно ошибка произошла
            $this->_error(self::INVALID);
            return false;
        }

        return true;
    }

}

