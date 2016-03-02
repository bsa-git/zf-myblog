<?php
/**
 * Default_Model_DbTable_BlogInfoProfile
 * 
 * Table - additional information content
 *
 *
 * @uses       Default_Model_Profile
 * @package    Module-Default
 * @subpackage Models
 * @author   Sergii Beskorovainyi <bsa2657@yandex.ru>
 * @license  MIT <http://www.opensource.org/licenses/mit-license.php>
 * @link     https://github.com/bsa-git/zf-myblog/
 */
class Default_Model_DbTable_BlogInfoProfile extends Default_Model_Profile {

    /**
     * 
     * Table config
     * @var array
     */
    private $_config = array(
        'name' => 'blog_info_profile',
        'primary' => array('info_id', 'profile_key'),
        'dependentTables' => array(),
        'referenceMap' => array(
            'colums' => array('info_id'),
            'refTableClass' => 'Default_Model_DbTable_BlogInfo',
            'refColums' => 'id',
            'onDelete' => self::CASCADE,
            'onUpdate' => self::CASCADE,)
    );

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
     * Set info ID
     * 
     * @param int $info_id 
     */
    public function setInfoId($info_id) {
        $filters = array('info_id' => (int) $info_id);
        $this->_filters = $filters;
    }
}

?>