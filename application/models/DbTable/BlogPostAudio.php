<?php

/**
 * Default_Model_DbTable_BlogPostAudio
 *
 * Table - to work with audio files
 *
 *
 * @uses       Default_Model_DatabaseObject
 * @package    Module-Default
 * @subpackage Models
 * @author   Sergii Beskorovainyi <bsa2657@yandex.ru>
 * @license  MIT <http://www.opensource.org/licenses/mit-license.php>
 * @link     https://github.com/bsa-git/zf-myblog/
 */
class Default_Model_DbTable_BlogPostAudio extends Default_Model_DatabaseObject {

    /**
     * Table config
     * @var array
     */
    private $_config = array(
        'name' => 'blog_posts_audio',
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
     *
     * Uploaded file
     *
     * @var string
     */
    protected $_uploadedFile;

    //============================

    /**
     * Constructor
     * 
     * @param Zend_Db_Adapter_Abstract $db
     */
    public function __construct($db) {
        $this->_config['db'] = $db;
        parent::__construct($this->_config);

        $this->add('filename');
        $this->add('post_id');
        $this->add('ranking');
        $this->add('name');
        $this->add('comment', "", self::TYPE_HTML);
    }

    //================ HANDLING OF EVENTS ============

    /**
     * Event before inserting the record
     *
     * @return bool
     */
    public function preInsert() {
        // first check that we can write to upload directory
        $path = self::GetUploadPath();
        if (!file_exists($path) || !is_dir($path))
            throw new Exception($this->Translate("Путь к загруженному файлу '%s' не найден", $path));

        if (!is_writable($path))
            throw new Exception($this->Translate("Путь к загруженному файлу '%s' защищен от записи", $path));

        // now determine the ranking of the new image
        $query = sprintf(
                        'select coalesce(max(ranking), 0) + 1 from %s where post_id = %d',
                        $this->_table,
                        $this->post_id
        );

        $this->ranking = $this->_db->fetchOne($query);
        return true;
    }

    /**
     * Event after inserting the record
     *
     * @return bool
     */
    public function postInsert() {
        if (strlen($this->_uploadedFile) > 0) {
            $uploadedFile = $this->_uploadedFile;
            $resultFile = $this->getFullPath();
            $result = rename($uploadedFile, $resultFile);
            return $result;
        }

        return false;
    }

    /**
     * Event before deleting the record
     *
     * @return bool
     */
    public function preDelete() {
        unlink($this->getFullPath());
        return true;
    }

    //================ WORKING WITH FILES ============

    /**
     * Upload data from audio for a particular post
     *
     * @param int $post_id
     * @param int $audio_id
     * @return <type>
     */
    public function loadForPost($post_id, $audio_id) {
        $post_id = (int) $post_id;
        $audio_id = (int) $audio_id;

        if ($post_id <= 0 || $audio_id <= 0)
            return false;

        $query = sprintf(
                        'select %s from %s where post_id = %d and id = %d',
                        join(', ', $this->getSelectFields()),
                        $this->_table,
                        $post_id,
                        $audio_id
        );

        return $this->_load($query);
    }

    /**
     * Checking the downloaded file that it exists, that it can be read
     *
     * @param string $path
     */
    public function uploadFile($path) {
        if (!file_exists($path) || !is_file($path))
            throw new Exception($this->Translate("Путь к загруженному файлу '%s' не найден", $path));

        if (!is_readable($path))
            throw new Exception($this->Translate("Путь к загруженному файлу '%s' защищен от записи", $path));

        $this->_uploadedFile = $path;
    }

    /**
     * Get the full path to the file and the file name is replaced by its unique ID code from DB
     *
     * @return string
     */
    public function getFullPath($username='') {
        $fileInfo = pathinfo($this->filename);
        return sprintf('%s/%s', self::GetUploadPath($username), $this->getId() . '.' . $fileInfo['extension']);
    }

    
    /**
     * Get the audio file storage path for a specific user
     *
     * @return string
     */
    public static function GetUploadPath($aUsername='') {
        // Определим путь загрузки изображений
        $config = Zend_Registry::get('config');
        $patch = $config['paths']['upload']['dir'];
        $patch = trim($patch, '/');

        if ($aUsername) {
            $patch = $patch . '/' . $aUsername . '/files/audio';
        } else {
            $auth = Zend_Auth::getInstance();
            if ($auth->hasIdentity()) {
                $identity = $auth->getIdentity();
                $username = new Default_Plugin_String($identity->username);
                $username = (string) $username->translit();
                $patch = $patch . '/' . $username . '/files/audio';
            } else {
                $patch = $patch . '/guest/files/audio';
            }
        }
        return $patch;
    }
    
    /**
     * Get the full URL to the resource
     *
     * @return string
     */
    public function getFullUrl_Res($username='') {
        return  Default_Plugin_SysBox::getFullURL_Res($this->getFullUrl($username));
    }
    
    /**
     * Get the full URL to the file and the file name is replaced by its unique ID code from the database
     *
     * @return string
     */
    public function getFullUrl($username='') {
        $fileInfo = pathinfo($this->filename);
        return sprintf('%s/%s', self::GetUploadUrl($username), $this->getId() . '.' . $fileInfo['extension']);
    }
    
    /**
     * Get the URL storing audio files for a specific user
     *
     * @return string
     */
    public static function GetUploadUrl($aUsername='') {
        // Определим путь загрузки изображений
        $config = Zend_Registry::get('config');
        $patch = $config['paths']['upload']['url'];
        $patch = trim($patch, '/');

        if ($aUsername) {
            $patch = $patch . '/' . $aUsername . '/files/audio';
        } else {
            $auth = Zend_Auth::getInstance();
            if ($auth->hasIdentity()) {
                $identity = $auth->getIdentity();
                $username = new Default_Plugin_String($identity->username);
                $username = (string) $username->translit();
                $patch = $patch . '/' . $username . '/files/audio';
            } else {
                $patch = $patch . '/guest/files/audio';
            }
        }
        return $patch;
    }


    /**
     * Get an array of audio objects for post
     *
     * @param Zend_Db_Adapter_Abstract $db
     * @param array $options
     * @return array Default_Model_DbTable_BlogPostImage
     */
    public static function GetAudio($db, $options = array()) {
        // initialize the options
        $defaults = array('post_id' => array());

        foreach ($defaults as $k => $v) {
            $options[$k] = array_key_exists($k, $options) ? $options[$k] : $v;
        }

        $select = $db->select();
        $select->from(array('au' => 'blog_posts_audio'), array('au.*'));

        // filter results on specified post ids (if any)
        if (count($options['post_id']) > 0)
            $select->where('au.post_id in (?)', $options['post_id']);

        $select->order('au.ranking');

        // fetch post data from database
        $data = $db->fetchAll($select);

        // turn data into array of BlogPostAudio objects
        $audio = parent::BuildMultiple($db, __CLASS__, $data);

        return $audio;
    }

    /**
     * Get an array of audio for post
     *
     * @param Zend_Db_Adapter_Abstract $db
     * @param array $options
     * @return array Default_Model_DbTable_BlogPostAudio
     */
    public static function GetAudio_Array($db, $options = array()) {
        // initialize the options
        $defaults = array('post_id' => array());

        foreach ($defaults as $k => $v) {
            $options[$k] = array_key_exists($k, $options) ? $options[$k] : $v;
        }

        $select = $db->select();
        $select->from(array('au' => 'blog_posts_audio'), array('au.*'));

        // filter results on specified post ids (if any)
        if (count($options['post_id']) > 0)
            $select->where('au.post_id in (?)', $options['post_id']);

        $select->order('au.ranking');

        $strSelect = $select->__toString();

        // fetch post data from database
        $data = $db->fetchAll($select);

        // turn data into array of BlogPostAudio objects
        $audio = parent::BuildMultiple_Array($db, __CLASS__, $data);

        return $audio;
    }

    /**
     * Get the total number of records satisfying the criteria specified in the parameter $options
     *
     * @param Zend_Db_Adapter_Abstract $db
     * @param array $options
     * @return int
     */
    public static function GetPostsAudio_Count($db, $options) {
        $defaults = array('post_id' => array());

        foreach ($defaults as $k => $v) {
            $options[$k] = array_key_exists($k, $options) ? $options[$k] : $v;
        }

        $select = $db->select();
        $select->from(array('blog_posts_audio'), 'count(*)');

        // filter results on specified post ids (if any)
        if (count($options['post_id']) > 0)
            $select->where('post_id in (?)', $options['post_id']);

        return $db->fetchOne($select);
    }
}