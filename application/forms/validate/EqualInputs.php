<?php

/**
 * Default_Form_Validate_EqualInputs
 * 
 * Validate - checks match for two fields.
 *
 *
 * @uses       Zend_Validate_Abstract
 * @package    Module-Default
 * @subpackage Forms.Validates
 * @author   Sergii Beskorovainyi <bsa2657@yandex.ru>
 * @license  MIT <http://www.opensource.org/licenses/mit-license.php>
 * @link     https://github.com/bsa-git/zf-myblog/
 */
class Default_Form_Validate_EqualInputs extends Zend_Validate_Abstract {
    
    /**
     * Error tag
     * @var const 
     */
    const NOT_EQUAL = 'stringsNotEqual';
    
    /**
     * Error text
     * @var array 
     */
    protected $_messageTemplates = array(
        self::NOT_EQUAL => 'Строки не равны'
    );
    
    /**
     * Field name, with which compare
     * @var string 
     */
    protected $_contextKey;
    
    /**
     * Constructor
     *
     * @param string $key 
     */
    public function __construct($key) {
        $this->_contextKey = $key;
    }
    
    
    /**
     * 
     * Compare fields
     * 
     * Compare $value with $context[ $this->_contextKey ]
     * 
     * @param string $value
     * @param string $context
     * @return bool 
     */
    public function isValid($value, $context = null) {
        
        $value = (string) $value;

        if (is_array($context)) {
            if (isset($context[$this->_contextKey]) && ($value === $context[$this->_contextKey])) {
                return true;
            }
        }
        else if (is_string($context) && ($value === $context))  {
            return true;
        }
    
        $this->_error(self::NOT_EQUAL);
        
        return false;
    }
}