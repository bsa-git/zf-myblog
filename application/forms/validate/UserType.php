<?php

/**
 * Default_Form_Validate_UserType
 * 
 * Validate user type (role)
 * 
 *
 *
 * @uses       Zend_Validate_Abstract
 * @package    Module-Default
 * @subpackage Forms.Validates
 * @author   Sergii Beskorovainyi <bsa2657@yandex.ru>
 * @license  MIT <http://www.opensource.org/licenses/mit-license.php>
 * @link     https://github.com/bsa-git/zf-myblog/
 */
class Default_Form_Validate_UserType extends Zend_Validate_Abstract {
    /**
     * Error tag
     * @var const 
     */
    const INVALID = 'usertypeInvalid';


    /**
     * Error text
     * @var array 
     */
    protected $_messageTemplates = array(
        self::INVALID => 'Неверное значение роли пользователя',
    );

    /**
     * Validate
     * 
     * @param string $value
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

