<?php

/**
 * Default_Model_DatabaseObject
 * 
 * Abstract class used to easily manipulate data in a database table
 * via simple load/save/delete methods
 *
 *
 * @uses       Zend_Db_Table_Abstract
 * @package    Module-Default
 * @subpackage Models
 */
abstract class Default_Model_DatabaseObject extends Zend_Db_Table_Abstract {

    const TYPE_TIMESTAMP = 1;
    const TYPE_BOOLEAN = 2;
    const TYPE_HTML = 3;

    /**
     * _serializer - обьект Zend_Serializer
     *
     * @var object
     */
    protected $_serializer = null;

    /**
     * _logMsg - обьект Zend_Log
     *
     * @var object
     */
    protected $_logMsg = null;

    /**
     * _logStat - обьект Zend_Log
     *
     * @var object
     */
    protected $_logStat = null;

    /**
     * _logEx - обьект Zend_Log
     *
     * @var object
     */
    protected $_logEx = null;
    protected static $types = array(self::TYPE_TIMESTAMP, self::TYPE_BOOLEAN, self::TYPE_HTML);
    private $_id = null;
    private $_properties = array();
    protected $_db = null;
    protected $_table = '';
    protected $_idField = '';
    protected $_request = null;
    public $sortColumn = '';
    public $ascDescFlg = false;


    /**
     * Конструктор класса
     *
     * @param array $config
     */
    public function __construct(array $config = array()) {
        parent::__construct($config);
        $this->_db = $config['db'];
        $this->_table = $config['name'];
        $this->_idField = $config['primary'];
        $front = Zend_Controller_Front::getInstance();
        $this->_request = $front->getRequest();
        $this->_config = Zend_Registry::get('config');

        // Получим обьект сериализатора
        $this->_serializer = Zend_Serializer::factory('PhpSerialize');

        // Получим обьекты для логирования
        $this->_logMsg = Zend_Registry::get('Zend_Log');
        $this->_logStat = Zend_Registry::get('Zend_LogStat');
        $this->_logEx = Zend_Registry::get('Zend_LogEx');
    }

    /**
     * Загрузить из таблицы записи
     *
     *
     * @param int $id        Значение поля
     * @param string $field     Название поля
     * @return bool
     */
    public function load($id, $field = null) {
        if (strlen($field) == 0) {
            $field = $this->_idField;
        }
        if ($field == $this->_idField) {
            $id = (int) $id;
            if ($id <= 0)
                return false;
        }
        $query = sprintf('select %s from %s where %s = ?', join(', ', $this->getSelectFields()), $this->_table, $field);
        $query = $this->_db->quoteInto($query, $id);
        return $this->_load($query);
    }

    /**
     * Получим массив полей таблицы
     * с добавленным префиксом перед названием поля
     *
     * @param string $prefix
     * @return array
     */
    public function getSelectFields($prefix = '') {
        $fields = array($prefix . $this->_idField);
        foreach ($this->_properties as $k => $v)
            $fields[] = $prefix . $k;
        return $fields;
    }

    /**
     * Выполнить запрос к базе данных
     *
     * @param string $query
     * @return bool
     */
    protected function _load($query) {
        $result = $this->_db->query($query);
        $row = $result->fetch();
        if (!$row)
            return false;
        $this->_init($row);
        $this->postLoad();
        return true;
    }

    /**
     *
     * Инициализация отдельных полей
     * полученной записи
     *
     * @param array $row
     * @return void
     */
    public function _init($row) {
        $initRow = array();
        foreach ($this->_properties as $k => $v) {
            $val = $row[$k];
            switch ($v['type']) {
                case self::TYPE_TIMESTAMP:
                    if (!is_null($val))
                        $val = strtotime($val);
                    break;
                case self::TYPE_BOOLEAN:
                    $val = (bool) $val;
                    break;
                case self::TYPE_HTML:
                    $filterContent = new Default_Form_Filter_AddBasePathUrl();
                    $content = $filterContent->filter($val);
                    $val = $content;

                    break;
            }
            // в базе данных символы " и ' храняться в виде \" и \'
            // заменим их на (\" -> ") и (\'->')
            if (is_string($val)) {
                $val = str_replace('\"', '"', $val);
                $val = str_replace("\'", "'", $val);
            }

            $this->_properties[$k]['value'] = $val;
            $initRow[$k] = $val;
        }
        $this->_id = (int) $row[$this->_idField];
        $initRow[$this->_idField] = (int) $row[$this->_idField];
        return $initRow;
    }

    /**
     * Операция сохранения записи в базе данных
     * Если запись отсутсвует, то она будет дабавлена
     * Если запись присутствует, то она будет изменена
     *
     * @param bool $useTransactions
     * @return bool
     */
    public function save($useTransactions = true) {
        $update = $this->isSaved();
        try {
            if ($useTransactions)
                $this->_db->beginTransaction();
            if ($update) {
                $commit = $this->preUpdate();
            } else {
                $commit = $this->preInsert();
            }
            if (!$commit) {
                if ($useTransactions)
                    $this->_db->rollback();
                return false;
            }
            $row = array();
            foreach ($this->_properties as $k => $v) {
                if ($update && !$v['updated'])
                    continue;
                switch ($v['type']) {
                    case self::TYPE_TIMESTAMP:
                        if (!is_null($v['value'])) {
                            if ($this->_db instanceof Zend_Db_Adapter_Pdo_Pgsql)
                                $v['value'] = date('Y-m-d H:i:sO', $v['value']);
                            else
                                $v['value'] = date('Y-m-d H:i:s', $v['value']);
                        }
                        break;
                    case self::TYPE_BOOLEAN:
                        $v['value'] = (int) ((bool) $v['value']);
                        break;
                    case self::TYPE_HTML:
                        $filterContent = new Default_Form_Filter_DeleteBasePathUrl();
                        $content = $filterContent->filter($v['value']);
                        if ($content === FALSE) {
                            return FALSE;
                        } else {
                            $v['value'] = $content;
                        }
                        break;
                }
                $row[$k] = $v['value'];
            }
            if (count($row) > 0) {
                // perform insert/update
                if ($update) {
                    $commit = (bool) $this->_db->update($this->_table, $row, sprintf('%s = %d', $this->_idField, $this->getId()));
                } else {
                    $this->_db->insert($this->_table, $row);
                    $lastInsertId = $this->_db->lastInsertId($this->_table, $this->_idField);
                    $this->_id = $lastInsertId;
                    $commit = (bool) $lastInsertId;
                }
            }
            // update internal id
            if ($commit) {
                if ($update) {
                    $commit = $this->postUpdate();
                } else {
                    $commit = $this->postInsert();
                }
            }
            if ($useTransactions) {
                if ($commit)
                    $this->_db->commit();
                else
                    $this->_db->rollback();
            }
            return $commit;
        } catch (Exception $exc) {

            if ($useTransactions) {
                $this->_db->rollback();
            }

            // Запомним в логе сообщений
            if ($update) {
                $message = "Update row from the table - \"$this->_table\" with a key field - \"$this->_idField\"=" . $this->getId();
            } else {
                $message = "Insert a row to table - \"$this->_table\"";
            }
            $message .= "\n\n" . $exc->getMessage();
            $this->_logMsg->db_save_err($message);

            // Запомним в логе ошибок
            $message .= "\n\n" . $exc->getTraceAsString();
            $this->_logEx->err($message);

            return FALSE;
        }
    }

    /**
     * Удаление записи из базы данных
     *
     * @param bool $useTransactions
     * @return bool
     */
    public function delete($useTransactions = true) {
        if (!$this->isSaved())
            return false;
        try {
            if ($useTransactions) {
                $this->_db->beginTransaction();
            }
            $commit = $this->preDelete();
            if ($commit) {
                $commit = (bool) $this->_db->delete($this->_table, sprintf('%s = %d', $this->_idField, $this->getId()));
                if (!$commit) {
                    if ($useTransactions) {
                        $this->_db->rollback();
                    }
                    return false;
                }
            } else {
                if ($useTransactions) {
                    $this->_db->rollback();
                }
                return false;
            }
            $commit = $this->postDelete();
            if ($commit) {
                if ($useTransactions) {
                    $this->_db->commit();
                }
                $this->_id = null;
            } else {
                if ($useTransactions) {
                    $this->_db->rollback();
                }
            }
            return $commit;
        } catch (Exception $exc) {

            if ($useTransactions) {
                $this->_db->rollback();
            }

            // Запомним в логе сообщений
            $message = "Error deleting row from the database! Delete row from the table - \"$this->_table\" with a key field - \"$this->_idField\"=" . $this->getId();
            $message .= "\n\n" . $exc->getMessage();
            $this->_logMsg->db_delete_err($message);

            // Запомним в логе ошибок
            $message .= "\n\n" . $exc->getTraceAsString();
            $this->_logEx->err($message);

            return FALSE;
        }
    }

    /**
     * Определим записана ли запись
     *
     * @return bool
     */
    public function isSaved() {
        return $this->getId() > 0;
    }

    /**
     * Получить значение _id
     *
     * @return int
     */
    public function getId() {
        return (int) $this->_id;
    }

    /**
     * Получить диспетчер базы данных
     *
     * @return Zend_Db_Adapter_Abstract
     */
    public function getDb() {
        return $this->_db;
    }

    /**
     * Установить значение поля записи таблицы
     *
     * @param string $name
     * @param string $value
     * @return bool
     */
    public function __set($name, $value) {
        if (array_key_exists($name, $this->_properties)) {
            $this->_properties[$name]['value'] = $value;
            $this->_properties[$name]['updated'] = true;
            return true;
        }
        return false;
    }

    /**
     * Получить значение поля записи таблицы
     *
     * @param string $name
     * @return string
     */
    public function __get($name) {
        return array_key_exists($name, $this->_properties) ? $this->_properties[$name]['value'] : null;
    }

    /**
     * Добавить или изменить значение поля записи таблицы
     *
     * @param string $field
     * @param string $default
     * @param int $type
     */
    protected function add($field, $default = null, $type = null) {
        $this->_properties[$field] = array('value' => $default,
            'type' => in_array($type, self::$types) ? $type : null,
            'updated' => false);
    }

    protected function preInsert() {
        return true;
    }

    protected function postInsert() {
        return true;
    }

    protected function preUpdate() {
        return true;
    }

    protected function postUpdate() {
        return true;
    }

    protected function preDelete() {
        return true;
    }

    protected function postDelete() {
        return true;
    }

    protected function postLoad() {
        return true;
    }

    //------------- РАБОТА С ОБЬЕКТАМИ ТАБЛИЦЫ ------------//

    /**
     * Получить массив обьектов записей таблицы
     *
     * @param Zend_Db_Adapter_Abstract $db
     * @param Default_Model_DatabaseObject $class     Класс для раб. с таблицей
     * @param array $data                             Массив записей разных таблиц
     * @return array
     */
    public static function BuildMultiple($db, $class, $data) {
        $ret = array();
        if (!class_exists($class))
            throw new Exception('Undefined class specified: ' . $class);
        $testObj = new $class($db);
        //if (! $testObj instanceof DatabaseObject)
        if (!$testObj instanceof Default_Model_DatabaseObject)
            throw new Exception('Class does not extend from DatabaseObject');
        foreach ($data as $row) {
            $obj = new $class($db);
            $obj->_init($row);
            $ret[$obj->getId()] = $obj;
        }
        return $ret;
    }

    /**
     * Получить массив значений записей таблицы
     *
     * @param Zend_Db_Adapter_Abstract $db
     * @param Default_Model_DatabaseObject $class     Класс для раб. с таблицей
     * @param array $data                             Массив записей разных таблиц
     * @param string $nameTable                       Название таблицы (если один класс используется для разных таблиц)
     * 
     * @return array
     */
    public static function BuildMultiple_Array($db, $class, $data, $nameTable = NULL) {
        $ret = array();
        if (!class_exists($class))
            throw new Exception('Undefined class specified: ' . $class);
        if ($nameTable) {
            $testObj = new $class($db, $nameTable);
        } else {
            $testObj = new $class($db);
        }

        //if (! $testObj instanceof DatabaseObject)
        if (!$testObj instanceof Default_Model_DatabaseObject)
            throw new Exception('Class does not extend from DatabaseObject');
        foreach ($data as $row) {
            if ($nameTable) {
                $obj = new $class($db, $nameTable);
            } else {
                $obj = new $class($db);
            }
            $initRow = $obj->_init($row);
            $ret[$obj->getId()] = $initRow;
        }
        return $ret;
    }

    //------------- СОРТИРОВКА ------------//

    /**
     * Получить отсортированный массив
     * в соответствии с параметрами сортировки
     * в параметрах указано поле сортировки и направление сортировки
     *
     * @param array $rows
     * @param array $options // где $options = array('sortColumn' => sortColumn, 'ascDescFlg' => ascDescFlg)
     * @return array
     */
    public static function GetSortProfiles_Arrays($rows, $options) {

        $sortRows = array();

        // Добавим поля к массиву с признаками желаемой сортировки
        foreach ($rows as $row) {
            $sortRows[] = $row + $options;
        }

        // Отсортируем массив
        usort($sortRows, array("Default_Model_DatabaseObject", "_SortProfiles_Arrays"));


        return $sortRows;
    }

    /**
     * This is the static comparing function
     *
     * @param string $a
     * @param string $b
     * @return int          // результат сортировки
     */
    static function _SortProfiles($a, $b) {
        $sortColumn = $a->sortColumn;
        $ascDescFlg = $a->ascDescFlg;

        $al = mb_strtolower($a->profile->$sortColumn);
        $bl = mb_strtolower($b->profile->$sortColumn);
        if ($al == $bl) {
            return 0;
        }
        if ($ascDescFlg == 'DESC') {
            return ($al < $bl) ? +1 : -1;
        } else {
            return ($al < $bl) ? -1 : +1;
        }
    }

    /**
     * This is the static comparing function
     *
     * @param string $a
     * @param string $b
     * @return int          // результат сортировки
     */
    static function _SortProfiles_Arrays($a, $b) {
        $sortColumn = $a['sortColumn'];
        $ascDescFlg = $a['ascDescFlg'];

        $al = mb_strtolower($a[$sortColumn]);
        $bl = mb_strtolower($b[$sortColumn]);
        if ($al == $bl) {
            return 0;
        }
        if ($ascDescFlg == 'DESC') {
            return ($al < $bl) ? +1 : -1;
        } else {
            return ($al < $bl) ? -1 : +1;
        }
    }

    /**
     * Ф-ия сортировки массивов
     *
     * @param array $a
     * @param array $b
     * @return int          // результат сортировки
     */
    static function _SortArrays($a, $b) {
        return strcmp($a["text"], $b["text"]);
    }

    /**
     * Ф-ия сортировки массивов
     *
     * @param array $a
     * @param array $b
     * @return int          // результат сортировки
     */
    static function _SortArrays2($a, $b) {
        return strcmp($a["value"], $b["value"]);
    }

    //------------- ДОПОЛНИТЕЛЬНЫЕ Ф-ИИ ------------//

    /**
     * Сделать перевод текста
     *
     *
     * @return string
     */
    public function Translate($aText, $param1 = NULL, $param2 = NULL, $param3 = NULL) {
        $text = Zend_Registry::get('Zend_Translate')->_($aText);
        return sprintf($text, $param1, $param2, $param3);
    }

    /**
     * Получить превдоним для таблицы в запросе к базе данных
     *
     * @param Zend_Db_Select $select                 Обьект базы даннх Select
     * @param string $table
     *
     * @return string
     */
    static function getAliasForTable($select, $table) {
        $aliasTable = '';
        //---------------
        $fromTables = $select->getPart(Zend_Db_Select::FROM);
        foreach ($fromTables as $alias => $joinParams) {
            // Если таблица -> $joinTableForSort уже присоединена
            // то получим ее псевдоним
            if ($joinParams['tableName'] == $table) {
                $aliasTable = $alias;
            }
        }
        return $aliasTable;
    }

}

?>