<?php
/**
 * Default_Model_DbTable_BlogPostTag
 *
 * Таблица - меток для записей в блоге
 *
 *
 * @uses       Default_Model_DatabaseObject
 * @package    Module-Default
 * @subpackage Models
 */
class Default_Model_DbTable_BlogPostTag extends Default_Model_DatabaseObject {

    /**
     *
     * Конфигурация таблицы
     * @var array
     */
    private $_config = array(
        'name' => 'blog_posts_tags',
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
        $this->add('tag');
        $this->add('label');
    }

    /**
     * Загрузить обьект метки для конкретной записи и id метки
     *
     * @param int $post_id
     * @param int $tag_id
     * @return Default_Model_DbTable_BlogPostTag
     */
    public function loadForPost($post_id, $tag_id) {
        $post_id = (int) $post_id;
        $tag_id = (int) $tag_id;

        if ($post_id <= 0 || $tag_id <= 0)
            return false;

        $query = sprintf(
                        'select %s from %s where post_id = %d and id = %d',
                        join(', ', $this->getSelectFields()),
                        $this->_table,
                        $post_id,
                        $tag_id
        );

        return $this->_load($query);
    }


    /**
     * Получить массив обьектов меток для сообщения
     *
     * @param Zend_Db_Adapter_Abstract $db
     * @param array $options
     * @return array Default_Model_DbTable_BlogPostTag
     */
    public static function GetTags($db, $options = array()) {
        // initialize the options
        $defaults = array('post_id' => array());

        foreach ($defaults as $k => $v)
            $options[$k] = array_key_exists($k, $options) ? $options[$k] : $v;

        $select = $db->select();
        $select->from(array('t' => 'blog_posts_tags'), 't.*');

        // filter results on specified post ids (if any)
        if (count($options['post_id']) > 0)
            $select->where('t.post_id in (?)', $options['post_id']);

        // fetch post data from database
        $data = $db->fetchAll($select);

        // turn data into array of DatabaseObject_BlogPostLocation objects
        $tags = parent::BuildMultiple($db, __CLASS__, $data);

        return $tags;
    }

    /**
     * Получить массив меток для сообщения
     *
     * @param Zend_Db_Adapter_Abstract $db
     * @param array $options
     * @return array 
     */
    public static function GetTags_Array($db, $options = array()) {
        // initialize the options
        $defaults = array('post_id' => array());

        foreach ($defaults as $k => $v)
            $options[$k] = array_key_exists($k, $options) ? $options[$k] : $v;

        $select = $db->select();
        $select->from(array('t' => 'blog_posts_tags'), 't.*');

        // filter results on specified post ids (if any)
        if (count($options['post_id']) > 0)
            $select->where('t.post_id in (?)', $options['post_id']);

        // fetch post data from database
        $data = $db->fetchAll($select);

        // turn data into array of DatabaseObject_BlogPostLocation objects
        $tags = parent::BuildMultiple_Array($db, __CLASS__, $data);

        return $tags;
    }

    /**
     * Получить общее кол. записей удовлетворяющих
     * критерия, заданным в парметре $options
     *
     * @param Zend_Db_Adapter_Abstract $db
     * @param array $options
     * @return int
     */
    public static function GetPostsTags_Count($db, $options) {
        $defaults = array('post_id' => array());

        foreach ($defaults as $k => $v) {
            $options[$k] = array_key_exists($k, $options) ? $options[$k] : $v;
        }

        $select = $db->select();
        $select->from(array('blog_posts_tags'), 'count(*)');

        // filter results on specified post ids (if any)
        if (count($options['post_id']) > 0)
            $select->where('post_id in (?)', $options['post_id']);

        return $db->fetchOne($select);
    }
}

?>