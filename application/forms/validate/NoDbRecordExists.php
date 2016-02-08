<?php

/**
 * Default_Form_Validate_NoDbRecordExists
 * 
 * Проверка отсутствия записи в таблице
 *
 *
 * @uses       Zend_Validate_Abstract
 * @package    Module-Default
 * @subpackage Forms.Validates
 */
class Default_Form_Validate_NoDbRecordExists extends Zend_Validate_Abstract
{

    /**
     * Метка ошибки
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
     * Имя таблица в которой будет происходить поиск записи
     * @var string
     */    
    protected $_table = null;    
    
    /**
     * Имя поля по которому будет происходить поиск значения 
     * @var string
     */    
    protected $_field = null;    

    /**
     * Используемый адаптер базы данных
     *
     * @var unknown_type
     */    
    protected $_adapter = null;    
       
    /**
     * Конструктор
     * 
     * @param string $table Имя таблицы
     * @param string $field Имя поля
     * @param Zend_Db_Adapter_Abstract $adapter Адаптер базы данных
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
     * Проверка
     * 
     * @param string $value значение которое поддается валидации
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

