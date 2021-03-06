<?php

/**
 * Default_Model_DbTable_BlogPostImage
 *
 * Table - to work with images
 *
 *
 * @uses       Default_Model_DatabaseObject
 * @package    Module-Default
 * @subpackage Models
 * @author   Sergii Beskorovainyi <bsa2657@yandex.ru>
 * @license  MIT <http://www.opensource.org/licenses/mit-license.php>
 * @link     https://github.com/bsa-git/zf-myblog/
 */
class Default_Model_DbTable_BlogPostImage extends Default_Model_DatabaseObject {

    /**
     *
     * Table config
     * @var array
     */
    private $_config = array(
        'name' => 'blog_posts_images',
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

    //================ КОНСТРУКТОР КЛАССА ============

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
        $this->add('comment');
    }

    //================ HANDLING OF EVENTS ============

    /**
     * Event before inserting the record
     *
     * @return bool
     */
    public function preInsert() {
        // first check that we can write the upload directory
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

        $pattern = sprintf('%s/%d.*',
                        self::GetThumbnailPath(),
                        $this->getId());

        // ф-ия glob Находит файловые пути, совпадающие с шаблоном
        foreach (glob($pattern) as $thumbnail) {
            unlink($thumbnail);
        }

        return true;
    }

    //================ WORKING WITH RECORD ============

    /**
     * Get image data for a specific post
     *
     * @param int $post_id
     * @param int $image_id
     * @return <type>
     */
    public function loadForPost($post_id, $image_id) {
        $post_id = (int) $post_id;
        $image_id = (int) $image_id;

        if ($post_id <= 0 || $image_id <= 0)
            return false;

        $query = sprintf(
                        'select %s from %s where post_id = %d and id = %d',
                        join(', ', $this->getSelectFields()),
                        $this->_table,
                        $post_id,
                        $image_id
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
     * Create thumbnail image and get path to this miniature
     *
     * @param int $maxW
     * @param int $maxH
     * @param string $username
     * @return string
     */
    public function createThumbnail($maxW, $maxH, $username='') {
        $fullpath = $this->getFullpath($username);

        $info = getImageSize($fullpath);
        if ($info === FALSE) {
            throw new Exception('Invalid image path');
        }

        $ts = (int) filemtime($fullpath);
        $ts = date('Y-m-d', $ts);


        $w = $info[0];          // original width
        $h = $info[1];          // original height

        $ratio = $w / $h;       // width:height ratio

        $maxW = min($w, $maxW); // new width can't be more than $maxW
        if ($maxW == 0)         // check if only max height has been specified
            $maxW = $w;

        $maxH = min($h, $maxH); // new height can't be more than $maxH
        if ($maxH == 0)         // check if only max width has been specified
            $maxH = $h;

        $newW = $maxW;          // first use the max width to determine new
        $newH = $newW / $ratio; // height by using original image w:h ratio

        if ($newH > $maxH) {        // check if new height is too big, and if
            $newH = $maxH;          // so determine the new width based on the
            $newW = $newH * $ratio; // max height
        }

        if ($w == $newW && $h == $newH) {
            // no thumbnail required, just return the original path
            $imageURL = self::getImageURL($fullpath,$username, FALSE);
            return $imageURL;
        }

        switch ($info[2]) {
            case IMAGETYPE_GIF:
                $infunc = 'ImageCreateFromGif';
                $outfunc = 'ImageGif';
                $ext = 'gif';
                break;

            case IMAGETYPE_JPEG:
                $infunc = 'ImageCreateFromJpeg';
                $outfunc = 'ImageJpeg';
                $ext = 'jpg';
                break;

            case IMAGETYPE_PNG:
                $infunc = 'ImageCreateFromPng';
                $outfunc = 'ImagePng';
                $ext = 'png';
                break;

            default;
                throw new Exception('Invalid image type');
        }

        // create a unique filename based on the specified options
        $filename = sprintf('%d.%dx%d.%s.%s',
                        $this->getId(),
                        $newW,
                        $newH,
                        $ts,
                        $ext);

        // autocreate the directory for storing thumbnails
        $path = self::GetThumbnailPath($username);
        if (!file_exists($path))
            mkdir($path, 0777);

        if (!is_writable($path))
            throw new Exception($this->Translate("Путь к папке для создания миниатюр изображений защищен от записи"));

        // determine the full path for the new thumbnail
        $thumbPath = sprintf('%s/%s', $path, $filename);

        if (!file_exists($thumbPath)) {

            // read the image in to GD
            $im = @$infunc($fullpath);
            if (!$im)
                throw new Exception($this->Translate('Невозможно прочитать изображение'));

            // create the output image
            $thumb = ImageCreateTrueColor($newW, $newH);

            // now resample the original image to the new image
            ImageCopyResampled($thumb, $im, 0, 0, 0, 0, $newW, $newH, $w, $h);

            $outfunc($thumb, $thumbPath);
        }

        if (!file_exists($thumbPath))
            throw new Exception($this->Translate('Неизвестная ошибка появилась, в результате создания миниатюры'));
        if (!is_readable($thumbPath))
            throw new Exception($this->Translate('Невозможно прочитать миниатюру изображение'));

        $imageURL = self::getImageURL($thumbPath,$username, TRUE);
        
        return $imageURL;
    }

    /**
     * Get the full path to the file and the file name is replaced by its unique ID code from the database
     *
     * @return string
     */
    public function getFullPath($username='') {
        $fileInfo = pathinfo($this->filename);
        return sprintf('%s/%s', self::GetUploadPath($username), $this->getId() . '.' . $fileInfo['extension']);
    }

    /**
     * Get the URL to the image by its physical path and name of the user
     *
     * @param string $imagePath
     * @param string $username
     * @return string
     */
    static function getImageURL($imagePath,$username = '', $isThumbnail = true) {
        if($isThumbnail){
            $imageURL = self::GetThumbnailURL($username);
        }else{
            $imageURL = self::GetUploadUrl($username);
        }
        
        $path_parts = pathinfo($imagePath);
        $imageURL = $imageURL . '/' . $path_parts['basename'];
        $baseURL = Default_Plugin_SysBox::getBaseURL();
        if($baseURL !== '/'){
            $imageURL = $baseURL . '/' . $imageURL;
        }
        return $imageURL;
    }

    /**
     * Get Hash code of the image
     *
     * @param int $id
     * @param int $w
     * @param int $h
     * @return string
     */
    public static function GetImageHash($id, $w, $h) {
        $id = (int) $id;
        $w = (int) $w;
        $h = (int) $h;

        return md5(sprintf('%s,%s,%s', $id, $w, $h));
    }

    /**
     * Get image storage path for a specific user
     *
     * @return string
     */
    public static function GetUploadPath($aUsername='') {
        // Определим путь загрузки изображений
        $config = Zend_Registry::get('config');
        $patch = $config['paths']['upload']['dir'];
        $patch = trim($patch, '/');

        if ($aUsername) {
            $patch = $patch . '/' . $aUsername . '/images';
        } else {
            $auth = Zend_Auth::getInstance();
            if ($auth->hasIdentity()) {
                $identity = $auth->getIdentity();
                $username = new Default_Plugin_String($identity->username);
                $username = (string) $username->translit();
                $patch = $patch . '/' . $username . '/images';
            } else {
                $patch = $patch . '/guest/images';
            }
        }
        return $patch;
    }

    /**
     * Get path to file thumbnails
     *
     * @return string
     */
    public static function GetThumbnailPath($aUsername='') {
        $config = Zend_Registry::get('config');
        $patch = $config['paths']['upload']['dir'];
        $patch = trim($patch, '/');

        if ($aUsername) {
            $patch = $patch . '/' . $aUsername;
        } else {
            $auth = Zend_Auth::getInstance();
            if ($auth->hasIdentity()) {
                $identity = $auth->getIdentity();
                $username = new Default_Plugin_String($identity->username);
                $username = (string) $username->translit();
                $patch = $patch . '/' . $username;
            } else {
                $patch = $patch . '/guest';
            }
        }
        return $patch . '/.thumbs/images';
    }
    
    /**
     * Get URL to file thumbnails
     *
     * @return string
     */
    public static function GetThumbnailURL($aUsername='') {
        $config = Zend_Registry::get('config');
        $patch = $config['paths']['upload']['url'];
        $patch = trim($patch, '/');

        if ($aUsername) {
            $patch = $patch . '/' . $aUsername;
        } else {
            $auth = Zend_Auth::getInstance();
            if ($auth->hasIdentity()) {
                $identity = $auth->getIdentity();
                $username = new Default_Plugin_String($identity->username);
                $username = (string) $username->translit();
                $patch = $patch . '/' . $username;
            } else {
                $patch = $patch . '/guest';
            }
        }
        return $patch . '/.thumbs/images';
    }
    
       
    /**
     * Get the full URL to the resource
     * ex. http://localhost:8080/zf-myblog/public/upload/users/user1/images/10.png
     *
     * @return string
     */
    public function getFullUrl_Res($username = '') {
        return Default_Plugin_SysBox::getFullURL_Res($this->getFullUrl($username));
    }

    /**
     * Get the URL to the resource
     * ex. /upload/users/user1/images/10.png
     *
     * @return string
     */
    public function getFullUrl($username = '') {
        $fileInfo = pathinfo($this->filename);
        return sprintf('%s/%s', self::GetUploadUrl($username), $this->getId() . '.' . $fileInfo['extension']);
    }

    /**
     * Get the URL storing images for a specific user
     * ex. /upload/users/user1/images
     *
     * @return string
     */
    public static function GetUploadUrl($aUsername = '') {
        // Определим путь загрузки изображений
        $config = Zend_Registry::get('config');
        $patch = $config['paths']['upload']['url'];
        $patch = trim($patch, '/');

        if ($aUsername) {
            $patch = $patch . '/' . $aUsername . '/images';
        } else {
            $auth = Zend_Auth::getInstance();
            if ($auth->hasIdentity()) {
                $identity = $auth->getIdentity();
                $username = new Default_Plugin_String($identity->username);
                $username = (string) $username->translit();
                $patch = $patch . '/' . $username . '/images';
            } else {
                $patch = $patch . '/guest/images';
            }
        }
        return $patch;
    }

    /**
     * Get an array of image objects for post
     *
     * @param Zend_Db_Adapter_Abstract $db
     * @param array $options
     * @return array Default_Model_DbTable_BlogPostImage
     */
    public static function GetImages($db, $options = array()) {
        // initialize the options
        $defaults = array('post_id' => array());

        foreach ($defaults as $k => $v) {
            $options[$k] = array_key_exists($k, $options) ? $options[$k] : $v;
        }

        $select = $db->select();
        $select->from(array('i' => 'blog_posts_images'), array('i.*'));

        // filter results on specified post ids (if any)
        if (count($options['post_id']) > 0)
            $select->where('i.post_id in (?)', $options['post_id']);

        $select->order('i.ranking');

        // fetch post data from database
        $data = $db->fetchAll($select);

        // turn data into array of DatabaseObject_BlogPostImage objects
        $images = parent::BuildMultiple($db, __CLASS__, $data);

        return $images;
    }

    /**
     * Get an array of image for post
     *
     * @param Zend_Db_Adapter_Abstract $db
     * @param array $options
     * @return array Default_Model_DbTable_BlogPostImage
     */
    public static function GetImages_Array($db, $options = array()) {
        // initialize the options
        $defaults = array('post_id' => array());

        foreach ($defaults as $k => $v) {
            $options[$k] = array_key_exists($k, $options) ? $options[$k] : $v;
        }

        $select = $db->select();
        $select->from(array('i' => 'blog_posts_images'), array('i.*'));

        // filter results on specified post ids (if any)
        if (count($options['post_id']) > 0)
            $select->where('i.post_id in (?)', $options['post_id']);

        $select->order('i.ranking');

        $strSelect = $select->__toString();

        // fetch post data from database
        $data = $db->fetchAll($select);

        // turn data into array of DatabaseObject_BlogPostImage objects
        $images = parent::BuildMultiple_Array($db, __CLASS__, $data);

        return $images;
    }

    /**
     * Get the total number of records satisfying the criteria specified in the parameter $options
     *
     * @param Zend_Db_Adapter_Abstract $db
     * @param array $options
     * @return int
     */
    public static function GetPostsImages_Count($db, $options) {
        $defaults = array('post_id' => array());

        foreach ($defaults as $k => $v) {
            $options[$k] = array_key_exists($k, $options) ? $options[$k] : $v;
        }

        $select = $db->select();
        $select->from(array('blog_posts_images'), 'count(*)');

        // filter results on specified post ids (if any)
        if (count($options['post_id']) > 0)
            $select->where('post_id in (?)', $options['post_id']);

        return $db->fetchOne($select);
    }
}