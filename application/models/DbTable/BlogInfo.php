<?php

/**
 * Default_Model_DbTable_BlogInfo
 *
 * Table - info help
 * It is used to generate output information in a window or in a tooltip
 *
 *
 * @uses       Default_Model_DatabaseObject
 * @package    Module-Default
 * @subpackage Models
 * @author   Sergii Beskorovainyi <bsa2657@yandex.ru>
 * @license  MIT <http://www.opensource.org/licenses/mit-license.php>
 * @link     https://github.com/bsa-git/zf-myblog/
 */
class Default_Model_DbTable_BlogInfo extends Default_Model_DatabaseObject {

    /**
     * Table config
     * 
     * @var array
     */
    private $_config = array(
        'name' => 'blog_info',
        'primary' => 'id',
        'dependentTables' => array('Default_Model_DbTable_BlogInfoProfile'),
        'referenceMap' => array()
    );

    /**
     * 
     * Table object for additional information
     * 
     * @var Default_Model_DbTable_BlogInfoProfile
     */
    public $profile = null;
    
    //=========================================

    /**
     * Constructor
     * 
     * @param Zend_Db_Adapter_Abstract $db
     */
    public function __construct($db) {
        $this->_config['db'] = $db;
        parent::__construct($this->_config);

        $this->add('info_key');
        $this->add('title_info');
        $this->add('actual', 1, self::TYPE_BOOLEAN);

        $this->profile = new Default_Model_DbTable_BlogInfoProfile($db);
    }

    //================ HANDLING OF EVENTS ============

    /**
     * Event before inserting the record
     *
     * @return bool
     */
    protected function preInsert() {
        $this->info_key = $this->generateUniqueKey($this->title_info);
        return true;
    }

    /**
     * Event after inserting the record
     * 
     * @return bool 
     */
    protected function postInsert() {
        $this->profile->setInfoId($this->getId());

        // Удалим базовый путь из URL ресурсов
        if ($this->filterContent('save')) {

            $this->profile->save(false);
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * Event after loaded the record
     *
     */
    protected function postLoad() {
        $this->profile->setInfoId($this->getId());
        $this->profile->load();

        // Добавим базовый путь к URL ресурсам на странице HTML
        $this->filterContent('load');
    }

    /**
     * Event after updated the record
     *
     * @return bool
     */
    protected function postUpdate() {

        // Удалим базовый путь из URL ресурсов
        if ($this->filterContent('save')) {

            $this->profile->save(false);
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * Event before deleting the record
     *
     * @return bool
     */
    protected function preDelete() {
        $this->profile->delete();
        return true;
    }

    //=========== WORKING WITH RECORD =============

    /**
     * Get an array objects of informational messages in all languages
     *
     * @param string $info_key
     * @return array Default_Model_DbTable_BlogInfo
     */
    public function loadForInfo($info_key) {

        if (!$info_key) {
            return false;
        }

        // Получим запрос
        $query = sprintf(
                'select %s from %s where info_key = "%s"', join(', ', $this->getSelectFields()), $this->_table, $info_key
        );

        return $this->_load($query);
    }

    //============== WORKING WITH RECORDS =================

    /**
     * Get the total number of records satisfying the criteria specified in the parameter $options
     * 
     * @param Zend_Db_Adapter_Abstract $db
     * @param array $options
     * @return int 
     */
    public static function GetInfoCount($db, $options) {
        $select = self::_GetBaseQuery($db, $options);
        $select->from(null, 'count(*)');

        return $db->fetchOne($select);
    }

    /**
     * Get the array of records satisfying the criteria specified in the parameter $options
     * 
     * @param Zend_Db_Adapter_Abstract $db
     * @param array $options
     * @return array массив обьектов типа - Default_Model_DbTable_BlogInfo 
     */
    public static function GetInfos($db, $options = array()) {
        $infos = FALSE;
        //------------------
        // инициализация опций
        $defaults = array(
            'offset' => 0,
            'limit' => 0,
            'order' => 'i.title_info',
            'sort' => true
        );

        foreach ($defaults as $k => $v) {
            $options[$k] = array_key_exists($k, $options) ? $options[$k] : $v;
        }

        $select = self::_GetBaseQuery($db, $options);

        $strSelect = $select->__toString();

        // установим поля таблицы для запроса
        $select->from(null, 'i.*');

        // set the offset, limit, and ordering of results
        if ($options['limit'] > 0){
            $select->limit($options['limit'], $options['offset']);
        }
            

        // Установим параметры сортировки для таблицы
        if ($options['sort'])
            $select = self::GetSelectForSort($select, $options);

        //------ Применить кеширование -------
        $dbCache = Default_Plugin_SysBox::getCache('db');
        if ($dbCache->getOption('caching')) {
            // Получим TAG для кеширования
            $arrItems = array(
                $select,
                $options
            );
            $strSerialize = serialize($arrItems);
            $tagCache = md5($strSerialize);

            // Очистим кеш
            if (Default_Plugin_SysBox::isCleanCache()) {
                $dbCache->clean(Zend_Cache::CLEANING_MODE_ALL);
            }

            // Получим данные из кеша по тегу $tagCache
            $infos = $dbCache->load($tagCache);
        }

        // проверка, есть ли уже данные в кэше:
        if ($infos === FALSE) {
            // получим результат запроса к базе данных
            $data = $db->fetchAll($select);

            // получим данные в виде массива обьектов Default_Model_DbTable_BlogInfo
            $infos = self::BuildMultiple($db, __CLASS__, $data);
            $info_ids = array_keys($infos);

            if (count($info_ids) == 0)
                return array();

            // получим данные о загруженных сообщениях
            $profiles = Default_Model_Profile::BuildMultiple(
                            $db, 'Default_Model_DbTable_BlogInfoProfile', array($info_ids)
            );

            foreach ($infos as $info_id => $info) {
                if (array_key_exists($info_id, $profiles)
                        && $profiles[$info_id] instanceof Default_Model_DbTable_BlogInfoProfile) {

                    $infos[$info_id]->profile = $profiles[$info_id];
                } else {
                    $infos[$info_id]->profile->setInfoId($info_id);
                }

                //!!!!------ Начало установки признака сортировки -----!!!!!
                if ($options['sortColumn']) {
                    $infos[$info_id]->sortColumn = $options['sortColumn'];
                }

                if ($options['ascDescFlg']) {
                    $infos[$info_id]->ascDescFlg = $options['ascDescFlg'];
                }

                //!!!!------ Конец установки признака сортировки -----!!!!!
            }

            // Если разрешено кеширование, то сохраним данные в кеше
            if ($dbCache->getOption('caching')) {
                $dbCache->save($infos, $tagCache);
            }
        } else {
            $result = $infos;
        }
        return $infos;
    }

    /**
     * Get sorted array in accordance with the collation settings
     *
     * @param Zend_Db_Adapter_Abstract $db
     * @param array $options
     * @return array
     */
    public static function GetSortProfiles($db, $options) {

        // Получим все записи с установленными признаками желаемой сортировки
        $infos = self::GetInfos($db, $options);
        // Отсортируем массив
        usort($infos, array("Default_Model_DatabaseObject", "_SortProfiles"));

        return $infos;
    }

    /**
     * Get the array of data satisfying the criteria specified in the parameter $options
     *
     * @param Zend_Db_Adapter_Abstract $db
     * @param array $options
     * @return array
     */
    public static function GetInfos_Array($db, $options = array()) {
        $arrInfos = FALSE;
        //----------------------
        // initialize the options
        $defaults = array(
            'offset' => 0,
            'limit' => 0,
            'order' => 'i.title_info',
            'sort' => true
        );

        foreach ($defaults as $k => $v) {
            $options[$k] = array_key_exists($k, $options) ? $options[$k] : $v;
        }

        $select = self::_GetBaseQuery($db, $options);

        // set the fields to select
        $select->from(null, 'i.*');

        // set the offset, limit, and ordering of results
        if ($options['limit'] > 0)
            $select->limit($options['limit'], $options['offset']);

        // Установим параметры сортировки для таблицы
        if ($options['sort'])
            $select = self::GetSelectForSort($select, $options);

        $strSelect = $select->__toString();

        //------ Применить кеширование -------
        $dbCache = Default_Plugin_SysBox::getCache('db');
        if ($dbCache->getOption('caching')) {
            // Получим TAG для кеширования
            $arrItems = array(
                $select,
                $options
            );
            $strSerialize = serialize($arrItems);
            $tagCache = md5($strSerialize);

            // Очистим кеш
            if (Default_Plugin_SysBox::isCleanCache()) {
                $dbCache->clean(Zend_Cache::CLEANING_MODE_ALL);
//                $cache->clean(Zend_Cache::CLEANING_MODE_OLD);
//                $cache->clean(Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, array($tagCache));
            }

            // Получим данные из кеша по тегу $tagCache
            $arrInfos = $dbCache->load($tagCache);
        }

        // проверка, есть ли уже данные в кэше:
        if ($arrInfos === FALSE) {
            // fetch user data from database
            $data = $db->fetchAll($select);

            // turn data into array of DatabaseObject_User objects
            $infos = parent::BuildMultiple_Array($db, __CLASS__, $data);

            if (count($infos) == 0)
                return $infos;

            $info_ids = array_keys($infos);

            // load the profile data for loaded posts
            $profiles = Default_Model_Profile::BuildMultiple_Array($db, 'Default_Model_DbTable_BlogInfoProfile', array($info_ids));
            $arrInfos = array();
            foreach ($infos as $info_id => $info) {
                if (array_key_exists($info_id, $profiles)) {
                    $arrInfos[$info_id] = $infos[$info_id] + $profiles[$info_id];
                } else {
                    $arrInfos[$info_id] = $infos[$info_id];
                }
            }
            // Если разрешено кеширование, то сохраним данные в кеше
            if ($dbCache->getOption('caching')) {
                $dbCache->save($arrInfos, $tagCache);
            }
        } else {
            $result = $arrInfos;
        }
        return $arrInfos;
    }

    /**
     * Get the array of ids satisfying the criteria specified in the parameter $options
     *
     * @param Zend_Db_Adapter_Abstract $db
     * @param array $options
     * @return array
     */
    public static function GetInfosIds_Array($db, $options = array()) {
        $info_ids = FALSE;
        //----------------------
        // initialize the options
        $defaults = array(
            'offset' => 0,
            'limit' => 0,
            'order' => 'i.title_info',
            'sort' => true
        );

        foreach ($defaults as $k => $v) {
            $options[$k] = array_key_exists($k, $options) ? $options[$k] : $v;
        }

        $select = self::_GetBaseQuery($db, $options);

        // set the fields to select
        $select->from(null, 'i.*');

        // set the offset, limit, and ordering of results
        if ($options['limit'] > 0)
            $select->limit($options['limit'], $options['offset']);

        // Установим параметры сортировки для таблицы
        if ($options['sort'])
            $select = self::GetSelectForSort($select, $options);

        $strSelect = $select->__toString();

        //------ Применить кеширование -------
        $dbCache = Default_Plugin_SysBox::getCache('db');
        if ($dbCache->getOption('caching')) {
            // Получим TAG для кеширования
            $arrItems = array(
                $select,
                $options
            );
            $strSerialize = serialize($arrItems);
            $tagCache = md5($strSerialize);

            // Очистим кеш
            if (Default_Plugin_SysBox::isCleanCache()) {
                $dbCache->clean(Zend_Cache::CLEANING_MODE_ALL);
            }

            // Получим данные из кеша по тегу $tagCache
            $info_ids = $dbCache->load($tagCache);
        }

        // проверка, есть ли уже данные в кэше:
        if ($info_ids === FALSE) {
            // fetch user data from database
            $data = $db->fetchAll($select);

            // turn data into array of DatabaseObject_User objects
            $infos = parent::BuildMultiple_Array($db, __CLASS__, $data);

            if (count($posts) == 0) {
                $info_ids = array();
            } else {
                $info_ids = array_keys($infos);
            }
            
            // Если разрешено кеширование, то сохраним данные в кеше
            if ($dbCache->getOption('caching')) {
                $dbCache->save($info_ids, $tagCache);
            }
        }else {
            $result = $info_ids;
        }
        return $info_ids;
    }

    /**
     * Get sorted and grouped values from table column
     *
     * @param Zend_Db_Adapter_Abstract $db
     * @param array $options
     * @return array
     */
    public static function GetValuesForCol($db, $options) {
        $arrValues = FALSE;
        //----------------------
        // инициализация параметров
        $defaults = array(
            'offset' => 0,
            'limit' => 0,
            'group' => TRUE,
            'order' => 'i.title_info'
        );
        foreach ($defaults as $k => $v) {
            $options[$k] = array_key_exists($k, $options) ? $options[$k] : $v;
        }


        $select = self::_GetBaseQuery($db, $options);

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
                $select->group('i.' . $options['field']);
            }

            $select->columns(array('i.id', 'i.' . $options['field']));
        }

        $strSelect = $select->__toString();

        //------ Применить кеширование -------
        $dbCache = Default_Plugin_SysBox::getCache('db');
        if ($dbCache->getOption('caching')) {
            // Получим TAG для кеширования
            $arrItems = array(
                $select,
                $options
            );
            $strSerialize = serialize($arrItems);
            $tagCache = md5($strSerialize);

            // Очистим кеш
            if (Default_Plugin_SysBox::isCleanCache()) {
                $dbCache->clean(Zend_Cache::CLEANING_MODE_ALL);
            }

            // Получим данные из кеша по тегу $tagCache
            $arrValues = $dbCache->load($tagCache);
        }
        // проверка, есть ли уже данные в кэше:
        if ($arrValues === FALSE) {
            $arrValues = $db->fetchPairs($select);

            // Если разрешено кеширование, то сохраним данные в кеше
            if ($dbCache->getOption('caching')) {
                $dbCache->save($arrValues, $tagCache);
            }
        } else {
            $result = $arrValues;
        }

        return $arrValues;
    }

    /**
     * Get the query satisfying the criteria specified in the parameter $options
     * 
     * @param Zend_Db_Adapter_Abstract $db
     * @param array $options
     * @return Zend_Db_Select 
     */
    private static function _GetBaseQuery($db, $options) {
        // инициализация параметров
        $defaults = array(
            'info_id' => array(),
            'actuals' => array(0, 1),
            'info_key' => '',
            'filter' => array()
        );

        foreach ($defaults as $k => $v) {
            $options[$k] = array_key_exists($k, $options) ? $options[$k] : $v;
        }

        // создадим запрос, который выбирает данные из таблицы blog_posts
        $select = $db->select();
        $select->from(array('i' => 'blog_info'), array());


        // создадим фильтр по IDs сообщениям
        if (count($options['info_id']) > 0)
            $select->where('i.id in (?)', $options['info_id']);

        // создадим фильтр по разрешению/запрету (актуальности) блогов
        if (count($options['actuals']) > 0)
            $select->where('i.actual in (?)', $options['actuals']);

        // отфильтруем результаты, на основе статуса записи
        if (strlen($options['info_key']) > 0)
            $select->where('info_key = ?', $options['info_key']);

        // создадим фильтр для параметров фильтра
        if ($options['filter']) {
            $count = count($options['filter']);
            if ($count > 0) {
                $select = self::GetSelectForFilter($select, $options['filter']);
            }
        }


        return $select;
    }

    //------------- FILTERS ------------//

    /**
     * Get Select object (Zend_Db_Select) for filtering table records
     *
     * @param Zend_Db_Select $select        
     * @param array $filter                 
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
            switch ($joinTable) {
                case 'blog_info_profile':
                    if (!$aliasTable) {
                        $select->joinInner(array('i_profile' => $joinTable), 'i_profile.info_id = i.id', array())
                                ->where('i_profile.profile_key = ?', $field);
                    }

                    foreach ($filterParams as $filterParam) {
                        if (is_array($filterParam)) {
                            $andLogic = (bool) $filterParam['andLogic'];
                            if ($andLogic) {
                                $select->where('i_profile.profile_value ' . $filterParam['compare'] . ' ?', $filterParam['value']);
                            } else {
                                $select->orWhere('i_profile.profile_value ' . $filterParam['compare'] . ' ?', $filterParam['value']);
                            }
                        }
                    }

                    break;
                default:
                    foreach ($filterParams as $filterParam) {
                        if (is_array($filterParam)) {
                            $andLogic = (bool) $filterParam['andLogic'];
                            if ($andLogic) {
                                $select->where('i.' . $field . ' ' . $filterParam['compare'] . ' ?', $filterParam['value']);
                            } else {
                                $select->orWhere('i.' . $field . ' ' . $filterParam['compare'] . ' ?', $filterParam['value']);
                            }
                        }
                    }
                    break;
            }
        }
        return $select;
    }

    //------------- SORT ------------//

    /**
     * Get Select object (Zend_Db_Select) for sorting table records
     *
     * @param Zend_Db_Select $select                 
     * @param array $options                          
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
                    // Присоединим таблицу
                    $joinTable = $joinTableForSort;
                    switch ($joinTable) {
                        case 'blog_info_profile':
                            $select->joinInner(array('i_profile' => $joinTable), 'i_profile.info_id = i.id', array())
                                    ->where('i_profile.profile_key = ?', $field);
                            $order = 'i_profile.profile_value ' . $arrOrder[1];
                            break;
                        default:
                            $order = 'i.' . $field . ' ' . $arrOrder[1];
                            break;
                    }
                }
            } else {
                $order = 'i.' . $order;
            }
        }
        $select->order($order);
        return $select;
    }

    //============== ADDITIONAL FUNCTIONS ================

    /**
     * Create a unique key for the information message
     *
     * @param string $title
     * @return string
     */
    protected function generateUniqueKey($title) {

        //Получим обьект строки для заголовка
        $oTitle = new Default_Plugin_String($title);
        //Преобразуем заголовок сообщения к нижнему регистру
        $oTitle = $oTitle->toLower()->translit();

        // Преобразуем заголовок сообщения в транслит.
        $url = strval($oTitle);


        $filters = array(
            // replace & with 'and' for readability
            '/&+/' => 'and',
            // replace non-alphanumeric characters with a hyphen
            '/[^a-z0-9]+/i' => '-',
            // replace multiple hyphens with a single hyphen
            '/-+/' => '-'
        );


        // apply each replacement
        foreach ($filters as $regex => $replacement)
            $url = preg_replace($regex, $replacement, $url);

        // remove hyphens from the start and end of string
        $url = trim($url, '-');

        // restrict the length of the URL
        $url = trim(substr($url, 0, 255));

        // set a default value just in case
        if (strlen($url) == 0)
            $url = 'post';


        // find similar URLs
        $query = sprintf("select info_key from %s where info_key like ?", $this->_table);

        $query = $this->_db->quoteInto($query, $url . '%');
        $result = $this->_db->fetchCol($query);


        // if no matching URLs then return the current URL
        if (count($result) == 0 || !in_array($url, $result))
            return $url;

        // generate a unique URL
        $i = 2;
        do {
            $_url = $url . '-' . $i++;
        } while (in_array($_url, $result));

        return $_url;
    }

    /**
     * Content filtering information for different languages:
     * - when saving removes the base path in the URL resources
     * - when reading add the base path to the URL resources
     *
     * @param string $type  Возможно 2 значения: 'save' и 'load'
     * @return bool
     */
    protected function filterContent($type) {
        $is_content = false;
        //--------------------------
        // Проверим наличие информации
        $translate = Zend_Registry::get('Zend_Translate');
        $list_languages = $translate->getAdapter()->getList();
        foreach ($list_languages as $language) {
            $key_content = 'content_' . $language;
            if ($this->profile->$key_content) {
                $is_content = TRUE;
            }
        }


        if (!$is_content) {
            return TRUE;
        }

        //Сохранение записи
        if ($type === 'save') {

            foreach ($list_languages as $language) {
                $key_content = 'content_' . $language;

                if (!$this->profile->$key_content) {
                    continue;
                }

                // Определим действие над полем
                $action = $this->profile->getAction($key_content);

                // Удалим базовый путь из URL ресурсов
                $filterContent = new Default_Form_Filter_DeleteBasePathUrl();
                $content = $filterContent->filter($this->profile->$key_content);
                if (!$content === FALSE) {
                    $this->profile->$key_content = $content;

                    // Установим старое действие над полем
                    $this->profile->setAction($key_content, $action);
                } else {
                    return FALSE;
                }
            }
        }

        //Чтение записи
        if ($type === 'load') {
            foreach ($list_languages as $language) {
                $key_content = 'content_' . $language;

                if (!$this->profile->$key_content) {
                    continue;
                }

                // Добавим базовый путь к URL ресурсам на странице HTML
                $filterContent = new Default_Form_Filter_AddBasePathUrl();
                $content = $filterContent->filter($this->profile->$key_content);

                if (!$content === FALSE) {
                    $this->profile->$key_content = $content;
                    // Установим действие по умолчанию для свойства 'content'
                    $this->profile->setAction($key_content, Default_Model_Profile::ACTION_NONE);
                } else {
                    return FALSE;
                }
            }
        }
        return TRUE;
    }

}