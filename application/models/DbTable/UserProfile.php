<?php

/**
 * Default_Model_DbTable_User
 * 
 * Table - to work with user profiles
 *
 *
 * @uses       Default_Model_Profile
 * @package    Module-Default
 * @subpackage Models
 * @author   Sergii Beskorovainyi <bsa2657@yandex.ru>
 * @license  MIT <http://www.opensource.org/licenses/mit-license.php>
 * @link     https://github.com/bsa-git/zf-myblog/
 */
class Default_Model_DbTable_UserProfile extends Default_Model_Profile {

    /**
     * 
     * Table config
     * @var array
     */
    private $_config = array(
        'name' => 'users_profile',
        'primary' => array('user_id', 'profile_key'),
        'dependentTables' => array(),
        'referenceMap' => array(
            'colums' => array('user_id'),
            'refTableClass' => 'Default_Model_DbTable_User',
            'refColums' => 'id',
            'onDelete' => self::CASCADE,
            'onUpdate' => self::CASCADE,)
    );
    
    //===================================

    /**
     * Constructor
     * 
     * @param Zend_Db_Adapter_Abstract $db
     * @param int $user_id 
     */
    public function __construct($db, $user_id = null) {
        $this->_config['db'] = $db;
        parent::__construct($this->_config);
        if ($user_id > 0)
            $this->setUserId($user_id);
    }

    /**
     * Set filter
     * 
     * @param int $user_id 
     */
    public function setUserId($user_id) {
        $filters = array('user_id' => (int) $user_id);
        $this->_filters = $filters;
    }

}