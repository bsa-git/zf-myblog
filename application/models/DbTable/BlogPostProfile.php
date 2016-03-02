<?php
/**
 * Default_Model_DbTable_BlogPost
 * 
 * Table - to work with post profiles
 *
 *
 * @uses       Default_Model_Profile
 * @package    Module-Default
 * @subpackage Models
 * @author   Sergii Beskorovainyi <bsa2657@yandex.ru>
 * @license  MIT <http://www.opensource.org/licenses/mit-license.php>
 * @link     https://github.com/bsa-git/zf-myblog/
 */
class Default_Model_DbTable_BlogPostProfile extends Default_Model_Profile {

    /**
     * 
     * Table config
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
    
    //=====================================

    /**
     * Constructor
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
     * Set filter
     * 
     * @param int $post_id 
     */
    public function setPostId($post_id) {
        $filters = array('post_id' => (int) $post_id);
        $this->_filters = $filters;
    }

}

?>