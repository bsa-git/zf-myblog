<?php

/**
 * Default_Model_DbTable_BlogPost
 * 
 * Table - blog posts
 *
 *
 * @uses       Default_Model_DatabaseObject
 * @package    Module-Default
 * @subpackage Models
 * @author   Sergii Beskorovainyi <bsa2657@yandex.ru>
 * @license  MIT <http://www.opensource.org/licenses/mit-license.php>
 * @link     https://github.com/bsa-git/zf-myblog/
 */
class Default_Model_DbTable_BlogPost extends Default_Model_DatabaseObject {

    /**
     * Table config
     * @var array
     */
    private $_config = array(
        'name' => 'blog_posts',
        'primary' => 'id',
        'dependentTables' => array('Default_Model_DbTable_BlogPostProfile'),
        'referenceMap' => array(
            'colums' => array('user_id'),
            'refTableClass' => 'Default_Model_DbTable_User',
            'refColums' => 'id',
            'onDelete' => self::CASCADE,
            'onUpdate' => self::CASCADE,)
    );

    /**
     * 
     * Table object for additional information
     * 
     * @var Default_Model_DbTable_BlogPostProfile
     */
    public $profile = null;

    /**
     *
     * Array image objects
     *
     * @var Default_Model_DbTable_BlogPostImage
     */
    public $images = array();

    /**
     *
     * Array audio objects
     *
     * @var Default_Model_DbTable_BlogPostAudio
     */
    public $audio = array();

    /**
     *
     * Array video objects
     *
     * @var Default_Model_DbTable_BlogPostVideo
     */
    public $video = array();

    /**
     *
     * Array location objects
     *
     * @var Default_Model_DbTable_BlogPostLocation
     */
    public $locations = array();
    
    /**
     *
     * Array comments objects
     *
     * @var Default_Model_DbTable_BlogPostComment
     */
    public $comments = array();

    /**
     * const - post status (draft)
     */

    const STATUS_DRAFT = 'D';

    /**
     * const - post status (live)
     */
    const STATUS_LIVE = 'L';

    //============================

    /**
     * Constructor
     * 
     * @param Zend_Db_Adapter_Abstract $db
     */
    public function __construct($db) {
        $this->_config['db'] = $db;
        parent::__construct($this->_config);

        $this->add('user_id');
        $this->add('url');
        $this->add('ts_created', time(), self::TYPE_TIMESTAMP);
        $this->add('status', self::STATUS_DRAFT);
        $this->add('actual', 1, self::TYPE_BOOLEAN);

        $this->profile = new Default_Model_DbTable_BlogPostProfile($db);
    }

    //================ HANDLING OF EVENTS ============

    /**
     * Event before inserting the record
     *
     * @return bool
     */
    protected function preInsert() {
        $this->url = $this->generateUniqueUrl($this->profile->title);
        return true;
    }

    /**
     * Event after inserting the record
     * 
     * @return bool 
     */
    protected function postInsert() {
        $this->profile->setPostId($this->getId());

        // Удалим базовый путь из URL ресурсов
        if ($this->filterContent('save')) {

            $this->profile->save(false);
            $this->addToIndex();
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * Event after loaded the record
     *
     */
    protected function postLoad() {
        $this->profile->setPostId($this->getId());
        $this->profile->load();

        // Добавим базовый путь к URL ресурсам на странице HTML
        $this->filterContent('load');

        $options = array(
            'post_id' => array($this->getId())
        );
        $this->images = Default_Model_DbTable_BlogPostImage::GetImages(
                        $this->getDb(), $options);

        $this->audio = Default_Model_DbTable_BlogPostAudio::GetAudio(
                        $this->getDb(), $options);

        $this->video = Default_Model_DbTable_BlogPostVideo::GetVideo(
                        $this->getDb(), $options);

        $this->locations = Default_Model_DbTable_BlogPostLocation::GetLocations(
                        $this->getDb(), $options);

        $this->comments = Default_Model_DbTable_BlogPostComment::GetComments(
                        $this->getDb(), $options);
    }

    /**
     * Event after updated the record
     *
     * @return bool
     */
    protected function postUpdate() {

        // Удалим базовый путь из URL ресурсов
        if ($this->filterContent('save')) {

            $this->profile->save(false);
            $this->addToIndex();
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * Event before deleting the record
     *
     * @return bool
     */
    protected function preDelete() {

        // Удалим все теги
        $result = $this->deleteAllTags();
        if (!$result) {
            return FALSE;
        }
        // Удалим все изображения
        foreach ($this->images as $image) {
            $result = $image->delete(false);
            if (!$result) {
                return FALSE;
            }
        }

        // Удалим все аудио
        foreach ($this->audio as $audio) {
            $result = $audio->delete(false);
            if (!$result) {
                return FALSE;
            }
        }

        // Удалим все видео
        foreach ($this->video as $video) {
            $result = $video->delete(false);
            if (!$result) {
                return FALSE;
            }
        }

        // Удалим все геокоординаты
        foreach ($this->locations as $location) {
            $result = $location->delete(false);
            if (!$result) {
                return FALSE;
            }
        }
        
        // Удалим все комментарии
        foreach ($this->comments as $comment) {
            $result = $comment->delete(false);
            if (!$result) {
                return FALSE;
            }
        }

        // Удалим профайл сообщения
        $result = $this->profile->delete();

        return $result;
    }

    /**
     * Event after deleting the record
     *
     * @return bool
     */
    protected function postDelete() {
        $this->deleteFromIndex();
        return TRUE;
    }

    //=========== WORKING WITH RECORD =============

    /**
     * Get specific record for a specific user
     * 
     * @param int $user_id
     * @param int $post_id
     * @return bool 
     */
    public function loadForUser($user_id, $post_id) {
        $post_id = (int) $post_id;
        $user_id = (int) $user_id;

        if ($post_id <= 0 || $user_id <= 0)
            return false;

        $query = sprintf(
                'select %s from %s where user_id = %d and id = %d', join(', ', $this->getSelectFields()), $this->_table, $user_id, $post_id
        );
        return $this->_load($query);
    }

    /**
     * Get specific record for his ID
     * 
     * @param int $post_id
     * @return bool 
     */
    public function loadForPostID($post_id) {
        $post_id = (int) $post_id;

        if ($post_id <= 0)
            return false;

        $query = sprintf(
                'select %s from %s where id = %d', join(', ', $this->getSelectFields()), $this->_table, $post_id
        );
        return $this->_load($query);
    }

    /**
     * Get specific record for a specific user for his address URL
     * 
     * @param int $user_id
     * @param string $url
     * @return bool 
     */
    public function loadLivePost($user_id, $url) {
        $user_id = (int) $user_id;
        $url = trim($url);

        if ($user_id <= 0 || strlen($url) == 0)
            return false;

        $select = $this->_db->select();

        $select->from($this->_table, $this->getSelectFields())
                ->where('user_id = ?', $user_id)
                ->where('url = ?', $url)
                ->where('status = ?', self::STATUS_LIVE);
        return $this->_load($select);
    }

    /**
     * Publish a blog post
     */
    public function sendLive() {
        if ($this->status != self::STATUS_LIVE) {
            $this->status = self::STATUS_LIVE;
            $this->profile->ts_published = date('Y-m-d H:i:s'); //time();
        }
    }

    /**
     * Define whether published a blog post?
     * 
     * @return bool
     */
    public function isLive() {
        return $this->isSaved() && $this->status == self::STATUS_LIVE;
    }

    /**
     * Set a draft mode for blog posts
     */
    public function sendBackToDraft() {
        $this->status = self::STATUS_DRAFT;
    }

    //=============== WORKING WITH TAGS ==========

    /**
     * Obtaining all tags for a blog post
     * 
     * @return array
     */
    public function getTags() {
        if (!$this->isSaved())
            return array();

        $query = 'select tag from blog_posts_tags where post_id = ?';

        // sort tags alphabetically
        $query .= ' order by lower(tag)';

        return $this->_db->fetchCol($query, $this->getId());
    }

    /**
     * Get all labels tags for the desired blog post
     *
     * @return array
     */
    public function getLabels() {
        if (!$this->isSaved())
            return array();

        $query = 'select label from blog_posts_tags where post_id = ?';

        // sort tags alphabetically
        $query .= ' order by lower(label)';

        return $this->_db->fetchCol($query, $this->getId());
    }

    /**
     * Get all labels and tags for the desired blog post
     *
     * @return array
     */
    public function getTagsLabels() {
        if (!$this->isSaved())
            return array();

        $query = 'select tag ,label from blog_posts_tags where post_id = ?';

        $query .= ' order by id';

        return $this->_db->fetchAll($query, $this->getId());
    }

    /**
     * Get label for desired tag
     *
     * @param Zend_Db_Adapter_Abstract $db
     * @return array
     */
    public static function getLabelForTag($db, $tag) {
        $select = $db->select();
        $select->from('blog_posts_tags', 'label')
                ->where('lower(tag) = lower(?)', trim($tag));
        $row = $db->fetchOne($select);
        if ($row) {
            return $row;
        } else {
            return '';
        }
    }

    /**
     * Determine whether tag at a given blog post
     *
     * @param string $tag
     * @return bool
     */
    public function hasTag($tag) {
        if (!$this->isSaved())
            return false;

        $select = $this->_db->select();
        $select->from('blog_posts_tags', 'count(*)')
                ->where('post_id = ?', $this->getId())
                ->where('lower(tag) = lower(?)', trim($tag));

        return $this->_db->fetchOne($select) > 0;
    }

    /**
     * Adding one or more tags to a blog post
     *
     * @param array $tags
     * @return bool
     */
    public function addTags($tags) {
        $addedTags = array();
        //---------------------
        if (!$this->isSaved()) {
            return $addedTags;
        }

        if (!is_array($tags))
            $tags = array($tags);

        // first create a clean list of tags
        $_tags = array();
        foreach ($tags as $tag) {
            $label = trim($tag);
            // Переведем строку к нижнему регистру
//            $label = mb_convert_case($label, MB_CASE_LOWER, "UTF-8");
            if (strlen($label) == 0)
                continue;
            $tag = $this->generateTag($label);
            $_tags[$tag] = $label;
        }

        // now insert each into the database, first ensuring
        // it doesn't already exist for the current post
        //$existingTags = array_map('strtolower', $this->getTags());
        $existingTags = $this->getTags();

        foreach ($_tags as $tag => $label) {
            if (in_array($tag, $existingTags))
                continue;

            $data = array('post_id' => $this->getId(),
                'tag' => $tag,
                'label' => $label);

            $this->_db->insert('blog_posts_tags', $data);

            $this->addToIndex();

            $addedTags[] = $data;
        }
        return $addedTags;
    }

    /**
     * Deleting one or more tags from a blog post
     *
     * @param array $tags
     * @return bool
     */
    public function deleteTags($tags) {
        $result = TRUE;
        //---------------
        if (!$this->isSaved())
            return FALSE;

        if (!is_array($tags))
            $tags = array($tags);

        $_tags = array();
        foreach ($tags as $tag) {
            $tag = trim($tag);
            if (strlen($tag) > 0)
                $_tags[] = strtolower($tag);
        }

        if (count($_tags) == 0)
            return FALSE;

        $where = array('post_id = ' . $this->getId(),
            $this->_db->quoteInto('lower(tag) in (?)', $tags));

        $result = (bool) $this->_db->delete('blog_posts_tags', $where);
        if ($result) {
            $this->addToIndex();
        }
        return $result;
    }

    /**
     * Deleting all tags from a blog post
     *
     * @return bool
     */
    public function deleteAllTags() {
        $result = TRUE;
        //---------------
        if (!$this->isSaved())
            return FALSE;
        $tags = $this->getTags();
        if (count($tags)) {
            $result = (bool) $this->_db->delete('blog_posts_tags', 'post_id = ' . $this->getId());
        }
        return $result;
    }

    /**
     * Get a list of suggested tags to a request entered by the user
     * 
     * @param Zend_Db_Adapter_Abstract $db
     * @param string $partialTag //request entered by the user
     * @param int $limit // count records
     * @return array 
     */
    public static function GetTagSuggestions($db, $partialTag, $limit = 0) {
        $partialTag = trim($partialTag);
        if (strlen($partialTag) == 0)
            return array();

        // Переведем строку к нижнему регистру
        $partialTag = mb_convert_case($partialTag, MB_CASE_LOWER, "UTF-8");

        $select = $db->select();
        $select->distinct();
        $select->from(array('t' => 'blog_posts_tags'), 'lower(label)')
                ->joinInner(array('p' => 'blog_posts'), 't.post_id = p.id', array())
                ->where('lower(t.label) like lower(?)', $partialTag . '%')
                ->where('p.status = ?', self::STATUS_LIVE)
                ->order('lower(t.label)');


        if ($limit > 0)
            $select->limit($limit);

        return $db->fetchCol($select);
    }

    //============== WORKING WITH RECORDS =================

    /**
     * Get the total number of records grouped for tags for a specific user
     * 
     * @param Zend_Db_Adapter_Abstract $db
     * @param int $user_id
     * если $user_id = 0 это значит запрос идет для всем авторам
     * @return array 
     */
    public static function GetTagSummary($db, $user_id) {
        $summary = FALSE;
        //----------------------

        $select = $db->select();
        $select->from(array('t' => 'blog_posts_tags'), array('count(*) as count', 't.tag as tag', 't.label as label'))
                ->joinInner(array('p' => 'blog_posts'), 'p.id = t.post_id', array())
                ->where('p.status = ?', self::STATUS_LIVE)
                ->where('p.actual = ?', 1)
                ->group('t.tag');

        if ($user_id) { // запрос к конкретному автору
            $select->where('p.user_id = ?', $user_id);
        } else { // запрос ко всем авторам
            // Признак публичного доступа будем проверять только для гостей
            $auth = Zend_Auth::getInstance();
            if (!$auth->hasIdentity()) {

                $select->joinInner(array('u' => 'users_profile'), 'p.user_id = u.user_id', array());
                $select->where('u.profile_key = ?', 'blog_public')
                        ->where('u.profile_value = ?', 1);
            }
        }

        $strSelect = $select->__toString();

        //------ Применить кеширование -------
        $dbCache = Default_Plugin_SysBox::getCache('db');
        if ($dbCache->getOption('caching')) {
            // Получим TAG для кеширования
            $arrItems = array(
                $select
            );
            $strSerialize = serialize($arrItems);
            $tagCache = md5($strSerialize);

            // Очистим кеш
            if (Default_Plugin_SysBox::isCleanCache()) {
                $dbCache->clean(Zend_Cache::CLEANING_MODE_ALL);
            }

            // Получим данные из кеша по тегу $tagCache
            $summary = $dbCache->load($tagCache);
        }

        // проверка, есть ли уже данные в кэше:
        if ($summary === FALSE) {
            $result = $db->query($select);
            $tags = $result->fetchAll();

            $summary = array();

            foreach ($tags as $tag) {
                $_tag = array();
                $_tag['count'] = (int) $tag['count'];
                if (isset($tag['"t"."tag"'])) {
                    $_tag['tag'] = strtolower($tag['"t"."tag"']);
                }
                if (isset($tag['tag'])) {
                    $_tag['tag'] = strtolower($tag['tag']);
                }
                if (isset($tag['"t"."label"'])) {
                    $_tag['label'] = $tag['"t"."label"'];
                }
                if (isset($tag['label'])) {
                    $_tag['label'] = $tag['label'];
                }
                $summary[] = $_tag;
            }

            // Если разрешено кеширование, то сохраним данные в кеше
            if ($dbCache->getOption('caching')) {
                $dbCache->save($summary, $tagCache);
            }
        } else {
            $result = $summary;
        }
        return $summary;
    }

    /**
     * Get the total number of records satisfying the criteria specified in the parameter $options
     * 
     * @param Zend_Db_Adapter_Abstract $db
     * @param array $options
     * @return int 
     */
    public static function GetPostsCount($db, $options) {
        $select = self::_GetBaseQuery($db, $options);
        $select->from(null, 'count(*)');

        return (int) $db->fetchOne($select);
    }

    /**
     * Get the array of records satisfying the criteria specified in the parameter $options
     * 
     * @param Zend_Db_Adapter_Abstract $db
     * @param array $options
     * @return array массив обьектов типа - Default_Model_DbTable_BlogPost 
     */
    public static function GetPosts($db, $options = array()) {
        $posts = FALSE;
        //----------------------
        // инициализация опций
        $defaults = array(
            'offset' => 0,
            'limit' => 0,
            'order' => 'p.ts_created',
            'sort' => true
        );

        foreach ($defaults as $k => $v) {
            $options[$k] = array_key_exists($k, $options) ? $options[$k] : $v;
        }

        $select = self::_GetBaseQuery($db, $options);

        $strSelect = $select->__toString();

        // установим поля таблицы для запроса
        $select->from(null, 'p.*');

        // set the offset, limit, and ordering of results
        if ($options['limit'] > 0)
            $select->limit($options['limit'], $options['offset']);

        // Установим параметры сортировки для таблицы
        if ($options['sort'])
            $select = self::GetSelectForSort($select, $options);

        //------ Применить кеширование -------
        $dbCache = Default_Plugin_SysBox::getCache('db');
        if ($dbCache->getOption('caching')) {
            // Получим TAG для кеширования
            $arrItems = array(
                $select,
                $options
            );
            $strSerialize = serialize($arrItems);
            $tagCache = md5($strSerialize);

            // Очистим кеш
            if (Default_Plugin_SysBox::isCleanCache()) {
                $dbCache->clean(Zend_Cache::CLEANING_MODE_ALL);
            }

            // Получим данные из кеша по тегу $tagCache
            $posts = $dbCache->load($tagCache);
        }

        // проверка, есть ли уже данные в кэше:
        if ($posts === FALSE) {
            // получим результат запроса к базе данных
            $data = $db->fetchAll($select);

            // получим данные в виде массива обьектов Default_Model_DbTable_BlogPost
            $posts = self::BuildMultiple($db, __CLASS__, $data);
            $post_ids = array_keys($posts);

            if (count($post_ids) == 0)
                return array();

            // получим данные о загруженных сообщениях
            $profiles = Default_Model_Profile::BuildMultiple(
                            $db, 'Default_Model_DbTable_BlogPostProfile', array($post_ids)
            );

            foreach ($posts as $post_id => $post) {
                if (array_key_exists($post_id, $profiles)
                        && $profiles[$post_id] instanceof Default_Model_DbTable_BlogPostProfile) {

                    $posts[$post_id]->profile = $profiles[$post_id];
                } else {
                    $posts[$post_id]->profile->setPostId($post_id);
                }

                //!!!!------ Начало установки признака сортировки -----!!!!!
                if (isset($options['sortColumn'])) {
                    $posts[$post_id]->sortColumn = $options['sortColumn'];
                }

                if (isset($options['ascDescFlg'])) {
                    $posts[$post_id]->ascDescFlg = $options['ascDescFlg'];
                }

                //!!!!------ Конец установки признака сортировки -----!!!!!
            }

            // load the images for each post
            $options = array('post_id' => $post_ids);
            $images = Default_Model_DbTable_BlogPostImage::GetImages($db, $options);

            foreach ($images as $image) {
                $posts[$image->post_id]->images[$image->getId()] = $image;
            }

            // load the locations for each post
            $locations = Default_Model_DbTable_BlogPostLocation::GetLocations($db, $options);

            foreach ($locations as $l)
                $posts[$l->post_id]->locations[$l->getId()] = $l;

            // load the audio for each post
            $audios = Default_Model_DbTable_BlogPostAudio::GetAudio($db, $options);

            foreach ($audios as $audio) {
                $posts[$audio->post_id]->audio[$audio->getId()] = $audio;
            }

            // load the video for each post
            $videos = Default_Model_DbTable_BlogPostVideo::GetVideo($db, $options);

            foreach ($videos as $video) {
                $posts[$video->post_id]->video[$video->getId()] = $video;
            }

            // load the comments for each post
            $comments = Default_Model_DbTable_BlogPostComment::GetComments($db, $options);

            foreach ($comments as $comment) {
                $posts[$comment->post_id]->comments[$comment->getId()] = $comment;
            }

            // Если разрешено кеширование, то сохраним данные в кеше
            if ($dbCache->getOption('caching')) {
                $dbCache->save($posts, $tagCache);
            }
        } else {
            $result = $posts;
        }

        return $posts;
    }

    /**
     * Get the array of records satisfying the criteria specified in the parameter $options using Zend_Paginator
     * 
     * @param Zend_Db_Adapter_Abstract $db
     * @param array $options
     * @return array  Default_Model_DbTable_BlogPost objects
     */
    public static function GetPaginatorPosts($db, $options = array()) {
        $arrResult = array();
        $_config = Zend_Registry::get('config');
        $itemCountPerPage = (int) $_config['paginator']['itemCountPerPage'];
        $pagesInRange = (int) $_config['paginator']['pagesInRange'];
        //---------------------------------------------------------
        // инициализация опций
        $defaults = array(
            'itemCountPerPage' => $itemCountPerPage,
            'pagesInRange' => $pagesInRange,
            'page' => 1,
            //------------------------
            'offset' => 0,
            'limit' => 0,
            'order' => 'p.ts_created',
            'sort' => true
        );

        foreach ($defaults as $k => $v) {
            $options[$k] = array_key_exists($k, $options) ? $options[$k] : $v;
        }

        $select = self::_GetBaseQuery($db, $options);

        // установим поля таблицы для запроса
        $select->from(null, 'p.*');

        // set the offset, limit, and ordering of results
        if ($options['limit'] > 0)
            $select->limit($options['limit'], $options['offset']);

        // Установим параметры сортировки для таблицы
        if ($options['sort'])
            $select = self::GetSelectForSort($select, $options);

        //------ Создадим обьект Zend_Paginator ---------
        $strSelect = $select->__toString();
        $adapter = new Zend_Paginator_Adapter_DbSelect($select);
        $count = self::GetPostsCount($db, $options);
        $adapter->setRowCount($count);
        $paginator = new Zend_Paginator($adapter);

        // Установим максимальное количество отображаемых на странице элементов
        $paginator->setItemCountPerPage($options['itemCountPerPage']);

        // Установи массив страниц, возвращенный текущим стилем прокрутки
        $paginator->setPageRange($options['pagesInRange']);

        // Установим текущую страницу
        $paginator->setCurrentPageNumber($options['page']);

        //----- Конфигурирование кеша для Paginator -----
        $pgCache = Default_Plugin_SysBox::getCache('paginator');
        if ($pgCache->getOption('caching')) {

            // Установим кеш для Paginator
            Zend_Paginator::setCache($pgCache);

            // Очищение кеша
            if (Default_Plugin_SysBox::isCleanCache()) {
                $paginator->clearPageItemCache();
            }
        }


        // получим данные в виде массива обьектов Default_Model_DbTable_BlogPost
        $posts = self::BuildMultiple($db, __CLASS__, $paginator);
        $post_ids = array_keys($posts);

        if (count($post_ids) == 0)
            return array();

        // получим данные о загруженных сообщениях
        $profiles = Default_Model_Profile::BuildMultiple(
                        $db, 'Default_Model_DbTable_BlogPostProfile', array($post_ids)
        );

        foreach ($posts as $post_id => $post) {
            if (array_key_exists($post_id, $profiles)
                    && $profiles[$post_id] instanceof Default_Model_DbTable_BlogPostProfile) {

                $posts[$post_id]->profile = $profiles[$post_id];
            } else {
                $posts[$post_id]->profile->setPostId($post_id);
            }

            //!!!!------ Начало установки признака сортировки -----!!!!!
            if (isset($options['sortColumn'])) {
                $posts[$post_id]->sortColumn = $options['sortColumn'];
            }

            if (isset($options['ascDescFlg'])) {
                $posts[$post_id]->ascDescFlg = $options['ascDescFlg'];
            }

            //!!!!------ Конец установки признака сортировки -----!!!!!
        }

        // load the images for each post
        $options = array('post_id' => $post_ids);
        $images = Default_Model_DbTable_BlogPostImage::GetImages($db, $options);

        foreach ($images as $image) {
            $posts[$image->post_id]->images[$image->getId()] = $image;
        }

        // load the locations for each post
        $locations = Default_Model_DbTable_BlogPostLocation::GetLocations($db, $options);

        foreach ($locations as $l)
            $posts[$l->post_id]->locations[$l->getId()] = $l;

        // load the audio for each post
        $audios = Default_Model_DbTable_BlogPostAudio::GetAudio($db, $options);

        foreach ($audios as $audio) {
            $posts[$audio->post_id]->audio[$audio->getId()] = $audio;
        }

        // load the video for each post
        $videos = Default_Model_DbTable_BlogPostVideo::GetVideo($db, $options);

        foreach ($videos as $video) {
            $posts[$video->post_id]->video[$video->getId()] = $video;
        }

        // load the comments for each post
        $comments = Default_Model_DbTable_BlogPostComment::GetComments($db, $options);

        foreach ($comments as $comment) {
            $posts[$comment->post_id]->comments[$comment->getId()] = $comment;
        }

        $arrResult['pages'] = $paginator->getPages();
        $arrResult['items'] = $posts;
        return $arrResult;
    }

    /**
     * Get sorted array profile posts in accordance with the collation settings
     *
     * @param Zend_Db_Adapter_Abstract $db
     * @param array $options
     * @return array
     */
    public static function GetSortProfiles($db, $options) {

        // Получим все записи с установленными признаками желаемой сортировки
        $posts = self::GetPosts($db, $options);
        // Отсортируем массив
        usort($posts, array("Default_Model_DatabaseObject", "_SortProfiles"));

        return $posts;
    }

    /**
     * Get the array of posts satisfying the criteria specified in the parameter $options
     *
     * @param Zend_Db_Adapter_Abstract $db
     * @param array $options
     * @return array
     */
    public static function GetPosts_Array($db, $options = array()) {
        $arrPosts = FALSE;
        $user_ids = array();
        //----------------------
        // initialize the options
        $defaults = array(
            'offset' => 0,
            'limit' => 0,
            'order' => 'p.ts_created',
            'sort' => true
        );

        foreach ($defaults as $k => $v) {
            $options[$k] = array_key_exists($k, $options) ? $options[$k] : $v;
        }

        $select = self::_GetBaseQuery($db, $options);

        // set the fields to select
        $select->from(null, 'p.*');

        // set the offset, limit, and ordering of results
        if ($options['limit'] > 0)
            $select->limit($options['limit'], $options['offset']);

        // Установим параметры сортировки для таблицы
        if ($options['sort'])
            $select = self::GetSelectForSort($select, $options);

        $strSelect = $select->__toString();

        //------ Применить кеширование -------
        $dbCache = Default_Plugin_SysBox::getCache('db');
        if ($dbCache->getOption('caching')) {
            // Получим TAG для кеширования
            $arrItems = array(
                $select,
                $options
            );
            $strSerialize = serialize($arrItems);
            $tagCache = md5($strSerialize);

            // Очистим кеш
            if (Default_Plugin_SysBox::isCleanCache()) {
                $dbCache->clean(Zend_Cache::CLEANING_MODE_ALL);
            }

            // Получим данные из кеша по тегу $tagCache
            $arrPosts = $dbCache->load($tagCache);
        }

        // проверка, есть ли уже данные в кэше:
        if ($arrPosts === FALSE) {
            // fetch user data from database
            $data = $db->fetchAll($select);

            // turn data into array of DatabaseObject_User objects
            $posts = parent::BuildMultiple_Array($db, __CLASS__, $data);

            if (count($posts) == 0)
                return $posts;

            $post_ids = array_keys($posts);

            // load the profile data for loaded posts
            $profiles = Default_Model_Profile::BuildMultiple_Array($db, 'Default_Model_DbTable_BlogPostProfile', array($post_ids));
            $arrPosts = array();
            foreach ($posts as $post_id => $post) {
                if (array_key_exists($post_id, $profiles)) {
                    $arrPosts[$post_id] = $posts[$post_id] + $profiles[$post_id];
                } else {
                    $arrPosts[$post_id] = $posts[$post_id];
                }

                // Получим ids пользователей для всех сообщениий
                $user_ids[] = $post['user_id'];
            }

            // Уберем повторяющиеся значения из массива
            $user_ids = array_unique($user_ids);
            // Получим всех пользователей
            $options = array('user_id' => $user_ids);
            $users = Default_Model_DbTable_User::GetUsers_Array($db, $options);

            // Добавим пользователя для каждого сообщения
            foreach ($posts as $post_id => $post) {
                $postuser_id = $post['user_id'];

                foreach ($users as $user) {
                    $user_id = $user['id'];
                    if ($user_id == $postuser_id) {
                        $arrPosts[$post_id]['_user_'] = $user;
                        break;
                    }
                }
            }



            // Загрузим изображения для каждого сообщения
            $options = array('post_id' => $post_ids);
            $images = Default_Model_DbTable_BlogPostImage::GetImages_Array($db, $options);

            foreach ($images as $image) {
                $post_id = $image['post_id'];
                $image_id = $image['id'];
                $arrPosts[$post_id]['_images_'][$image_id] = $image;
            }

            // Загрузим координаты для каждого сообщения
            $locations = Default_Model_DbTable_BlogPostLocation::GetLocations_Array($db, $options);

            foreach ($locations as $location) {
                $post_id = $location['post_id'];
                $location_id = $location['id'];
                $arrPosts[$post_id]['_locations_'][$location_id] = $location;
            }

            // Загрузим метки для каждого сообщения
            $tags = Default_Model_DbTable_BlogPostTag::GetTags_Array($db, $options);

            foreach ($tags as $tag) {
                $post_id = $tag['post_id'];
                $tag_id = $tag['id'];
                $arrPosts[$post_id]['_tags_'][$tag_id] = $tag;
            }

            // Загрузим audio для каждого сообщения
            $audios = Default_Model_DbTable_BlogPostAudio::GetAudio_Array($db, $options);

            foreach ($audios as $audio) {
                $post_id = $audio['post_id'];
                $audio_id = $audio['id'];
                $arrPosts[$post_id]['_audio_'][$audio_id] = $audio;
            }

            // Загрузим video для каждого сообщения
            $videos = Default_Model_DbTable_BlogPostVideo::GetVideo_Array($db, $options);

            foreach ($videos as $video) {
                $post_id = $video['post_id'];
                $video_id = $video['id'];
                $arrPosts[$post_id]['_video_'][$video_id] = $video;
            }
            
            // Загрузим comments для каждого сообщения
            $comments = Default_Model_DbTable_BlogPostComment::GetComments_Array($db, $options);

            foreach ($comments as $comment) {
                $post_id = $comment['post_id'];
                $comment_id = $comment['id'];
                $arrPosts[$post_id]['_comments_'][$comment_id] = $comment;
            }

            // Если разрешено кеширование, то сохраним данные в кеше
            if ($dbCache->getOption('caching')) {
                $dbCache->save($arrPosts, $tagCache);
            }
        } else {
            $result = $arrPosts;
        }
        return $arrPosts;
    }

    /**
     * Get the array of ids satisfying the criteria specified in the parameter $options
     *
     * @param Zend_Db_Adapter_Abstract $db
     * @param array $options
     * @return array
     */
    public static function GetPostsIds_Array($db, $options = array()) {
        $post_ids = FALSE;
        //----------------------
        // initialize the options
        $defaults = array(
            'offset' => 0,
            'limit' => 0,
            'order' => 'p.ts_created',
            'sort' => true
        );

        foreach ($defaults as $k => $v) {
            $options[$k] = array_key_exists($k, $options) ? $options[$k] : $v;
        }

        $select = self::_GetBaseQuery($db, $options);

        // set the fields to select
        $select->from(null, 'p.*');

        // set the offset, limit, and ordering of results
        if ($options['limit'] > 0)
            $select->limit($options['limit'], $options['offset']);

        // Установим параметры сортировки для таблицы
        if ($options['sort'])
            $select = self::GetSelectForSort($select, $options);

        $strSelect = $select->__toString();

        //------ Применить кеширование -------
        $dbCache = Default_Plugin_SysBox::getCache('db');
        if ($dbCache->getOption('caching')) {
            // Получим TAG для кеширования
            $arrItems = array(
                $select,
                $options
            );
            $strSerialize = serialize($arrItems);
            $tagCache = md5($strSerialize);

            // Очистим кеш
            if (Default_Plugin_SysBox::isCleanCache()) {
                $dbCache->clean(Zend_Cache::CLEANING_MODE_ALL);
            }

            // Получим данные из кеша по тегу $tagCache
            $post_ids = $dbCache->load($tagCache);
        }

        // проверка, есть ли уже данные в кэше:
        if ($post_ids === FALSE) {
            // fetch user data from database
            $data = $db->fetchAll($select);

            // turn data into array of DatabaseObject_User objects
            $posts = parent::BuildMultiple_Array($db, __CLASS__, $data);

            if (count($posts) == 0) {
                $post_ids = array();
            } else {
                $post_ids = array_keys($posts);
            }

            // Если разрешено кеширование, то сохраним данные в кеше
            if ($dbCache->getOption('caching')) {
                $dbCache->save($post_ids, $tagCache);
            }
        } else {
            $result = $post_ids;
        }
        return $post_ids;
    }

    /**
     * Get the number of records for each month within a date range satisfying the criteria specified in the parameter $options
     * 
     * @param Zend_Db_Adapter_Abstract $db
     * @param array $options
     * @return array 
     */
    public static function GetMonthlySummary($db, $options) {
        $arrPosts = FALSE;
        //----------------------
        //Получим отформатированную строку даты
        if ($db instanceof Zend_Db_Adapter_Pdo_Mysql) {
            $dateString = "date_format(p.ts_created, '%Y-%m')";
        } elseif ($db instanceof Zend_Db_Adapter_Pdo_Sqlite) {
            $dateString = "strftime('%Y-%m', p.ts_created)";
        } else {
            $dateString = "to_char(p.ts_created, 'yyyy-mm')";
        }

        // инициализация параметров
        $defaults = array(
            'offset' => 0,
            'limit' => 0,
            'order' => $dateString . ' desc'
        );
        foreach ($defaults as $k => $v) {
            $options[$k] = array_key_exists($k, $options) ? $options[$k] : $v;
        }


        $select = self::_GetBaseQuery($db, $options);
        $select->from(null, array($dateString . ' as month',
            'count(*) as num_posts'));

        $select->group($dateString);

        $select->order($options['order']);

        //------ Применить кеширование -------
        $dbCache = Default_Plugin_SysBox::getCache('db');
        if ($dbCache->getOption('caching')) {
            // Получим TAG для кеширования
            $arrItems = array(
                $select,
                $options
            );
            $strSerialize = serialize($arrItems);
            $tagCache = md5($strSerialize);

            // Очистим кеш
            if (Default_Plugin_SysBox::isCleanCache()) {
                $dbCache->clean(Zend_Cache::CLEANING_MODE_ALL);
            }

            // Получим данные из кеша по тегу $tagCache
            $arrPosts = $dbCache->load($tagCache);
        }
        // проверка, есть ли уже данные в кэше:
        if ($arrPosts === FALSE) {
            $arrPosts = $db->fetchPairs($select);
            // Если разрешено кеширование, то сохраним данные в кеше
            if ($dbCache->getOption('caching')) {
                $dbCache->save($arrPosts, $tagCache);
            }
        } else {
            $result = $arrPosts;
        }
        return $arrPosts;
    }

    /**
     * Get sorted and grouped values from table column
     *
     * @param Zend_Db_Adapter_Abstract $db
     * @param array $options
     * @return array
     */
    public static function GetValuesForCol($db, $options) {
        $arrValues = FALSE;
        //----------------------
        // инициализация параметров
        $defaults = array(
            'offset' => 0,
            'limit' => 0,
            'group' => TRUE,
            'order' => 'p.ts_created'
        );
        foreach ($defaults as $k => $v) {
            $options[$k] = array_key_exists($k, $options) ? $options[$k] : $v;
        }


        $select = self::_GetBaseQuery($db, $options);

        // set the offset, limit, and ordering of results
        if ($options['limit'] > 0)
            $select->limit($options['limit'], $options['offset']);

        // Установим параметры сортировки для таблицы
        $select = self::GetSelectForSort($select, $options);

        // Добавим группировку и соответствующую колонку
        $aliasTable = Default_Model_DatabaseObject::getAliasForTable($select, $options['joinTableForSort']);
        if ($aliasTable) {
            $orderData = $select->getPart(Zend_Db_Select::ORDER);
            if ($options['group']) {
                $select->group($orderData[0][0]);
            }
            // Найдем алиас таблицы, запрашиваемого поля
            $arrAliasTable = explode('_', $aliasTable);
            $select->columns(array($arrAliasTable[0] . '.id', $orderData[0][0]));
        } else {
            if ($options['group']) {
                $select->group('p.' . $options['field']);
            }

            $select->columns(array('p.id', 'p.' . $options['field']));
        }

        $strSelect = $select->__toString();

        //------ Применить кеширование -------
        $dbCache = Default_Plugin_SysBox::getCache('db');
        if ($dbCache->getOption('caching')) {
            // Получим TAG для кеширования
            $arrItems = array(
                $select,
                $options
            );
            $strSerialize = serialize($arrItems);
            $tagCache = md5($strSerialize);

            // Очистим кеш
            if (Default_Plugin_SysBox::isCleanCache()) {
                $dbCache->clean(Zend_Cache::CLEANING_MODE_ALL);
            }

            // Получим данные из кеша по тегу $tagCache
            $arrValues = $dbCache->load($tagCache);
        }
        // проверка, есть ли уже данные в кэше:
        if ($arrValues === FALSE) {
            $arrValues = $db->fetchPairs($select);

            // Если разрешено кеширование, то сохраним данные в кеше
            if ($dbCache->getOption('caching')) {
                $dbCache->save($arrValues, $tagCache);
            }
        } else {
            $result = $arrValues;
        }


        return $arrValues;
    }

    /**
     * Get the query satisfying the criteria specified in the parameter $options
     * 
     * @param Zend_Db_Adapter_Abstract $db
     * @param array $options
     * @return Zend_Db_Select 
     */
    private static function _GetBaseQuery($db, $options) {
        // инициализация параметров
        $defaults = array(
            'post_id' => array(),
            'user_id' => array(),
            'actuals' => array(0, 1),
            'from' => '',
            'to' => '',
            'tag' => '',
            'status' => '',
            'public_only' => FALSE,
            'filter' => array()
        );

        foreach ($defaults as $k => $v) {
            $options[$k] = array_key_exists($k, $options) ? $options[$k] : $v;
        }

        // создадим запрос, который выбирает данные из таблицы blog_posts
        $select = $db->select();
        $select->from(array('p' => 'blog_posts'), array());

        // создадим фильтр по дате
        if (strlen($options['from']) > 0) {
            $ts = strtotime($options['from']);
            $select->where('p.ts_created >= ?', date('Y-m-d H:i:s', $ts));
        }

        if (strlen($options['to']) > 0) {
            $ts = strtotime($options['to']);
            $select->where('p.ts_created <= ?', date('Y-m-d H:i:s', $ts));
        }

        // создадим фильтр по IDs сообщениям
        if (count($options['post_id']) > 0)
            $select->where('p.id in (?)', $options['post_id']);

        // создадим фильтр по пользователям
        if (count($options['user_id']) > 0)
            $select->where('p.user_id in (?)', $options['user_id']);

        // создадим фильтр по разрешению/запрету (актуальности) блогов
        if (count($options['actuals']) > 0)
            $select->where('p.actual in (?)', $options['actuals']);

        // отфильтруем результаты, на основе статуса записи
        if (strlen($options['status']) > 0)
            $select->where('status = ?', $options['status']);

        if ($options['public_only']) {
            $select->joinInner(array('up' => 'users_profile'), 'p.user_id = up.user_id', array())
                    ->where("up.profile_key = 'blog_public'")
                    ->where('up.profile_value = 1');
        }

        $options['tag'] = trim($options['tag']);
        if (strlen($options['tag']) > 0) {
            $select->joinInner(array('t' => 'blog_posts_tags'), 't.post_id = p.id', array())
                    ->where('t.tag = ?', $options['tag']);
        }

        // создадим фильтр для параметров фильтра
        if ($options['filter']) {
            $count = count($options['filter']);
            if ($count > 0) {
                $select = self::GetSelectForFilter($select, $options['filter']);
            }
        }


        return $select;
    }

    //------------- FILTER ------------//

    /**
     * Get Select object (Zend_Db_Select) for filtering table records
     *
     * @param Zend_Db_Select $select
     * @param array $filter         
     *
     * @return Zend_Db_Select
     */
    public static function GetSelectForFilter($select, $filter) {
        $joinTable = '';
        //-----------------------------------
        // создадим фильтр по разрешению/запрету (актуальности) блогов
        // Построим выражения SELECT
        foreach ($filter as $field => $filterParams) {
            $joinTable = $filterParams['joinTable'];
            $aliasTable = Default_Model_DatabaseObject::getAliasForTable($select, $joinTable);
            $filterParams = $filterParams['filterParams'];
            switch ($joinTable) {
                case 'blog_posts_profile':
                    if (!$aliasTable) {
                        $select->joinInner(array('p_profile' => $joinTable), 'p_profile.post_id = p.id', array())
                                ->where('p_profile.profile_key = ?', $field);
                    }

                    foreach ($filterParams as $filterParam) {
                        if (is_array($filterParam)) {
                            $andLogic = (bool) $filterParam['andLogic'];
                            if ($andLogic) {
                                $select->where('p_profile.profile_value ' . $filterParam['compare'] . ' ?', $filterParam['value']);
                            } else {
                                $select->orWhere('p_profile.profile_value ' . $filterParam['compare'] . ' ?', $filterParam['value']);
                            }
                        }
                    }

                    break;
                case 'blog_posts_images':
                    if (!$aliasTable) {
                        $select->joinInner(array('img' => $joinTable), 'img.post_id = p.id', array());
                    }
                    foreach ($filterParams as $filterParam) {
                        if (is_array($filterParam)) {
                            $andLogic = (bool) $filterParam['andLogic'];
                            if ($andLogic) {
                                $select->where('img.' . $field . ' ' . $filterParam['compare'] . ' ?', $filterParam['value']);
                            } else {
                                $select->orWhere('img.' . $field . ' ' . $filterParam['compare'] . ' ?', $filterParam['value']);
                            }
                        }
                    }

                    break;
                case 'blog_posts_audio':
                    if (!$aliasTable) {
                        $select->joinInner(array('au' => $joinTable), 'au.post_id = p.id', array());
                    }
                    foreach ($filterParams as $filterParam) {
                        if (is_array($filterParam)) {
                            $andLogic = (bool) $filterParam['andLogic'];
                            if ($andLogic) {
                                $select->where('au.' . $field . ' ' . $filterParam['compare'] . ' ?', $filterParam['value']);
                            } else {
                                $select->orWhere('au.' . $field . ' ' . $filterParam['compare'] . ' ?', $filterParam['value']);
                            }
                        }
                    }

                    break;
                case 'blog_posts_video':
                    if (!$aliasTable) {
                        $select->joinInner(array('v' => $joinTable), 'v.post_id = p.id', array());
                    }
                    foreach ($filterParams as $filterParam) {
                        if (is_array($filterParam)) {
                            $andLogic = (bool) $filterParam['andLogic'];
                            if ($andLogic) {
                                $select->where('v.' . $field . ' ' . $filterParam['compare'] . ' ?', $filterParam['value']);
                            } else {
                                $select->orWhere('v.' . $field . ' ' . $filterParam['compare'] . ' ?', $filterParam['value']);
                            }
                        }
                    }

                    break;
                case 'blog_posts_lacations':
                    if (!$aliasTable) {
                        $select->joinInner(array('l' => $joinTable), 'l.post_id = p.id', array());
                    }
                    foreach ($filterParams as $filterParam) {
                        if (is_array($filterParam)) {
                            $andLogic = (bool) $filterParam['andLogic'];
                            if ($andLogic) {
                                $select->where('l.' . $field . ' ' . $filterParam['compare'] . ' ?', $filterParam['value']);
                            } else {
                                $select->orWhere('l.' . $field . ' ' . $filterParam['compare'] . ' ?', $filterParam['value']);
                            }
                        }
                    }
                    break;
                case 'blog_posts_tags':
                    if (!$aliasTable) {
                        $select->joinInner(array('t' => $joinTable), 't.post_id = p.id', array());
                    }
                    foreach ($filterParams as $filterParam) {
                        if (is_array($filterParam)) {
                            $andLogic = (bool) $filterParam['andLogic'];
                            if ($andLogic) {
                                $select->where('t.' . $field . ' ' . $filterParam['compare'] . ' ?', $filterParam['value']);
                            } else {
                                $select->orWhere('t.' . $field . ' ' . $filterParam['compare'] . ' ?', $filterParam['value']);
                            }
                        }
                    }

                    break;
                case 'blog_posts_comments':
                    if (!$aliasTable) {
                        $select->joinInner(array('c' => $joinTable), 'c.post_id = p.id', array());
                    }
                    foreach ($filterParams as $filterParam) {
                        if (is_array($filterParam)) {
                            $andLogic = (bool) $filterParam['andLogic'];
                            if ($andLogic) {
                                $select->where('c.' . $field . ' ' . $filterParam['compare'] . ' ?', $filterParam['value']);
                            } else {
                                $select->orWhere('c.' . $field . ' ' . $filterParam['compare'] . ' ?', $filterParam['value']);
                            }
                        }
                    }

                    break;
                case 'users':
                    if (!$aliasTable) {
                        $select->joinInner(array('u' => $joinTable), 'u.id = p.user_id', array());
                    }
                    foreach ($filterParams as $filterParam) {
                        if (is_array($filterParam)) {
                            $andLogic = (bool) $filterParam['andLogic'];
                            if ($andLogic) {
                                $select->where('u.' . $field . ' ' . $filterParam['compare'] . ' ?', $filterParam['value']);
                            } else {
                                $select->orWhere('u.' . $field . ' ' . $filterParam['compare'] . ' ?', $filterParam['value']);
                            }
                        }
                    }

                    break;
                case 'users_profile':
                    if (!$aliasTable) {
                        $select->joinInner(array('u_profile' => $joinTable), 'u_profile.user_id = p.user_id', array())
                                ->where('u_profile.profile_key = ?', $field);
                    }
                    foreach ($filterParams as $filterParam) {
                        if (is_array($filterParam)) {
                            $andLogic = (bool) $filterParam['andLogic'];
                            if ($andLogic) {
                                $select->where('u_profile.value ' . $filterParam['compare'] . ' ?', $filterParam['value']);
                            } else {
                                $select->orWhere('u_profile.value ' . $filterParam['compare'] . ' ?', $filterParam['value']);
                            }
                        }
                    }

                    break;
                default:
                    foreach ($filterParams as $filterParam) {
                        if (is_array($filterParam)) {
                            $andLogic = (bool) $filterParam['andLogic'];
                            if ($andLogic) {
                                $select->where('p.' . $field . ' ' . $filterParam['compare'] . ' ?', $filterParam['value']);
                            } else {
                                $select->orWhere('p.' . $field . ' ' . $filterParam['compare'] . ' ?', $filterParam['value']);
                            }
                        }
                    }
                    break;
            }
        }
        return $select;
    }

    //------------- SORT ------------//

    /**
     * Get Select object (Zend_Db_Select) for sorting table records
     *
     * @param Zend_Db_Select $select
     * @param string $order         
     *
     * @return Zend_Db_Select
     */
    public static function GetSelectForSort($select, $options) {
        $aliasTable = '';
        $order = $options['order'];
        //--------------------------
        $arrOrder = explode('.', $order);

        // Если в параметре сортировки не задан псевдоним таблицы
        // то определим его, и если нужно присоединим,
        // соответствующую таблицу
        if (count($arrOrder) == 1) {
            $joinTableForSort = $options['joinTableForSort'];
            if ($joinTableForSort) {
                // Определим какие таблицы уже присоединены
                $fromTables = $select->getPart(Zend_Db_Select::FROM);
                foreach ($fromTables as $alias => $joinParams) {
                    // Если таблица -> $joinTableForSort уже присоединена
                    // то получим ее псевдоним
                    if ($joinParams['tableName'] == $joinTableForSort) {
                        $aliasTable = $alias;
                    }
                }
                if ($aliasTable) {
                    $order = $aliasTable . '.' . $order;
                } else {

                    // Получим поле сортировки
                    $arrOrder = explode(' ', trim($order));
                    $field = $arrOrder[0];
                    // Присоединим таблицу
                    $joinTable = $joinTableForSort;
                    switch ($joinTable) {
                        case 'blog_posts_profile':
                            $select->joinInner(array('p_profile' => $joinTable), 'p_profile.post_id = p.id', array())
                                    ->where('p_profile.profile_key = ?', $field);
                            $order = 'p_profile.profile_value ' . $arrOrder[1];
                            break;
                        case 'blog_posts_images':
                            $select->joinInner(array('img' => $joinTable), 'img.post_id = p.id', array());
                            $order = 'img.' . $field . ' ' . $arrOrder[1];
                            break;
                        case 'blog_posts_audio':
                            $select->joinInner(array('au' => $joinTable), 'au.post_id = p.id', array());
                            $order = 'au.' . $field . ' ' . $arrOrder[1];
                            break;
                        case 'blog_posts_video':
                            $select->joinInner(array('v' => $joinTable), 'v.post_id = p.id', array());
                            $order = 'v.' . $field . ' ' . $arrOrder[1];
                            break;
                        case 'blog_posts_lacations':
                            $select->joinInner(array('l' => $joinTable), 'l.post_id = p.id', array());
                            $order = 'l.' . $field . ' ' . $arrOrder[1];
                            break;
                        case 'blog_posts_tags':
                            $select->joinInner(array('t' => $joinTable), 't.post_id = p.id', array());
                            $order = 't.' . $field . ' ' . $arrOrder[1];
                            break;
                        case 'blog_posts_comments':
                            $select->joinInner(array('c' => $joinTable), 'c.post_id = p.id', array());
                            $order = 'c.' . $field . ' ' . $arrOrder[1];
                            break;
                        case 'users':
                            $select->joinInner(array('u' => $joinTable), 'u.id = p.user_id', array());
                            $order = 'u.' . $field . ' ' . $arrOrder[1];
                            break;
                        case 'users_profile':
                            $select->joinInner(array('u_profile' => $joinTable), 'u_profile.user_id = p.user_id', array())
                                    ->where('u_profile.profile_key = ?', $field);
                            $order = 'u_profile.profile_value ' . $arrOrder[1];
                            break;
                        default:
                            $order = 'p.' . $field . ' ' . $arrOrder[1];
                            break;
                    }
                }
            } else {
                $order = 'p.' . $order;
            }
        }
        $select->order($order);
        return $select;
    }

    //============== WORKING WITH IMAGES ================

    /**
     * Set order of images
     *
     * @param array $order
     * @return void
     */
    public function setImageOrder($order) {
        // sanitize the image IDs
        if (!is_array($order))
            return;

        $newOrder = array();
        foreach ($order as $image_id) {
            if (array_key_exists($image_id, $this->images))
                $newOrder[] = $image_id;
        }

        // ensure the correct number of IDs were passed in
        $newOrder = array_unique($newOrder);
        if (count($newOrder) != count($this->images)) {
            return;
        }

        // now update the database
        $rank = 1;
        foreach ($newOrder as $image_id) {
            $this->_db->update('blog_posts_images', array('ranking' => $rank), 'id = ' . $image_id);

            $rank++;
        }
    }

    //============== WORKING WITH AUDIO ================

    /**
     * Set order of audios
     *
     * @param array $order
     * @return void
     */
    public function setAudioOrder($order) {
        // sanitize the image IDs
        if (!is_array($order))
            return;

        $newOrder = array();
        foreach ($order as $audio_id) {
            if (array_key_exists($audio_id, $this->audio))
                $newOrder[] = $audio_id;
        }

        // ensure the correct number of IDs were passed in
        $newOrder = array_unique($newOrder);
        if (count($newOrder) != count($this->audio)) {
            return;
        }

        // now update the database
        $rank = 1;
        foreach ($newOrder as $audio_id) {
            $this->_db->update('blog_posts_audio', array('ranking' => $rank), 'id = ' . $audio_id);

            $rank++;
        }
    }

    //============== WORKING WITH VIDEO ================

    /**
     * Set order of videos
     *
     * @param array $order
     * @return void
     */
    public function setVideoOrder($order) {
        // sanitize the image IDs
        if (!is_array($order))
            return;

        $newOrder = array();
        foreach ($order as $video_id) {
            if (array_key_exists($video_id, $this->video))
                $newOrder[] = $video_id;
        }

        // ensure the correct number of IDs were passed in
        $newOrder = array_unique($newOrder);
        if (count($newOrder) != count($this->video)) {
            return;
        }

        // now update the database
        $rank = 1;
        foreach ($newOrder as $video_id) {
            $this->_db->update('blog_posts_video', array('ranking' => $rank), 'id = ' . $video_id);

            $rank++;
        }
    }
    
    //============== WORKING WITH COMMENTS ================

    /**
     * Get the data to build a tree Comments
     * 
     *
     * @return array
     */
    public function getTreeComments() {//
        return  Default_Model_DbTable_BlogPostComment::getTreeComments($this->_db, $this->user_id, array('post_id' => $this->getId()));
    }
    
    /**
     * Check whether there is comments for this message
     *
     * @return bool
     */
    public function isComments() {
        return  (bool) Default_Model_DbTable_BlogPostComment::GetPostsComments_Count($this->_db, array(
            'post_id' => array($this->getId())
            ));
    }
    
    /**
     * Get the number of comments to this post
     *
     * @return bool
     */
    public function getCommentsCount() {
        return  (int) Default_Model_DbTable_BlogPostComment::GetPostsComments_Count($this->_db, array(
            'post_id' => array($this->getId())
            ));
    }

    //============== FULL-TEXT SEARCH ================//

    /**
     * Creating and getting indexed document
     *
     * @return Zend_Search_Lucene_Document
     */
    public function getIndexableDocument() {
        $doc = new Zend_Search_Lucene_Document();
        $doc->addField(Zend_Search_Lucene_Field::Keyword('post_id', $this->getId()));

        $fields = array(
            'title' => $this->profile->title,
            'description' => $this->profile->description,
            'content' => strip_tags($this->profile->content),
            'published' => $this->profile->ts_published,
            'tags' => join(' ', $this->getLabels())
        );

        foreach ($fields as $name => $field) {
            $doc->addField(
                    Zend_Search_Lucene_Field::UnStored($name, $field, 'utf-8')
            );
        }

        return $doc;
    }

    /**
     * Get the path to the search index
     *
     * @return string
     */
    public static function getIndexFullpath() {
        $config = Zend_Registry::get('config');

        $pathsData = $config['paths']['data'];

        return sprintf('%s/search-index', $pathsData);
    }

    /**
     * Add the document in the search index
     *
     * @return void
     */
    protected function addToIndex() {
        try {
            $index = Zend_Search_Lucene::open(self::getIndexFullpath());
        } catch (Exception $ex) {
            self::RebuildIndex();
            return;
        }

        try {
            $query = new Zend_Search_Lucene_Search_Query_Term(
                            new Zend_Search_Lucene_Index_Term($this->getId(), 'post_id')
            );

            $hits = $index->find($query);
            foreach ($hits as $hit)
                $index->delete($hit->id);

            if ($this->status == self::STATUS_LIVE)
                $index->addDocument($this->getIndexableDocument());

            $index->commit();
        } catch (Exception $ex) {
            $logger = Zend_Registry::get('Zend_Log');
            $logger->warn('Error updating document in search index: ' .
                    $ex->getMessage());
        }
    }

    /**
     * Remove the document from the search index
     *
     * @return void
     */
    protected function deleteFromIndex() {
        try {
            $index = Zend_Search_Lucene::open(self::getIndexFullpath());
            $query = new Zend_Search_Lucene_Search_Query_Term(
                            new Zend_Search_Lucene_Index_Term($this->getId(), 'post_id')
            );

            $hits = $index->find($query);
            foreach ($hits as $hit)
                $index->delete($hit->id);

            $index->commit();
        } catch (Exception $ex) {
            $logger = Zend_Registry::get('Zend_Log');
            $logger->warn('Error removing document from search index: ' .
                    $ex->getMessage());
        }
    }

    /**
     * To create a new search index
     *
     */
    public static function RebuildIndex() {
        try {
            $indexFullpath = self::getIndexFullpath();
            $index = Zend_Search_Lucene::create($indexFullpath);
            $options = array('status' => self::STATUS_LIVE);
            $posts = self::GetPosts(Zend_Registry::get('db'), $options);

            foreach ($posts as $post) {
                $index->addDocument($post->getIndexableDocument());
            }
            $index->commit();
        } catch (Exception $ex) {
            $logger = Zend_Registry::get('Zend_Log');
            $logger->warn('Error rebuilding search index: ' .
                    $ex->getMessage());
        }
    }

    //============== ADDITIONAL FUNCTIONS ================

    /**
     * Create a unique URL to get the blog post
     *
     * @param string $title
     * @return string
     */
    protected function generateUniqueUrl($title) {

        //Получим обьект строки для заголовка
        $oTitle = new Default_Plugin_String($title);
        //Преобразуем заголовок сообщения к нижнему регистру
        $oTitle = $oTitle->toLower()->translit();

        // Преобразуем заголовок сообщения в транслит.
        $url = strval($oTitle);


        $filters = array(
            // replace & with 'and' for readability
            '/&+/' => 'and',
            // replace non-alphanumeric characters with a hyphen
            '/[^a-z0-9]+/i' => '-',
            // replace multiple hyphens with a single hyphen
            '/-+/' => '-'
        );


        // apply each replacement
        foreach ($filters as $regex => $replacement)
            $url = preg_replace($regex, $replacement, $url);

        // remove hyphens from the start and end of string
        $url = trim($url, '-');

        // restrict the length of the URL
        $url = trim(substr($url, 0, 50));

        // set a default value just in case
        if (strlen($url) == 0)
            $url = 'post';


        // find similar URLs
        $query = sprintf("select url from %s where user_id = %d and url like ?", $this->_table, $this->user_id);


        $query = $this->_db->quoteInto($query, $url . '%');
        $result = $this->_db->fetchCol($query);


        // if no matching URLs then return the current URL
        if (count($result) == 0 || !in_array($url, $result))
            return $url;

        // generate a unique URL
        $i = 2;
        do {
            $_url = $url . '-' . $i++;
        } while (in_array($_url, $result));

        return $_url;
    }

    /**
     * Create a label (transliteration) of given Russian notation suitable for use in the URL
     *
     * @param string $label
     * @return string
     */
    protected function generateTag($label) {

        //Получим обьект строки для метки
        $oLabel = new Default_Plugin_String($label);
        //Преобразуем метку к нижнему регистру и сделаем транслит
        $oLabel = $oLabel->toLower()->translit();

        // Преобразуем метку в строку
        $url = (string) ($oLabel);


        $filters = array(
            // replace & with 'and' for readability
            '/&+/' => 'and',
            // replace non-alphanumeric characters with a hyphen
            '/[^a-z0-9]+/i' => '-',
            // replace multiple hyphens with a single hyphen
            '/-+/' => '-'
        );


        // apply each replacement
        foreach ($filters as $regex => $replacement)
            $url = preg_replace($regex, $replacement, $url);

        // remove hyphens from the start and end of string
        $url = trim($url, '-');

        // restrict the length of the URL
        $url = trim(substr($url, 0, 30));

        // set a default value just in case
        if (strlen($url) == 0)
            $url = 'tag';

        return $url;
    }

    /**
     * Circumcision line
     *
     * @param int $length
     * @return string
     */
    public function getTeaser($length) {
        $search = array('&', '/em', '/p', 'ampamp;', 'amp;', 'lt;', 'gt;');
        //-------------------------
        $s = $this->profile->content;
        // Заменим все HTML сущности соответствующими символами
        $s = html_entity_decode($this->profile->content);
        // Удалим HTML и PHP тэги из строки
        $s = strip_tags($s);
        // Удалим некоторые символы, обрежем строку до заданного размера и уберем лишние пробелы
        $s = new Default_Plugin_String($s);
        $s = (string) $s->replace($search, "")->Truncate($length)->Strip();
        return $s;
    }

    /**
     * Content filtering information for blog post:
     * - when saving removes the base path in the URL resources
     * - when reading add the base path to the URL resources
     * 
     * @param string $action  Maybe two values: 'save' and 'load'
     * @return bool
     */
    protected function filterContent($action) {

        if (!$this->profile->content) {
            return TRUE;
        }

        //Сохранение записи
        if ($action == 'save') {
            // Определим действие над полем
            $action = $this->profile->getAction('content');

            // Удалим базовый путь из URL ресурсов
            $filterContent = new Default_Form_Filter_DeleteBasePathUrl();
            $content = $filterContent->filter($this->profile->content);
            if (!$content === FALSE) {
                $this->profile->content = $content;

                // Установим старое действие над полем
                $this->profile->setAction('content', $action);
                return TRUE;
            } else {
                return FALSE;
            }
        }

        //Чтение записи
        if ($action == 'load') {
            // Добавим базовый путь к URL ресурсам на странице HTML
            $filterContent = new Default_Form_Filter_AddBasePathUrl();
            $content = $filterContent->filter($this->profile->content);

            if (!$content === FALSE) {
                $this->profile->content = $content;
                // Установим действие по умолчанию для свойства 'content'
                $this->profile->setAction('content', Default_Model_Profile::ACTION_NONE);
                return TRUE;
            } else {
                return FALSE;
            }
        }
    }

}