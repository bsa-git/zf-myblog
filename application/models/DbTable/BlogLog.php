<?php

/**
 * Default_Model_DbTable_BlogLog
 *
 * Таблица - лога
 * - события на сайте;
 * - статистика сайта;
 * - ошибки
 *
 *
 * @uses       Default_Model_DatabaseObject
 * @package    Module-Default
 * @subpackage Models
 */
class Default_Model_DbTable_BlogLog extends Default_Model_DatabaseObject {

    /**
     *
     * Конфигурация таблицы
     * @var array
     */
    private $_config = array(
        'name' => 'log_msg', //log_stat, log_error
        'primary' => 'id',
        'dependentTables' => array(),
        'referenceMap' => array()
    );

    /**
     * 
     * Обьект таблицы доп. информации
     * 
     * @var Default_Model_DbTable_BlogPost
     */
    public $profile = null;

    /**
     * Конструктор обьекта таблицы
     * 
     * @param Zend_Db_Adapter_Abstract $db
     * @param string $nameTable // имя таблицы
     */
    public function __construct($db, $nameTable) {
        $this->_config['db'] = $db;
        $this->_config['name'] = $nameTable;
        parent::__construct($this->_config);

        $this->add('ts', time(), self::TYPE_TIMESTAMP);
        $this->add('msg');
        $this->add('pr');
        $this->add('pr_name');
    }

    //================ ОБРАБОТКА СОБЫТИЙ ============

    /**
     * Событие перед вставкой записи
     *
     * @return bool
     */
    protected function preInsert() {
        return true;
    }

    /**
     * Событие после вставки записи
     * 
     * @return bool 
     */
    protected function postInsert() {
        return TRUE;
    }

    /**
     * Событие после загрузки записи
     *
     */
    protected function postLoad() {
        
    }

    /**
     * Событие после обновления записи
     *
     * @return bool
     */
    protected function postUpdate() {
        return TRUE;
    }

    /**
     * Событие перед удалением записи
     *
     * @return bool
     */
    protected function preDelete() {
        return true;
    }

    //=========== РАБОТА С ЗАПИСЬЮ =============

    /**
     * Получить обьект лога по его ID
     *
     * @param int $log_id
     * @return Default_Model_DbTable_BlogLog|bool // Возвращает или обьект Default_Model_DbTable_BlogLog или FALSE
     */
    public function loadForLogID($log_id) {

        if (!$log_id) {
            return false;
        }

        // Получим запрос
        $query = sprintf(
                'select %s from %s where id = %s', join(', ', $this->getSelectFields()), $this->_table, $log_id
        );

        return $this->_load($query);
    }

    //============== РАБОТА С НАБОРОМ ЗАПИСЕЙ =================

    /**
     * Получить общее кол. записей удовлетворяющих
     * критерия, заданным в парметре $options
     * 
     * @param Zend_Db_Adapter_Abstract $db
     * @param string $nameTable
     * @param array $options
     * @return int 
     */
    public static function GetLogCount($db,  $nameTable, $options) {
        $select = self::_GetBaseQuery($db, $nameTable, $options);
        $select->from(null, 'count(*)');

        return $db->fetchOne($select);
    }

    /**
     * Получить массив записей удовлетворяющих
     * критерия, заданным в парметре $options
     * 
     * @param Zend_Db_Adapter_Abstract $db
     * @param string $nameTable
     * @param array $options
     * @return array массив обьектов типа - Default_Model_DbTable_BlogInfo 
     */
    public static function GetLogs($db, $nameTable, $options = array()) {
        //------------------
        // инициализация опций
        $defaults = array(
            'offset' => 0,
            'limit' => 0,
            'order' => 'l.ts Desc',
            'sort' => true
        );

        foreach ($defaults as $k => $v) {
            $options[$k] = array_key_exists($k, $options) ? $options[$k] : $v;
        }

        $select = self::_GetBaseQuery($db, $nameTable, $options);

        $strSelect = $select->__toString();

        // установим поля таблицы для запроса
        $select->from(null, 'l.*');

        // set the offset, limit, and ordering of results
        if ($options['limit'] > 0)
            $select->limit($options['limit'], $options['offset']);

        // Установим параметры сортировки для таблицы
        if ($options['sort'])
            $select = self::GetSelectForSort($select, $options);

        // проверка, есть ли уже данные в кэше:
        // получим результат запроса к базе данных
        $data = $db->fetchAll($select);

        // получим данные в виде массива обьектов Default_Model_DbTable_BlogLog
        $logs = self::BuildMultiple($db, __CLASS__, $data, $nameTable);
        $log_ids = array_keys($logs);

        if (count($log_ids) == 0)
            return array();

        foreach ($logs as $log_id => $log) {

            //!!!!------ Начало установки признака сортировки -----!!!!!
            if ($options['sortColumn']) {
                $logs[$log_id]->sortColumn = $options['sortColumn'];
            }

            if ($options['ascDescFlg']) {
                $logs[$log_id]->ascDescFlg = $options['ascDescFlg'];
            }

            //!!!!------ Конец установки признака сортировки -----!!!!!
        }

        return $logs;
    }

    /**
     * Получить массив данных инф. удовлетворяющих
     * критериям, заданным в параметре $options
     *
     * @param Zend_Db_Adapter_Abstract $db
     * @param string $nameTable
     * @param array $options
     * @return array
     */
    public static function GetLogs_Array($db, $nameTable, $options = array()) {
        //----------------------
        // initialize the options
        $defaults = array(
            'offset' => 0,
            'limit' => 0,
            'order' => 'l.ts Desc',
            'sort' => true
        );

        foreach ($defaults as $k => $v) {
            $options[$k] = array_key_exists($k, $options) ? $options[$k] : $v;
        }

        $select = self::_GetBaseQuery($db, $nameTable, $options);

        // set the fields to select
        $select->from(null, 'l.*');

        // set the offset, limit, and ordering of results
        if ($options['limit'] > 0)
            $select->limit($options['limit'], $options['offset']);

        // Установим параметры сортировки для таблицы
        if ($options['sort'])
            $select = self::GetSelectForSort($select, $options);

        $strSelect = $select->__toString();

        // fetch user data from database
        $data = $db->fetchAll($select);

        // turn data into array of DatabaseObject_User objects
        $logs = parent::BuildMultiple_Array($db, __CLASS__, $data, $nameTable);

        if (count($logs) == 0)
            return $logs;

        $log_ids = array_keys($logs);

        // load the profile data for loaded posts
        $arrLogs = array();
        foreach ($logs as $log_id => $log) {
            $arrLogs[$log_id] = $logs[$log_id];
        }
        return $arrLogs;
    }

    /**
     * Получить массив ids инф. удовлетворяющих
     * критериям, заданным в параметре $options
     *
     * @param Zend_Db_Adapter_Abstract $db
     * @param string $nameTable
     * @param array $options
     * @return array
     */
    public static function GetLogIds_Array($db, $nameTable, $options = array()) {
        //----------------------
        // initialize the options
        $defaults = array(
            'offset' => 0,
            'limit' => 0,
            'order' => 'l.ts',
            'sort' => true
        );

        foreach ($defaults as $k => $v) {
            $options[$k] = array_key_exists($k, $options) ? $options[$k] : $v;
        }

        $select = self::_GetBaseQuery($db, $nameTable, $options);

        // set the fields to select
        $select->from(null, 'l.*');

        // set the offset, limit, and ordering of results
        if ($options['limit'] > 0)
            $select->limit($options['limit'], $options['offset']);

        // Установим параметры сортировки для таблицы
        if ($options['sort'])
            $select = self::GetSelectForSort($select, $options);

        $strSelect = $select->__toString();

        // fetch user data from database
        $data = $db->fetchAll($select);

        // turn data into array of DatabaseObject_User objects
        $logs = parent::BuildMultiple_Array($db, __CLASS__, $data, $nameTable);

        $log_ids = array_keys($logs);

        return $log_ids;
    }

    /**
     * Получить отсортированные и сгруппированные значения из колонки таблицы
     *
     * @param Zend_Db_Adapter_Abstract $db
     * @param string $nameTable
     * @param array $options
     * @return array
     */
    public static function GetValuesForCol($db, $nameTable, $options) {
        $arrValues = FALSE;
        //----------------------
        // инициализация параметров
        $defaults = array(
            'offset' => 0,
            'limit' => 0,
            'group' => TRUE,
            'order' => 'l.ts'
        );
        foreach ($defaults as $k => $v) {
            $options[$k] = array_key_exists($k, $options) ? $options[$k] : $v;
        }


        $select = self::_GetBaseQuery($db, $nameTable, $options);

        // set the offset, limit, and ordering of results
        if ($options['limit'] > 0)
            $select->limit($options['limit'], $options['offset']);

        // Установим параметры сортировки для таблицы
        $select = self::GetSelectForSort($select, $options);

        // Добавим группировку и соответствующую колонку
        $aliasTable = Default_Model_DatabaseObject::getAliasForTable($select, $options['joinTableForSort']);
        if ($aliasTable) {
            $orderData = $select->getPart(Zend_Db_Select::ORDER);
            if ($options['group']) {
                $select->group($orderData[0][0]);
            }
            // Найдем алиас таблицы, запрашиваемого поля
            $arrAliasTable = explode('_', $aliasTable);
            $select->columns(array($arrAliasTable[0] . '.id', $orderData[0][0]));
        } else {
            if ($options['group']) {
                $select->group('l.' . $options['field']);
            }

            $select->columns(array('l.id', 'l.' . $options['field']));
        }

        $strSelect = $select->__toString();

        $arrValues = $db->fetchPairs($select);


        return $arrValues;
    }

    /**
     * Получить запрос удовлетворяющий
     * критерия, заданным в парметре $options
     * 
     * @param Zend_Db_Adapter_Abstract $db
     * @param string $nameTable      // Имя таблицы
     * @param array $options
     * @return Zend_Db_Select 
     */
    private static function _GetBaseQuery($db, $nameTable, $options) {
        // инициализация параметров
        $defaults = array(
            'log_id' => array(),
            'pr_name' => array(),
            'filter' => array()
        );

        foreach ($defaults as $k => $v) {
            $options[$k] = array_key_exists($k, $options) ? $options[$k] : $v;
        }

        // создадим запрос, который выбирает данные из таблицы blog_posts
        $select = $db->select();
        $select->from(array('l' => $nameTable), array());


        // создадим фильтр по IDs сообщениям
        if (count($options['log_id']) > 0)
            $select->where('l.id in (?)', $options['log_id']);

        // отфильтруем результаты, на основе статуса записи
        if (count($options['pr_name']) > 0)
            $select->where('l.pr_name in (?)', $options['pr_name']);

        // создадим фильтр для параметров фильтра
        if ($options['filter']) {
            $count = count($options['filter']);
            if ($count > 0) {
                $select = self::GetSelectForFilter($select, $options['filter']);
            }
        }


        return $select;
    }

    //------------- ФИЛЬТРАЦИЯ ------------//

    /**
     * Получить обьект Select (Zend_Db_Select) для фильтрации записей в таблице
     *
     * @param Zend_Db_Select $select                 Обьект базы даннх Select
     * @param array $filter                          Массив данных для фильтра
     *
     * @return Zend_Db_Select
     */
    public static function GetSelectForFilter($select, $filter) {
        $joinTable = '';
        //-----------------------------------
        // создадим фильтр по разрешению/запрету (актуальности) блогов
        // Построим выражения SELECT
        foreach ($filter as $field => $filterParams) {
            $joinTable = $filterParams['joinTable'];
            $aliasTable = Default_Model_DatabaseObject::getAliasForTable($select, $joinTable);
            $filterParams = $filterParams['filterParams'];
            foreach ($filterParams as $filterParam) {
                if (is_array($filterParam)) {
                    $andLogic = (bool) $filterParam['andLogic'];
                    if ($andLogic) {
                        $select->where('l.' . $field . ' ' . $filterParam['compare'] . ' ?', $filterParam['value']);
                    } else {
                        $select->orWhere('l.' . $field . ' ' . $filterParam['compare'] . ' ?', $filterParam['value']);
                    }
                }
            }
        }
        return $select;
    }

    //------------- СОРТИРОВКА ------------//

    /**
     * Получить обьект Select (Zend_Db_Select) для сортировки записей в таблице
     *
     * @param Zend_Db_Select $select                 Обьект базы даннх Select
     * @param string $order                          Массив данных для фильтра
     *
     * @return Zend_Db_Select
     */
    public static function GetSelectForSort($select, $options) {
        $aliasTable = '';
        $order = $options['order'];
        //--------------------------
        $arrOrder = explode('.', $order);

        // Если в параметре сортировки не задан псевдоним таблицы
        // то определим его, и если нужно присоединим,
        // соответствующую таблицу
        if (count($arrOrder) == 1) {
            $joinTableForSort = $options['joinTableForSort'];
            if ($joinTableForSort) {
                // Определим какие таблицы уже присоединены
                $fromTables = $select->getPart(Zend_Db_Select::FROM);
                foreach ($fromTables as $alias => $joinParams) {
                    // Если таблица -> $joinTableForSort уже присоединена
                    // то получим ее псевдоним
                    if ($joinParams['tableName'] == $joinTableForSort) {
                        $aliasTable = $alias;
                    }
                }
                if ($aliasTable) {
                    $order = $aliasTable . '.' . $order;
                } else {

                    // Получим поле сортировки
                    $arrOrder = explode(' ', trim($order));
                    $field = $arrOrder[0];
                    $order = 'l.' . $field . ' ' . $arrOrder[1];
                }
            } else {
                $order = 'l.' . $order;
            }
        }
        $select->order($order);
        return $select;
    }
}

?>