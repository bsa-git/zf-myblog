<?php

/**
 * Default_Model_DbTable_BlogPostAudio
 *
 * Таблица - для работы с музыкальными файлами
 *
 *
 * @uses       Default_Model_DatabaseObject
 * @package    Module-Default
 * @subpackage Models
 */
class Default_Model_DbTable_BlogPostAudio extends Default_Model_DatabaseObject {

    /**
     *
     * Конфигурация таблицы
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
     * Загруженный файл - путь к файлу
     *
     * @var string
     */
    protected $_uploadedFile;

    //================ КОНСТРУКТОР КЛАССА ============

    /**
     * Конструктор обьекта таблицы
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

    //================ ОБРАБОТКА СОБЫТИЙ ============

    /**
     * Событие перед вставкой записи
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
     * Событие после вставки записи
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
     * Событие перед удалением записи
     *
     * @return bool
     */
    public function preDelete() {
        unlink($this->getFullPath());
        return true;
    }

    //================ РАБОТА С ФАЙЛАМИ ============

    /**
     * Загрузить данные муз. файла для конкретного сообщения
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
     * Проверка загруженного файла
     * что это файл, что он существует, что его можно читать
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
     * Получить полный путь к файлу
     * причем имя файла заменяется на его уникальный код ID
     * из базы данных
     *
     * @return string
     */
    public function getFullPath($username='') {
        $fileInfo = pathinfo($this->filename);
        return sprintf('%s/%s', self::GetUploadPath($username), $this->getId() . '.' . $fileInfo['extension']);
    }

    
    /**
     * Получить путь хранения муз. файлов для
     * конкретного пользователя
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
     * Получить полный URL к ресурсу
     *
     * @return string
     */
    public function getFullUrl_Res($username='') {
        return  Default_Plugin_SysBox::getFullURL_Res($this->getFullUrl($username));
    }
    
    /**
     * Получить полный URL к файлу
     * причем имя файла заменяется на его уникальный код ID
     * из базы данных
     *
     * @return string
     */
    public function getFullUrl($username='') {
        $fileInfo = pathinfo($this->filename);
        return sprintf('%s/%s', self::GetUploadUrl($username), $this->getId() . '.' . $fileInfo['extension']);
    }
    
    /**
     * Получить URL хранения муз. файлов для
     * конкретного пользователя
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
     * Получить массив обьектов музыки для статьи
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
     * Получить массив муз.файлов для сообщения
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
     * Получить общее кол. записей удовлетворяющих
     * критерия, заданным в парметре $options
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

?>