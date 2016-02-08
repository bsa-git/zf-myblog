<?php
/**
 * Default_Model_DbTable_BlogPost
 * 
 * Таблица - данных о блогах
 *
 *
 * @uses       Default_Model_Profile
 * @package    Module-Default
 * @subpackage Models
 */
class Default_Model_DbTable_BlogPostProfile extends Default_Model_Profile {

    /**
     * 
     * Конфигурация таблицы
     * @var array
     */
    private $_config = array(
        'name' => 'blog_posts_profile',
        'primary' => array('post_id', 'profile_key'),
        'dependentTables' => array(),
        'referenceMap' => array(
            'colums' => array('post_id'),
            'refTableClass' => 'Default_Model_DbTable_BlogPost',
            'refColums' => 'id',
            'onDelete' => self::CASCADE,
            'onUpdate' => self::CASCADE,)
    );

    /**
     * Конструктор обьекта
     * 
     * @param Zend_Db_Adapter_Abstract $db
     * @param int $post_id 
     */
    public function __construct($db, $post_id = null) {
        $this->_config['db'] = $db;
        parent::__construct($this->_config);

        if ($post_id > 0)
            $this->setPostId($post_id);
    }

    /**
     * Установить фильтр
     * 
     * @param int $post_id 
     */
    public function setPostId($post_id) {
        $filters = array('post_id' => (int) $post_id);
        $this->_filters = $filters;
    }

}

?>