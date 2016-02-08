<?php

/**
 * Default_Model_Profile
 * 
 * Абстрактный класс таблицы, где 
 * хранится информация об обьекте
 *
 *
 * @uses       Zend_Db_Table_Abstract
 * @package    Module-Default
 * @subpackage Models
 */
abstract class Default_Model_Profile extends Zend_Db_Table_Abstract {

    const ACTION_NONE = 0;
    const ACTION_UPDATE = 1;
    const ACTION_INSERT = 2;
    const ACTION_DELETE = 3;

    protected $_db = null;
    protected $_table = null;
    protected $_parentField = null;
    protected $_keyField = 'profile_key';
    protected $_valueField = 'profile_value';
    protected $_filters = array();
    protected $_properties = array();

    /**
     * Конструктор класса
     * 
     * @param array $config
     * @param array $filters 
     */
    public function __construct(array $config = array(), array $filters = array()) {
        parent::__construct($config);
        $this->_db = $config['db'];
        $this->_table = $config['name'];
        $this->_parentField = $config['primary'][0];
        $this->_filters = $filters;
    }

    /**
     * Загрузить из таблицы записи
     *
     *
     * @return void
     */
    public function load() {
        $query = sprintf('select %s, %s from %s', $this->_keyField, $this->_valueField, $this->_table);
        if (count($this->_filters) > 0) {
            $filters = array();
            foreach ($this->_filters as $k => $v)
                $filters[] = $this->_db->quoteInto($k . ' = ?', $v);
            $query .= sprintf(' where %s', join(' and ', $filters));
        }
        $result = $this->_db->fetchPairs($query);
        foreach ($result as $k => $v) {
            // в базе данных символы " и ' храняться в виде \" и \'
            // заменим их на (\" -> ") и (\'->')
            if (is_string($v)) {
                $v = str_replace('\"', '"', $v);
                $v = str_replace("\'", "'", $v);
            }
            $this->_properties[$k] = array('value' => $v,
                'action' => self::ACTION_NONE);
        }
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
        if ($useTransactions)
            $this->_db->beginTransaction();
        foreach ($this->_properties as $k => $v) {
            switch ($v['action']) {
                case self::ACTION_DELETE:
                    $where = array();
                    foreach ($this->_filters as $_k => $_v)
                        $where[] = $this->_db->quoteInto($_k . ' = ?', $_v);
                    $where[] = $this->_db->quoteInto($this->_keyField . ' = ?', $k);
                    $this->_db->delete($this->_table, $where);
                    break;
                case self::ACTION_INSERT:
                    $values = $this->_filters;
                    $values[$this->_keyField] = $k;
                    $values[$this->_valueField] = $v['value'];
                    $this->_db->insert($this->_table, $values);
                    break;
                case self::ACTION_UPDATE:
                    $where = array();
                    foreach ($this->_filters as $_k => $_v)
                        $where[] = $this->_db->quoteInto($_k . ' = ?', $_v);
                    $where[] = $this->_db->quoteInto($this->_keyField . ' = ?', $k);
                    $this->_db->update($this->_table, array($this->_valueField => $v['value']), $where);
                    break;
                case self::ACTION_NONE:
                default:
                // do nothing
            }
            if ($v['action'] == self::ACTION_DELETE) {
                unset($this->_properties[$k]);
                continue;
            }
            $this->_properties[$k]['action'] = self::ACTION_NONE;
        }
        if ($useTransactions)
            $this->_db->commit();
        return true;
    }

    /**
     * Удаление записи из базы данных
     * 
     * @param  array|string $where SQL WHERE clause(s).
     * @return bool
     */
    public function delete($where) {
        $where = array();
        foreach ($this->_filters as $k => $v) {
            $where[] = $this->_db->quoteInto($k . ' = ?', $v);
        }
        $result = $this->_db->delete($this->_table, $where);
        $this->_properties = array();
        return (bool) $result;
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
            //if (empty($value) || is_null($value) || (is_string($value) && strlen($value) == 0)) {
            // Я сделал изменения, т.к. ф-ия empty($value)== true например при $value = '0'
            // что по моему мнению не должно быть
            if (is_null($value) || (is_string($value) && strlen($value) == 0)) {

                $empty = empty($value);
                $is_null = is_null($value);
                $is_string = is_string($value);
                $strlen = strlen($value);

                unset($this->$name);
            } else
            if ($this->_properties[$name]['value'] != $value) {
                $this->_properties[$name]['value'] = $value;
                $this->_properties[$name]['action'] = self::ACTION_UPDATE;
            }
        } else {
            $this->_properties[$name] = array('value' => $value,
                'action' => self::ACTION_INSERT);
        }
        return false;
    }

    /**
     * Установить значение действия для поля записи таблицы
     *
     * @param string $name поле записи
     * @param string $action действие для поля записи
     * @return bool
     */
    public function setAction($name, $action) {
        if (array_key_exists($name, $this->_properties)) {
            $this->_properties[$name]['action'] = $action;
            return TRUE;
        }
        return false;
    }

    /**
     * Получить значение действия для поля записи таблицы
     *
     * @param string $name поле записи
     * @return int
     */
    public function getAction($name) {
        if (array_key_exists($name, $this->_properties)) {
            return $this->_properties[$name]['action'];
        }
        return -1;
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
     * Определим существует ли поле со значением в таблице
     * для контретной записи родителя
     *
     * @param string $name
     * @return bool
     */
    public function __isset($name) {
        return array_key_exists($name, $this->_properties) &&
                $this->_properties[$name]['action'] != self::ACTION_DELETE;
    }

    public function __unset($name) {
        if (!array_key_exists($name, $this->_properties))
            return;
        $action = $this->_properties[$name]['action'];
        switch ($action) {
            case self::ACTION_NONE:
            case self::ACTION_UPDATE:
                $this->_properties[$name]['action'] = self::ACTION_DELETE;
                break;
            case self::ACTION_INSERT:
                unset($this->_properties[$name]);
                break;
            case self::ACTION_DELETE:
            default:
        }
    }

    /**
     * Получить массив обьектов записей таблицы
     *
     * @param Zend_Db_Adapter_Abstract $db
     * @param Default_Model_DatabaseObject $class     Класс для раб. с таблицей
     * @param array $filters                          Массив фильтров
     * @return array
     */
    public static function BuildMultiple($db, $class, $filters) {
        if (!class_exists($class))
            throw new Exception('Undefined class specified: ' . $class);
        $obj = new $class($db);
        if (!$obj instanceof Default_Model_Profile)
            throw new Exception('Class does not extend from Profile');
        //$fields = array_keys($filters);
        $fields = array($obj->_parentField);
        $fields[] = $obj->_keyField;
        $fields[] = $obj->_valueField;
        $select = $db->select();
        $select->from($obj->_table, $fields);
        $select->where($obj->_parentField . ' in (?)', $filters);

        $data = $db->fetchAll($select);
        $ret = array();
        foreach ($data as $row) {
            $key = array();
            $key[] = $row[$obj->_parentField];
            $key = join(',', $key);
            if (!array_key_exists($key, $ret)) {
                $_key = (int)$key;
                $objProfile = new $class($db, $_key);
                $ret[$key] = $objProfile;
            }

            $k = $row[$obj->_keyField];
            $v = $row[$obj->_valueField];
            $ret[$key]->_properties[$k] = array('value' => $v,
                'action' => self::ACTION_NONE);
        }
        return $ret;
    }

    /**
     * Получить массив записей таблицы
     *
     * @param Zend_Db_Adapter_Abstract $db
     * @param Default_Model_DatabaseObject $class     Класс для раб. с таблицей
     * @param array $filters                          Массив фильтров
     * @return array
     */
    public static function BuildMultiple_Array($db, $class, $filters) {
        if (!class_exists($class))
            throw new Exception('Undefined class specified: ' . $class);
        $obj = new $class($db);
        if (!$obj instanceof Default_Model_Profile)
            throw new Exception('Class does not extend from Profile');
        $fields = array($obj->_parentField);
        $fields[] = $obj->_keyField;
        $fields[] = $obj->_valueField;
        $select = $db->select();
        $select->from($obj->_table, $fields);
        $select->where($obj->_parentField . ' in (?)', $filters);

        $data = $db->fetchAll($select);
        $ret = array();
        foreach ($data as $row) {
            $key = array();
            $key[] = $row[$obj->_parentField];
            $key = join(',', $key);
            if (!array_key_exists($key, $ret)){
                $ret[$key] = array();
            }
            $k = $row[$obj->_keyField];
            $v = $row[$obj->_valueField];
            $ret[$key][$k] = $v;
        }
        return $ret;
    }

    /**
     * Сделать перевод текста
     *
     * @return string
     */
    public function Translate($aText, $param1 = NULL, $param2 = NULL, $param3 = NULL) {
        $text = Zend_Registry::get('Zend_Translate')->_($aText);
        return sprintf($text, $param1, $param2, $param3);
    }

}
