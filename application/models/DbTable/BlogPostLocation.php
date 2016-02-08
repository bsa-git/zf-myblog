<?php
/**
 * Default_Model_DbTable_BlogPostLocation
 *
 * Таблица - гео информации Google картам
 *
 *
 * @uses       Default_Model_DatabaseObject
 * @package    Module-Default
 * @subpackage Models
 */
class Default_Model_DbTable_BlogPostLocation extends Default_Model_DatabaseObject {

    /**
     *
     * Конфигурация таблицы
     * @var array
     */
    private $_config = array(
        'name' => 'blog_posts_locations',
        'primary' => 'id',
        'dependentTables' => array(),
        'referenceMap' => array(
            'colums' => array('post_id'),
            'refTableClass' => 'Default_Model_DbTable_BlogPost',
            'refColums' => 'id',
            'onDelete' => self::CASCADE,
            'onUpdate' => self::CASCADE,)
    );
    
     /**
     * Конструктор обьекта таблицы
     * 
     * @param Zend_Db_Adapter_Abstract $db
     */
    public function __construct($db) {
        $this->_config['db'] = $db;
        parent::__construct($this->_config);

        $this->add('post_id');
        $this->add('longitude');
        $this->add('latitude');
        $this->add('description');
        $this->add('content', NULL, self::TYPE_HTML);
        $this->add('correction', 0);
        $this->add('details', NULL, self::TYPE_HTML);
    }

    
    public function loadForPost($post_id, $location_id) {
        $post_id = (int) $post_id;
        $location_id = (int) $location_id;

        if ($post_id <= 0 || $location_id <= 0)
            return false;

        $query = sprintf(
                        'select %s from %s where post_id = %d and id = %d',
                        join(', ', $this->getSelectFields()),
                        $this->_table,
                        $post_id,
                        $location_id
        );

        return $this->_load($query);
    }

    public function __set($name, $value) {
        switch ($name) {
            case 'latitude':
            case 'longitude':
                $value = sprintf('%01.6lf', $value);
                break;
        }

        return parent::__set($name, $value);
    }

    /**
     * Получить массив обьектов координат для сообщения
     *
     * @param Zend_Db_Adapter_Abstract $db
     * @param array $options
     * @return array Default_Model_DbTable_BlogPostLocation
     */
    public static function GetLocations($db, $options = array()) {
        // initialize the options
        $defaults = array('post_id' => array());

        foreach ($defaults as $k => $v)
            $options[$k] = array_key_exists($k, $options) ? $options[$k] : $v;

        $select = $db->select();
        $select->from(array('l' => 'blog_posts_locations'), 'l.*');

        // filter results on specified post ids (if any)
        if (count($options['post_id']) > 0)
            $select->where('l.post_id in (?)', $options['post_id']);

        // fetch post data from database
        $data = $db->fetchAll($select);

        // turn data into array of DatabaseObject_BlogPostLocation objects
        $locations = parent::BuildMultiple($db, __CLASS__, $data);

        return $locations;
    }

    /**
     * Получить массив координат для сообщения
     *
     * @param Zend_Db_Adapter_Abstract $db
     * @param array $options
     * @return array Default_Model_DbTable_BlogPostLocation
     */
    public static function GetLocations_Array($db, $options = array()) {
        // initialize the options
        $defaults = array('post_id' => array());

        foreach ($defaults as $k => $v)
            $options[$k] = array_key_exists($k, $options) ? $options[$k] : $v;

        $select = $db->select();
        $select->from(array('l' => 'blog_posts_locations'), 'l.*');

        // filter results on specified post ids (if any)
        if (count($options['post_id']) > 0)
            $select->where('l.post_id in (?)', $options['post_id']);

        // fetch post data from database
        $data = $db->fetchAll($select);

        // turn data into array of DatabaseObject_BlogPostLocation objects
        $locations = parent::BuildMultiple_Array($db, __CLASS__, $data);

        return $locations;
    }

    /**
     * Получить общее кол. записей удовлетворяющих
     * критерия, заданным в парметре $options
     *
     * @param Zend_Db_Adapter_Abstract $db
     * @param array $options
     * @return int
     */
    public static function GetPostsLocations_Count($db, $options) {
        $defaults = array('post_id' => array());

        foreach ($defaults as $k => $v) {
            $options[$k] = array_key_exists($k, $options) ? $options[$k] : $v;
        }

        $select = $db->select();
        $select->from(array('blog_posts_locations'), 'count(*)');

        // filter results on specified post ids (if any)
        if (count($options['post_id']) > 0)
            $select->where('post_id in (?)', $options['post_id']);

        return $db->fetchOne($select);
    }
}

?>