<?php

/**
 * Default_Form_Validate_DbMultipleKey
 * 
 * Validate - verify the absence of a key complex in the table
 *
 *
 * @uses       Zend_Validate_Abstract
 * @package    Module-Default
 * @subpackage Forms.Validates
 * @author   Sergii Beskorovainyi <bsa2657@yandex.ru>
 * @license  MIT <http://www.opensource.org/licenses/mit-license.php>
 * @link     https://github.com/bsa-git/zf-myblog/
 */
class Default_Form_Validate_DbMultipleKey extends Zend_Validate_Abstract {
    /**
     * Error tag
     * @var const 
     */
    const RECORD_EXISTS = 'dbMultipleKeyExists';

    /**
     * Error text
     * @var array 
     */
    protected $_messageTemplates = array(
        self::RECORD_EXISTS => "Запись с составным ключом '%value%' уже существует в таблице"
    );

    /**
     * The table scheme in which will be searched record
     * @var string
     */
    protected $_schema = '';

    /**
     * The table name in which will be searched record
     * @var string
     */
    protected $_table = null;

    /**
     * Name field composite key1 on which the search will take place values
     * @var string
     */
    protected $_fieldKey1 = null;

    /**
     * Name field composite key2 on which the search will take place values
     * @var string
     */
    protected $_fieldKey2 = null;

    /**
     * Database adapter
     *
     * @var unknown_type
     */
    protected $_adapter = null;

    /**
     * Key ID
     *
     * @var int
     */
    protected $_id = 0;

    /**
     * The value of the unique key1 record
     *
     * @var int
     */
    protected $_valueKey1 = null;

    /**
     * The value of the unique key2 record
     *
     * @var int
     */
    protected $_valueKey2 = null;

    /**
     * Constructor
     * 
     * @param array $params
     */
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
     * Validate
     * 
     * @param string $value
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
                            'adapter' => $adapter
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
    }

}

