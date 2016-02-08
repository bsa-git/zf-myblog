<?php

/**
 * Default_Model_DbTable_BlogPostVideo
 *
 * Таблица - для работы с видео файлами
 *
 *
 * @uses       Default_Model_DatabaseObject
 * @package    Module-Default
 * @subpackage Models
 */
class Default_Model_DbTable_BlogPostVideo extends Default_Model_DatabaseObject {

    /**
     *
     * Конфигурация таблицы
     * @var array
     */
    private $_config = array(
        'name' => 'blog_posts_video',
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
        $this->add('type');
        $this->add('identifier');
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


        // Проверим возможность записи в директорию загрузки
        $arrType = explode('-', $this->type);

        if ($arrType[0] == 'file') {
            // first check that we can write to upload directory
            $path = self::GetUploadPath();
            if (!file_exists($path) || !is_dir($path))
                throw new Exception($this->Translate("Путь к загруженному файлу '%s' не найден", $path));

            if (!is_writable($path))
                throw new Exception($this->Translate("Путь к загруженному файлу '%s' защищен от записи", $path));
        }

        try {
            // now determine the ranking of the new image
            $query = sprintf(
                    'select coalesce(max(ranking), 0) + 1 from %s where post_id = %d', $this->_table, $this->post_id
            );

            $this->ranking = $this->_db->fetchOne($query);
            return TRUE;
        } catch (Exception $exc) {
            return FALSE;
        }
    }

    /**
     * Событие после вставки записи
     *
     * @return bool
     */
    public function postInsert() {

        $arrType = explode('-', $this->type);

        if ($arrType[0] == 'url') {
            return true;
        }

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

        // Если это указатель на URL ресурса, то ничего не делаем
        $arrType = explode('-', $this->type);
        if ($arrType[0] == 'url') {
            return true;
        }

        try {
            // Удалим ресурс видео
            $fullPath = $this->getFullPath();
            if (file_exists($fullPath) && is_file($fullPath)) {
                unlink($fullPath);
            }

            // Удалим файл конфигурации json, если он существует 
            $path_json = self::GetUploadPath() . '/' . $this->getId() . '.json';
            if (file_exists($path_json) && is_file($path_json)) {
                unlink($path_json);
            }
            return true;
        } catch (Exception $exc) {
            return false;
        }
    }

    //================ РАБОТА С ФАЙЛАМИ ============

    /**
     * Загрузить данные видео файла для конкретного сообщения
     *
     * @param int $post_id
     * @param int $video_id
     * @return <type>
     */
    public function loadForPost($post_id, $video_id) {
        $post_id = (int) $post_id;
        $video_id = (int) $video_id;

        if ($post_id <= 0 || $video_id <= 0)
            return false;

        $query = sprintf(
                'select %s from %s where post_id = %d and id = %d', join(', ', $this->getSelectFields()), $this->_table, $post_id, $video_id
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
     * C:/XamppServers3/htdocs/zf-myblog/public/upload/users/user1/files/video/27.mp4
     *
     * @return string
     */
    public function getFullPath($username = '') {
        $fileInfo = pathinfo($this->identifier);
        return sprintf('%s/%s', self::GetUploadPath($username), $this->getId() . '.' . $fileInfo['extension']);
    }

    /**
     * Получить путь хранения видеофайлов для
     * конкретного пользователя
     * C:/XamppServers3/htdocs/zf-myblog/public/upload/users/user1/files/video
     *
     * @return string
     */
    public static function GetUploadPath($aUsername = '') {
        // Определим путь загрузки изображений
        $config = Zend_Registry::get('config');
        $patch = $config['paths']['upload']['dir'];
        $patch = trim($patch, '/');

        if ($aUsername) {
            $patch = $patch . '/' . $aUsername . '/files/video';
        } else {
            $auth = Zend_Auth::getInstance();
            if ($auth->hasIdentity()) {
                $identity = $auth->getIdentity();
                $username = new Default_Plugin_String($identity->username);
                $username = (string) $username->translit();
                $patch = $patch . '/' . $username . '/files/video';
            } else {
                $patch = $patch . '/guest/files/video';
            }
        }
        return $patch;
    }

    /**
     * Получить полный URL к ресурсу
     * пр. http://localhost:8080/zf-myblog/public/upload/users/user1/files/video/10.wmv
     *
     * @return string
     */
    public function getFullUrl_Res($username = '') {
        return Default_Plugin_SysBox::getFullURL_Res($this->getFullUrl($username));
    }

    /**
     * Получить полный URL к файлу
     * причем имя файла заменяется на его уникальный код ID из базы данных
     * пр. /upload/users/user1/files/video/10.wmv
     *
     * @return string
     */
    public function getFullUrl($username = '') {
        $fileInfo = pathinfo($this->identifier);
        return sprintf('%s/%s', self::GetUploadUrl($username), $this->getId() . '.' . $fileInfo['extension']);
    }

    /**
     * Получить URL хранения видеофайлов для
     * конкретного пользователя
     * пр. /upload/users/user1/files/video
     *
     * @return string
     */
    public static function GetUploadUrl($aUsername = '') {
        // Определим путь загрузки изображений
        $config = Zend_Registry::get('config');
        $patch = $config['paths']['upload']['url'];
        $patch = trim($patch, '/');

        if ($aUsername) {
            $patch = $patch . '/' . $aUsername . '/files/video';
        } else {
            $auth = Zend_Auth::getInstance();
            if ($auth->hasIdentity()) {
                $identity = $auth->getIdentity();
                $username = new Default_Plugin_String($identity->username);
                $username = (string) $username->translit();
                $patch = $patch . '/' . $username . '/files/video';
            } else {
                $patch = $patch . '/guest/files/video';
            }
        }
        return $patch;
    }

    /**
     * Получить URL хранения Flash файлов для
     * конкретного пользователя
     * пр. /upload/users/user1/flash
     *
     * @return string
     */
    public static function GetUploadUrlForFlash($aUsername = '') {
        // Определим путь загрузки изображений
        $config = Zend_Registry::get('config');
        $patch = $config['paths']['upload']['url'];
        $patch = trim($patch, '/');

        if ($aUsername) {
            $patch = $patch . '/' . $aUsername . '/flash';
        } else {
            $auth = Zend_Auth::getInstance();
            if ($auth->hasIdentity()) {
                $identity = $auth->getIdentity();
                $username = new Default_Plugin_String($identity->username);
                $username = (string) $username->translit();
                $patch = $patch . '/' . $username . '/flash';
            } else {
                $patch = $patch . '/guest/flash';
            }
        }
        return $patch;
    }

    /**
     * Получить массив обьектов видео для статьи
     *
     * @param Zend_Db_Adapter_Abstract $db
     * @param array $options
     * @return array Default_Model_DbTable_BlogPostImage
     */
    public static function GetVideo($db, $options = array()) {
        // initialize the options
        $defaults = array('post_id' => array());

        foreach ($defaults as $k => $v) {
            $options[$k] = array_key_exists($k, $options) ? $options[$k] : $v;
        }

        $select = $db->select();
        $select->from(array('v' => 'blog_posts_video'), array('v.*'));

        // filter results on specified post ids (if any)
        if (count($options['post_id']) > 0)
            $select->where('v.post_id in (?)', $options['post_id']);

        $select->order('v.ranking');

        // fetch post data from database
        $data = $db->fetchAll($select);

        // turn data into array of BlogPostAudio objects
        $video = parent::BuildMultiple($db, __CLASS__, $data);

        return $video;
    }

    /**
     * Получить массив  видеофайлов для сообщения
     *
     * @param Zend_Db_Adapter_Abstract $db
     * @param array $options
     * @return array Default_Model_DbTable_BlogPostAudio
     */
    public static function GetVideo_Array($db, $options = array()) {
        // initialize the options
        $defaults = array('post_id' => array());

        foreach ($defaults as $k => $v) {
            $options[$k] = array_key_exists($k, $options) ? $options[$k] : $v;
        }

        $select = $db->select();
        $select->from(array('v' => 'blog_posts_video'), array('v.*'));

        // filter results on specified post ids (if any)
        if (count($options['post_id']) > 0)
            $select->where('v.post_id in (?)', $options['post_id']);

        $select->order('v.ranking');

        $strSelect = $select->__toString();

        // fetch post data from database
        $data = $db->fetchAll($select);

        // turn data into array of BlogPostAudio objects
        $video = parent::BuildMultiple_Array($db, __CLASS__, $data);

        return $video;
    }

    /**
     * Получить общее кол. записей удовлетворяющих
     * критерия, заданным в парметре $options
     *
     * @param Zend_Db_Adapter_Abstract $db
     * @param array $options
     * @return int
     */
    public static function GetPostsVideo_Count($db, $options) {
        $defaults = array('post_id' => array());

        foreach ($defaults as $k => $v) {
            $options[$k] = array_key_exists($k, $options) ? $options[$k] : $v;
        }

        $select = $db->select();
        $select->from(array('blog_posts_video'), 'count(*)');

        // filter results on specified post ids (if any)
        if (count($options['post_id']) > 0)
            $select->where('post_id in (?)', $options['post_id']);

        return $db->fetchOne($select);
    }

}

?>