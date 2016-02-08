<?php

/**
 * Default_Model_DbTable_BlogPostComment
 *
 * Таблица - для работы с комментариями пользователей
 *
 *
 * @uses       Default_Model_DatabaseObject
 * @package    Module-Default
 * @subpackage Models
 */
class Default_Model_DbTable_BlogPostComment extends Default_Model_DatabaseObject {

    /**
     *
     * Конфигурация таблицы
     * @var array
     */
    private $_config = array(
        'name' => 'blog_posts_comments',
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
//    protected $_uploadedFile;
    //================ КОНСТРУКТОР КЛАССА ============

    /**
     * Конструктор обьекта таблицы
     * 
     * @param Zend_Db_Adapter_Abstract $db
     */
    public function __construct($db) {
        $this->_config['db'] = $db;
        parent::__construct($this->_config);
        $this->add('ts', time(), self::TYPE_TIMESTAMP);
        $this->add('post_id');
        $this->add('user_id');
        $this->add('reply_id', 0);
        $this->add('comment', "", self::TYPE_HTML);
    }

    //================ ОБРАБОТКА СОБЫТИЙ ============

    /**
     * Событие перед вставкой записи
     *
     * @return bool
     */
    public function preInsert() {
        return TRUE;
    }

    /**
     * Событие после вставки записи
     *
     * @return bool
     */
    public function postInsert() {
        return TRUE;
    }

    /**
     * Событие перед удалением записи
     *
     * @return bool
     */
    public function preDelete() {
        return TRUE;
    }

    //============== РАБОТА С НАБОРОМ ЗАПИСЕЙ =================

    /**
     * Загрузить данные комментария для конкретного сообщения
     *
     * @param int $post_id
     * @param int $comment_id
     * @return bool
     */
    public function loadForPost($post_id, $comment_id) {
        $post_id = (int) $post_id;
        $comment_id = (int) $comment_id;

        if ($post_id <= 0 || $comment_id <= 0)
            return false;

        $query = sprintf(
                'select %s from %s where post_id = %d and id = %d', join(', ', $this->getSelectFields()), $this->_table, $post_id, $comment_id
        );

        return $this->_load($query);
    }

    /**
     * Получить данные для построения дерева комментариев
     *
     * @param Zend_Db_Adapter_Abstract $db
     * @param int $post_id
     * @return array
     */
    public static function getTreeComments($db, $user_id, $params ) {
        $sortcomm = array();
        $newComments = array();
        //-----------------------------------------
        
        //------ Добавим некоторые поля в записи комментариев ------
        
        // Получим комментарии
//        $options = array('post_id' => $post_id);
        $comments = self::GetComments_Array($db, $params);
        
        // Получим не повторяющийся массив Ids пользователей
        $arrBox = new Default_Plugin_ArrayBox($comments);
        if($arrBox->count() == 0){
            return $sortcomm;
        }
        
        $arrUser_ids = $arrBox->slice('user_id', TRUE);
        
        // Добавим в массив Ids пользователей id автора, если его там нет
        if(! $arrUser_ids->isValue($user_id)){
            $arrUser_ids = $arrUser_ids->push($user_id);
        }
        $arrUser_ids = $arrUser_ids->get();
        
        // Получим массив пользователей из их Ids
        $options = array('user_id' => $arrUser_ids);
        $users = Default_Model_DbTable_User::GetUsers($db, $options);


        foreach ($comments as $comment) {
            $user = $users[$comment['user_id']];
            
            // Установим имя пользователя
            $comment['username'] = $user->username;

            // Установим дату создания комментария
            $date = new Zend_Date($comment['ts'], 'U');
            $dtFormat = $date->get('dd MMMM YYYY, HH:mm');
            $comment['date'] = $dtFormat;

            // Установим признак авторства
            $isAutor = ($user_id == $comment['user_id']);
            $comment['is_autor'] = $isAutor;

            // Установим изображение пользователя
            if ($user->profile->user_img) {
                $user_img = $user->profile->user_img;
            } else {
                if ($comment['is_autor']) {
                    $user_img = "/images/system/user_new.png";
                } else {
                    if ($user->profile->sex) {
                        if ($user->profile->sex == 'male') {
                            $user_img = "/images/system/user_male.png";
                        } else {
                            $user_img = "/images/system/user_female.png";
                        }
                    } else {
                        $user_img = "/images/system/user_message.png";
                    }
                }
            }
            $comment['user_img'] = $user_img;

            // Установим URL пользователя
            $comment['user_url'] = "/user/{$user->username}";
            
            // Добавим в новый массив
            $newComments[] = $comment;
        }

        //------ Создадим дерево комментариев ------
        
        if (count($newComments) > 0) {
            // subcomments
            foreach ($newComments as $item) {
                if ($item['reply_id'] == 0) {
                    $sortcomm[$item['id']]['parent'] = $item;
                }

                if ($item['reply_id'] > 0) {
                    if (isset($path[$item['reply_id']])) {
                        $str = '$sortcomm';
                        foreach ($path[$item['reply_id']] as $pitem) {
                            $rep = $item['reply_id'];
                            $str.="[$pitem][sub]";
                        }
                        $str.="[{$item['reply_id']}][sub]";

                        $str.="[{$item['id']}]['parent']";
                        $str.='=$item;';

                        eval($str);

                        foreach ($path[$item['reply_id']] as $pitem) {
                            $path[$item['id']][] = $pitem;
                        }

                        $path[$item['id']][] = $item['reply_id'];
                    } else {
                        $sortcomm[$item['reply_id']]['sub'][$item['id']]['parent'] = $item;
                        $path[$item['id']][] = $item['reply_id'];
                    }
                }
            }
        }
        return $sortcomm;
    }

    /**
     * Получить массив обьектов комментариев для сообщения
     *
     * @param Zend_Db_Adapter_Abstract $db
     * @param array $options
     * @return array Default_Model_DbTable_BlogPostComment
     */
    public static function GetComments($db, $options = array()) {
        // initialize the options
        $defaults = array(
            'user_id' => array(),
            'post_id' => array(),
            'reply_id' => array()
        );

        foreach ($defaults as $k => $v) {
            $options[$k] = array_key_exists($k, $options) ? $options[$k] : $v;
        }

        $select = $db->select();
        $select->from(array('c' => 'blog_posts_comments'), array('c.*'));

        // filter results on specified user ids (if any)
        if (count($options['user_id']) > 0) {
            $select->where('c.user_id in (?)', $options['user_id']);
        }

        // filter results on specified post ids (if any)
        if (count($options['post_id']) > 0) {
            $select->where('c.post_id in (?)', $options['post_id']);
        }

        // filter results on specified reply ids (if any)
        if (count($options['reply_id']) > 0)
            $select->where('c.reply_id in (?)', $options['reply_id']);

        $select->order('c.id');

        // fetch post data from database
        $data = $db->fetchAll($select);

        // turn data into array of BlogPostComment objects
        $comments = parent::BuildMultiple($db, __CLASS__, $data);

        return $comments;
    }

    /**
     * Получить массив  комментариев для сообщения
     *
     * @param Zend_Db_Adapter_Abstract $db
     * @param array $options
     * @return array Default_Model_DbTable_BlogPostComment
     */
    public static function GetComments_Array($db, $options = array()) {
        // initialize the options
        $defaults = array(
            'user_id' => array(),
            'post_id' => array(),
            'reply_id' => array(),
            'comment_id' => array()
        );

        foreach ($defaults as $k => $v) {
            $options[$k] = array_key_exists($k, $options) ? $options[$k] : $v;
        }

        $select = $db->select();
        $select->from(array('c' => 'blog_posts_comments'), array('c.*'));

        // filter results on specified user ids (if any)
        if (count($options['user_id']) > 0) {
            $select->where('c.user_id in (?)', $options['user_id']);
        }

        // filter results on specified post ids (if any)
        if (count($options['post_id']) > 0) {
            $select->where('c.post_id in (?)', $options['post_id']);
        }

        // filter results on specified reply ids (if any)
        if (count($options['reply_id']) > 0)
            $select->where('c.reply_id in (?)', $options['reply_id']);
        
        // filter results on specified id ids (if any)
        if (count($options['comment_id']) > 0)
            $select->where('c.id in (?)', $options['comment_id']);

        $select->order('c.id');

        $strSelect = $select->__toString();

        // fetch post data from database
        $data = $db->fetchAll($select);

        // turn data into array of BlogPostComment objects
        $comments = parent::BuildMultiple_Array($db, __CLASS__, $data);

        return $comments;
    }

    /**
     * Получить общее кол. записей удовлетворяющих
     * критерия, заданным в парметре $options
     *
     * @param Zend_Db_Adapter_Abstract $db
     * @param array $options
     * @return int
     */
    public static function GetPostsComments_Count($db, $options) {
        // initialize the options
        $defaults = array(
            'user_id' => array(),
            'post_id' => array(),
            'reply_id' => array()
        );

        foreach ($defaults as $k => $v) {
            $options[$k] = array_key_exists($k, $options) ? $options[$k] : $v;
        }

        $select = $db->select();
        $select->from(array('blog_posts_comments'), 'count(*)');

        // filter results on specified user ids (if any)
        if (count($options['user_id']) > 0) {
            $select->where('user_id in (?)', $options['user_id']);
        }

        // filter results on specified post ids (if any)
        if (count($options['post_id']) > 0) {
            $select->where('post_id in (?)', $options['post_id']);
        }

        // filter results on specified reply ids (if any)
        if (count($options['reply_id']) > 0)
            $select->where('reply_id in (?)', $options['reply_id']);

        return $db->fetchOne($select);
    }

}

?>