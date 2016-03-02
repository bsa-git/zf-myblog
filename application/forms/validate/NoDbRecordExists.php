<?php

/**
 * Default_Form_Validate_NoDbRecordExists
 * 
 * Validate - check absence for entries in the table
 *
 *
 * @uses       Zend_Validate_Abstract
 * @package    Module-Default
 * @subpackage Forms.Validates
 * @author   Sergii Beskorovainyi <bsa2657@yandex.ru>
 * @license  MIT <http://www.opensource.org/licenses/mit-license.php>
 * @link     https://github.com/bsa-git/zf-myblog/
 */
class Default_Form_Validate_NoDbRecordExists extends Zend_Validate_Abstract
{

    /**
     * Error tag
     * @var const 
     */    
    const RECORD_EXISTS = 'dbRecordExists';
    
    /**
     * Текст ошибки
     * @var array 
     */
    protected $_messageTemplates = array(
        self::RECORD_EXISTS => 'Запись со значением %value% уже существует в таблице'
    );

    /**
     * The table name in which will be searched record
     * @var string
     */    
    protected $_table = null;    
    
    /**
     * The field name for which will be searched for the value 
     * @var string
     */    
    protected $_field = null;    

    /**
     * Database adapter
     *
     * @var unknown_type
     */    
    protected $_adapter = null;    
       
    /**
     * Constructor
     * 
     * @param string $table
     * @param string $field
     * @param Zend_Db_Adapter_Abstract $adapter
     */
    public function __construct($table, $field, Zend_Db_Adapter_Abstract $adapter = null)
    {
        $this->_table = $table;
        $this->_field = $field;
        
        if ($adapter == null) {
        	// Если адаптер не задан, пробуем подключить адаптер заданный по умолчанию для Zend_Db_Table
        	$adapter = Zend_Db_Table::getDefaultAdapter();
        	
        	// Если адаптер по умолчанию не задан выбрасываем исключение
        	if ($adapter == null) {
        	   throw new Exception('Адаптер базы данных, заданный по умолчанию не был найден');
        	}
        }
        
        $this->_adapter = $adapter;
    }
    
    /**
     * Validate
     * 
     * @param string $value
     */
    public function isValid($value) 
    {
        $this->_setValue($value);
        
        $adapter = $this->_adapter;
        
        $select = $adapter->select()
            ->from($this->_table)
            ->where($adapter->quoteIdentifier($this->_field) . ' = ?', $value)
            ->limit(1)
            ;
        $stmt = $adapter->query($select);
        $result = $stmt->fetch(Zend_Db::FETCH_ASSOC);
        
        if ($result !== false) {
            $messageKey = self::RECORD_EXISTS;
            $value = $this->_messageTemplates[$messageKey];
            $this->_error($messageKey);
            return false;
        }
        
        return true;

    }

}

