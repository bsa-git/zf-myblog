<?php

/**
 * Default_Form_Validate_DbMultipleKey
 * 
 * Проверка отсутствия сложного ключа в таблице
 *
 *
 * @uses       Zend_Validate_Abstract
 * @package    Module-Default
 * @subpackage Forms.Validates
 */
class Default_Form_Validate_DbMultipleKey extends Zend_Validate_Abstract {
    /**
     * Метка ошибки
     * @var const 
     */

    const RECORD_EXISTS = 'dbMultipleKeyExists';

    /**
     * Текст ошибки
     * @var array 
     */
    protected $_messageTemplates = array(
        self::RECORD_EXISTS => "Запись с составным ключом '%value%' уже существует в таблице"
    );

    /**
     * Схема таблицы в которой будет происходить поиск записи
     * @var string
     */
    protected $_schema = '';

    /**
     * Имя таблица в которой будет происходить поиск записи
     * @var string
     */
    protected $_table = null;

    /**
     * Имя поля составного ключа1 по которому будет происходить поиск значения 
     * @var string
     */
    protected $_fieldKey1 = null;

    /**
     * Имя поля составного ключа2 по которому будет происходить поиск значения 
     * @var string
     */
    protected $_fieldKey2 = null;

    /**
     * Используемый адаптер базы данных
     *
     * @var unknown_type
     */
    protected $_adapter = null;

    /**
     * Уникальный ключ записи
     *
     * @var int
     */
    protected $_id = 0;

    /**
     * Значение уникального ключа1 записи
     *
     * @var int
     */
    protected $_valueKey1 = null;

    /**
     * Значение уникального ключа2 записи
     *
     * @var int
     */
    protected $_valueKey2 = null;

    /**
     * Конструктор
     * 
     * @param array $params  Массив с параметрами
     */
//    public function __construct($table, $field, Zend_Db_Adapter_Abstract $adapter = null)
    public function __construct($params) {
        $this->_schema = $params['schema'] ? $params['schema'] : '';
        $this->_table = $params['table'];
        $this->_fieldKey1 = $params['fieldKey1'];
        $this->_fieldKey2 = $params['fieldKey2'];
        $this->_id = (int) $params['id'] ? $params['id'] : 0;

        if (isset($params['adapter'])) {
            $this->_adapter = $params['adapter'];
        } else {
            // Если адаптер не задан, пробуем подключить адаптер заданный по умолчанию для Zend_Db_Table
            $adapter = Zend_Db_Table::getDefaultAdapter();

            // Если адаптер по умолчанию не задан выбрасываем исключение
            if ($adapter == null) {
                throw new Exception(Default_Plugin_SysBox::Translate('Адаптер базы данных, заданный по умолчанию не был найден'));
            }
        }
    }

    /**
     * Проверка
     * 
     * @param string $value значение которое поддается валидации
     */
    public function isValid($value) {

        // Установим значения
        $this->_setValue($value);
        list($this->_valueKey1, $this->_valueKey2) = explode('[;]', $value);

        $adapter = $this->_adapter;

        
        if ($this->_id) {
            $clause = $adapter->quoteInto($adapter->quoteIdentifier($this->_fieldKey2) . ' = ?', $this->_valueKey2) . ' AND ' .
                $adapter->quoteInto($adapter->quoteIdentifier('id') . ' <> ?', $this->_id);
        } else {
            $clause = $adapter->quoteInto($adapter->quoteIdentifier($this->_fieldKey2) . ' = ?', $this->_valueKey2);
        }

        $validator = new Zend_Validate_Db_NoRecordExists(
                        array(
                            'table' => $this->_table,
                            'field' => $this->_fieldKey1,
                            'exclude' => $clause,
                            'adapter' => $adapter,
//                            'schema' => $this->_schema
                        )
        );

        if ($validator->isValid($this->_valueKey1)) {
            // Составной ключ не существует в таблице
            return true;
        } else { // Ошибка валидации (нарушение уникальности), в таблице уже есть такой составной ключ
            $messageKey = self::RECORD_EXISTS;
            $value = $this->_messageTemplates[$messageKey];
            $this->_error($messageKey);
            return false;
        }

//        $select = $adapter->select()
//                ->from($this->_table)
//                ->where($adapter->quoteIdentifier($this->_field) . ' = ?', $value)
//                ->limit(1)
//        ;
//        $stmt = $adapter->query($select);
//        $result = $stmt->fetch(Zend_Db::FETCH_ASSOC);
//
//        if ($result !== false) {
//            $messageKey = self::RECORD_EXISTS;
//            $value = $this->_messageTemplates[$messageKey];
//            $this->_error($messageKey);
//            return false;
//        }

        
    }

}

