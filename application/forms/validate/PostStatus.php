<?php

/**
 * Default_Form_Validate_PostStatus
 * 
 * Validate - post status
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
class Default_Form_Validate_PostStatus extends Zend_Validate_Abstract {
    /**
     * Error tag
     * @var const 
     */
    const INVALID = 'poststatusInvalid';


    /**
     * Error text
     * @var array 
     */
    protected $_messageTemplates = array(
        self::INVALID => 'Неверное значение статуса сообщения',
    );

    /**
     * Validate
     * 
     * @param string $value
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

