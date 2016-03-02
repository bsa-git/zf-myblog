<?php

/**
 * Default_Model_DbTable_User
 * 
 * Table - users
 *
 *
 * @uses       Default_Model_DatabaseObject
 * @package    Module-Default
 * @subpackage Models
 * @author   Sergii Beskorovainyi <bsa2657@yandex.ru>
 * @license  MIT <http://www.opensource.org/licenses/mit-license.php>
 * @link     https://github.com/bsa-git/zf-myblog/
 */
class Default_Model_DbTable_User extends Default_Model_DatabaseObject {

    /**
     *
     * Table config
     * @var array
     */
    private $_config = array(
        'name' => 'users',
        'primary' => 'id',
        'dependentTables' => array('Default_Model_DbTable_UserProfile'),
        'referenceMap' => array()
    );

    /**
     *
     * Password config
     * @var array
     */
    private $_config_password = array(
        'length' => 6,
        'alpha_upper_include' => FALSE,
        'symbol_include' => FALSE,);

    /**
     *
     * User roles
     * @var array
     */
    static $userTypes = array(
        'member' => 'member',
        'editor' => 'editor',
        'administrator' => 'administrator');

    /**
     * 
     * Array post objects
     * 
     * @var Default_Model_DbTable_BlogPost
     */
    public $posts = null;

    /**
     * 
     * Object user profile
     * 
     * @var Default_Model_DbTable_UserProfile
     */
    public $profile = null;

    /**
     * New password
     *
     * @var string
     */
    public $_newPassword = null;

    //================================
    
    /**
     * Constructor
     * 
     * @param Zend_Db_Adapter_Abstract $db
     */
    public function __construct($db) {
        $this->_config['db'] = $db;
        parent::__construct($this->_config);

        $this->add('username', NULL, NULL);
        $this->add('password', NULL, NULL);
        $this->add('user_type', self::$userTypes['member'], NULL);
        $this->add('ts_created', time(), self::TYPE_TIMESTAMP);
        $this->add('ts_last_login', NULL, self::TYPE_TIMESTAMP);
        $this->add('actual', 1, self::TYPE_BOOLEAN);

        $this->profile = new Default_Model_DbTable_UserProfile($db);
    }

    //================ HANDLING OF EVENTS ============

    /**
     * Event before inserting the record
     *
     * @return bool
     */
    protected function preInsert() {

        //Получим пароль
        if ($this->_request->getModuleName() != 'admin') {
            $password = Default_Plugin_SysBox::createPassword($this->_config_password);
            $this->_newPassword = $password;
            $this->password = $this->_newPassword;
        }
        $this->profile->num_posts = 10;
        $this->profile->blog_public = 0;
        return true;
    }

    /**
     * Event after inserting the record
     * 
     * @return bool 
     */
    protected function postInsert() {
        $this->profile->setUserId($this->getId());
        $this->profile->save(false);
        if ($this->_request->getModuleName() != 'admin') {
            $arrMail = $this->_createUserReg_Email('user-register.tpl');
            $this->sendEmail($arrMail);
        }
        return true;
    }

    /**
     * Event after loaded the record
     *
     */
    protected function postLoad() {
        $this->profile->setUserId($this->getId());
        $this->profile->load();
    }

    /**
     * Event after updated the record
     *
     * @return bool
     */
    protected function postUpdate() {
        $this->profile->save(false);
        return true;
    }

    /**
     * Event before deleting the record
     *
     * @return bool
     */
    protected function preDelete() {
        $result = TRUE;
        //-----------------
        // Удалим все сообщения
        if ($this->posts) {
            foreach ($this->posts as $post) {
                $result = $post->delete(false);
                if (!$result) {
                    return FALSE;
                }
            }
        }

        // Удалим профайл пользователя
        if ($result) {
            $result = $this->profile->delete();
        }

        return $result;
    }

    /**
     * Event after deleting the record
     *
     * @return bool
     */
    protected function postDelete() {
        // Удалим директорию пользователя для загрузки файлов
        $result = Default_Plugin_SysBox::deleteUserUploadDir($this->username);
        return $result;
    }

    //============= MAIL ============//

    /**
     * Preparation of email for user registration
     * 
     * @param string  $tpl  //Название шаблона письма
     * @return array
     */
    private function _createUserReg_Email($tpl) {
        $arrMail = array();
        //--------------------
        $templater = Default_Plugin_SysBox::createViewSmarty();
        $templater->user = $this;
        $templater->LoginURL = Default_Plugin_SysBox::getFullURL(array(
                    'controller' => 'account',
                    'action' => 'login'));

        // fetch the e-mail body
        $body = $templater->render('email/' . $tpl);

        // extract the subject from the first line
        list($subject, $body) = preg_split('/\r|\n/', $body, 2);
        $arrMail['subject'] = trim($subject);
        $arrMail['body'] = trim($body);

        return $arrMail;
    }

    /**
     * Creating email of the user's new password
     * 
     * @param string  Название шаблона письма
     * @return array
     */
    private function _createFetchPassword_Email($tpl) {
        $arrMail = array();
        //--------------------

        $templater = Default_Plugin_SysBox::createViewSmarty();
        $templater->user = $this;
        $templater->ActivateURL = Default_Plugin_SysBox::getFullURL(array(
                    'controller' => 'account',
                    'action' => 'fetchpassword'));

        // fetch the e-mail body
        $body = $templater->render('email/' . $tpl);

        // extract the subject from the first line
        list($subject, $body) = preg_split('/\r|\n/', $body, 2);

        $arrMail['subject'] = trim($subject);
        $arrMail['body'] = trim($body);
        return $arrMail;
    }

    /**
     * Send email
     *
     * @param array $aMail
     */
    public function sendEmail(array $aMail) {
        //Отправим почту
        Default_Plugin_SysBox::sendMail(array(
            'to' => array(
                'email' => $this->profile->email,
                'name' => trim($this->profile->first_name . ' ' . $this->profile->last_name)
            ),
            'subject' => $aMail['subject'],
            'body' => $aMail['body'],
        ));
    }

    //============== USER AUTHENTICATION ===============

    /**
     * Create a user object identification
     * 
     * @return stdClass 
     */
    public function createAuthIdentity() {
        $identity = new stdClass;

        // Личные данные пользователя
        $identity->user_id = $this->getId();
        $identity->username = $this->username;
        $identity->user_type = $this->user_type;
        $identity->first_name = $this->profile->first_name;
        $identity->last_name = $this->profile->last_name;
        $identity->email = $this->profile->email;

        // Настройки профиля
        $identity->num_posts = $this->profile->num_posts; // кол. отображаемых записей в блоге
        $identity->blog_public = $this->profile->blog_public; // признак показать свои опубликованные записи другим пользователям
        // Публичные данные пользователя
        $identity->public_first_name = $this->profile->public_first_name;
        $identity->public_last_name = $this->profile->public_last_name;
        $identity->public_email = $this->profile->public_email;
        $identity->public_home_phone = $this->profile->public_home_phone;
        $identity->public_work_phone = $this->profile->public_work_phone;
        $identity->public_mobile_phone = $this->profile->public_mobile_phone;

        return $identity;
    }

    /**
     * Login success
     */
    public function loginSuccess() {
        $this->ts_last_login = time();
        unset($this->profile->new_password);
        unset($this->profile->new_password_ts);
        unset($this->profile->new_password_key);
        $this->save();

        $message = sprintf('Successful login attempt from url="%s"; user="%s"', $_SERVER['REMOTE_ADDR'], $this->username);

        // Запомним в логе сообщений
        $this->_logMsg->login_ok($message);
        // Запомним в логе статистики
        $serialized = $this->_serializer->serialize(array(
            'url' => $_SERVER['REMOTE_ADDR'],
            'user' => $this->username
                ));
        $this->_logStat->login_ok($serialized);
    }

    /**
     * Error login 
     * 
     * @param string $username
     * @param int $code 
     */
    static public function LoginFailure($username, $code = '') {
        switch ($code) {
            case Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND:
                $reason = 'Unknown username';
                break;
            case Zend_Auth_Result::FAILURE_IDENTITY_AMBIGUOUS:
                $reason = 'Multiple users found with this username';
                break;
            case Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID:
                $reason = 'Invalid password';
                break;
            case Zend_Auth_Result::FAILURE_UNCATEGORIZED:
                $reason = 'Invalid uncategorized';
                break;
            default:
                $reason = '';
        }

        $message = sprintf('Failed login attempt from url="%s"; user="%s"', $_SERVER['REMOTE_ADDR'], $username);

        if (strlen($reason) > 0)
            $message .= sprintf(' (%s)', $reason);

        // Запомним в логе сообщений
        $logMsg = Zend_Registry::get('Zend_Log');
        $logMsg->login_err($message);
        // Запомним в логе статистики
        $serializer = Zend_Serializer::factory('PhpSerialize');
        $serialized = $serializer->serialize(array(
            'url' => $_SERVER['REMOTE_ADDR'],
            'user' => $username,
            'reason' => $reason
                ));
        $logStat = Zend_Registry::get('Zend_LogStat');
        $logStat->login_err($serialized);
    }

    //=================== GET NEW PASSWORD ==================

    /**
     * Fetch password
     * 
     * @return bool 
     */
    public function fetchPassword() {
        if (!$this->isSaved())
            return false;

        //Получим новый пароль
        $password_new = Default_Plugin_SysBox::createPassword($this->_config_password);
        $this->_newPassword = $password_new;
        $this->profile->new_password = md5($this->_newPassword);
        $this->profile->new_password_ts = time();
        $this->profile->new_password_key = md5(uniqid() .
                $this->getId() .
                $this->_newPassword);

        // save new password to profile and send e-mail
        $this->profile->save();
        $arrMail = $this->_createFetchPassword_Email('user-fetch-password.tpl');
        $this->sendEmail($arrMail);

        return true;
    }

    /**
     * Confirm new password
     * 
     * @param string $key
     * @return bool
     */
    public function confirmNewPassword($key) {

        // check that valid password reset data is set
        if (!isset($this->profile->new_password)
                || !isset($this->profile->new_password_ts)
                || !isset($this->profile->new_password_key)) {

            return false;
        }

        // check if the password is being confirm within a day
        if (time() - $this->profile->new_password_ts > 86400)
            return false;

        // check that the key is correct
        if ($this->profile->new_password_key != $key)
            return false;

        // everything is valid, now update the account to use the new password
        // bypass the local setter as new_password is already an md5
        parent::__set('password', $this->profile->new_password);

        unset($this->profile->new_password);
        unset($this->profile->new_password_ts);
        unset($this->profile->new_password_key);

        // finally, save the updated user record and the updated profile
        return $this->save();
    }

    //=============== USER DATA =========//

    /**
     * Get the total number of users satisfying the criteria specified in the parameter $options
     *
     * @param Zend_Db_Adapter_Abstract $db
     * @param array $options
     * @return int
     */
    public static function GetUsersCount($db, $options) {
        $select = self::_GetBaseQuery($db, $options);
        $select->from(null, 'count(*)');

        return $db->fetchOne($select);
    }

    /**
     * Get the array objects of users satisfying the criteria specified in the parameter $options
     * 
     * @param Zend_Db_Adapter_Abstract $db
     * @param array $options
     * @return array массив обьектов типа - Default_Model_DbTable_User 
     */
    public static function GetUsers($db, $options = array()) {
        $users = FALSE;
        //----------------------
        // initialize the options
        $defaults = array(
            'offset' => 0,
            'limit' => 0,
            'order' => 'u.username'
        );

        foreach ($defaults as $k => $v) {
            $options[$k] = array_key_exists($k, $options) ? $options[$k] : $v;
        }

        $select = self::_GetBaseQuery($db, $options);

        // set the fields to select
        $select->from(null, 'u.*');

        // set the offset, limit, and ordering of results
        if ($options['limit'] > 0)
            $select->limit($options['limit'], $options['offset']);

        // Установим параметры сортировки для таблицы
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
            $users = $dbCache->load($tagCache);
        }

        // проверка, есть ли уже данные в кэше:
        if ($users === FALSE) {
            // fetch user data from database
            $data = $db->fetchAll($select);

            // turn data into array of DatabaseObject_User objects
            $users = parent::BuildMultiple($db, __CLASS__, $data);

            if (count($users) == 0)
                return $users;

            $user_ids = array_keys($users);

            // load the profile data for loaded posts
            $profiles = Default_Model_Profile::BuildMultiple($db, 'Default_Model_DbTable_UserProfile', array($user_ids));

            foreach ($users as $user_id => $user) {
                if (array_key_exists($user_id, $profiles)
                        && $profiles[$user_id] instanceof Default_Model_DbTable_UserProfile) {

                    $users[$user_id]->profile = $profiles[$user_id];
                } else {
                    $users[$user_id]->profile->setUserId($user_id);
                }

                //!!!!------ Начало установки признака сортировки -----!!!!!
                if (isset($options['sortColumn'])) {
                    $users[$user_id]->sortColumn = $options['sortColumn'];
                }

                if (isset($options['ascDescFlg'])) {
                    $users[$user_id]->ascDescFlg = $options['ascDescFlg'];
                }
                //!!!!------ Начало установки признака сортировки -----!!!!!
            }

            // Если разрешено кеширование, то сохраним данные в кеше
            if ($dbCache->getOption('caching')) {
                $dbCache->save($users, $tagCache);
            }
        } else {
            $result = $users;
        }
        return $users;
    }

    /**
     * Get sorted array profile users in accordance with the collation settings
     *
     * @param Zend_Db_Adapter_Abstract $db
     * @param array $options
     * @return array
     */
    public static function GetSortProfiles($db, $options) {

        // Получим все записи с установленными признаками желаемой сортировки
        $users = self::GetUsers($db, $options);
        // Отсортируем массив
        usort($users, array("Default_Model_DatabaseObject", "_SortProfiles"));

        return $users;
    }

    /**
     * Get the array of users satisfying the criteria specified in the parameter $options
     * 
     * @param Zend_Db_Adapter_Abstract $db
     * @param array $options
     * @return array  
     */
    public static function GetUsers_Array($db, $options = array()) {
        $arrUsers = FALSE;
        //----------------------
        // initialize the options
        $defaults = array(
            'offset' => 0,
            'limit' => 0,
            'order' => 'u.username'
        );

        foreach ($defaults as $k => $v) {
            $options[$k] = array_key_exists($k, $options) ? $options[$k] : $v;
        }

        $select = self::_GetBaseQuery($db, $options);

        // set the fields to select
        $select->from(null, 'u.*');

        // set the offset, limit, and ordering of results
        if ($options['limit'] > 0)
            $select->limit($options['limit'], $options['offset']);

//        $select->order($options['order']);
        // Установим параметры сортировки для таблицы
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
//                $cache->clean(Zend_Cache::CLEANING_MODE_OLD);
//                $cache->clean(Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, array($tagCache));
            }

            // Получим данные из кеша по тегу $tagCache
            $arrUsers = $dbCache->load($tagCache);
        }

        // проверка, есть ли уже данные в кэше:
        if ($arrUsers === FALSE) {
            // fetch user data from database
            $data = $db->fetchAll($select);

            // turn data into array of DatabaseObject_User objects
            $users = parent::BuildMultiple_Array($db, __CLASS__, $data);

            if (count($users) == 0)
                return $users;

            $user_ids = array_keys($users);

            // load the profile data for loaded posts
            $profiles = Default_Model_Profile::BuildMultiple_Array($db, 'Default_Model_DbTable_UserProfile', array($user_ids));
            $arrUsers = array();
            foreach ($users as $user_id => $user) {
                if (array_key_exists($user_id, $profiles)) {
                    $arrUsers[$user_id] = $users[$user_id] + $profiles[$user_id];
                } else {
                    $arrUsers[$user_id] = $users[$user_id];
                }
            }
            // Если разрешено кеширование, то сохраним данные в кеше
            if ($dbCache->getOption('caching')) {
                $dbCache->save($arrUsers, $tagCache);
            }
        } else {
            $result = $arrUsers;
        }



        return $arrUsers;
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
            'order' => 'u.username'
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
        if ($aliasTable) { // Присоединенная таблица
            $orderData = $select->getPart(Zend_Db_Select::ORDER);
            if ($options['group']) {
                $select->group($orderData[0][0]);
            }

            // Найдем алиас таблицы, запрашиваемого поля
            $arrAliasTable = explode('_', $aliasTable);
            $select->columns(array($arrAliasTable[0] . '.id', $orderData[0][0]));
        } else {
            if ($options['group']) {
                $select->group('u.' . $options['field']);
            }
            $select->columns(array('u.id', 'u.' . $options['field']));
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
        // initialize the options
        $defaults = array(
            'user_id' => array(),
            'actuals' => array(0, 1),
        );

        foreach ($defaults as $k => $v) {
            $options[$k] = array_key_exists($k, $options) ? $options[$k] : $v;
        }

        // create a query that selects from the users table
        $select = $db->select();
        $select->from(array('u' => 'users'), array());


        // filter results on specified user ids (if any)
        if (count($options['user_id']) > 0)
            $select->where('u.id in (?)', $options['user_id']);

        // создадим фильтр по разрешению/запрету (актуальности) блогов
        if (count($options['actuals']) > 0)
            $select->where('u.actual in (?)', $options['actuals']);

        // создадим фильтр для параметров фильтра
        if (isset($options['filter'])) {
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
     * @return array
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
                case 'users_profile':
                    if (!$aliasTable) {
                        $select->joinInner(array('u_profile' => $joinTable), 'u_profile.user_id = u.id', array())
                                ->where('u_profile.profile_key = ?', $field);
                    }

                    foreach ($filterParams as $filterParam) {
                        if (is_array($filterParam)) {
                            $andLogic = (bool) $filterParam['andLogic'];
                            if ($andLogic) {
                                $select->where('u_profile.profile_value ' . $filterParam['compare'] . ' ?', $filterParam['value']);
                            } else {
                                $select->orWhere('u_profile.profile_value ' . $filterParam['compare'] . ' ?', $filterParam['value']);
                            }
                        }
                    }

                    break;
                default:
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
            }
        }
        return $select;
    }

    //------------- SORT ------------//

    /**
     * Get Select object (Zend_Db_Select) for sorting table records
     *
     * @param Zend_Db_Select $select
     * @param array $options
     *
     * @return Zend_Db_Select
     */
    public static function GetSelectForSort($select, $options) {
        $aliasTable = '';
        $order = $options['order'];
        //--------------------------
        $arrOrder = explode('.', $order);

        // Если в параметре сортировкп не задан псевдоним таблицы
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
                        case 'users_profile':
                            $select->joinInner(array('u_profile' => $joinTable), 'u_profile.user_id = u.id', array())
                                    ->where('u_profile.profile_key = ?', $field);
                            $order = 'u_profile.profile_value ' . $arrOrder[1];
                            break;
                        default:
                            $order = 'u.' . $field . ' ' . $arrOrder[1];
                            break;
                    }
                }
            } else {
                $order = 'u.' . $order;
            }
        }
        $select->order($order);
        return $select;
    }
    
    //================ ADDITIONAL FUNCTIONS ==============

    /**
     * Verify whether there is a user named - $aUsername
     *
     * @param string $aUsername
     * @return bool
     */
    public function usernameExists($aUsername) {
        $query = sprintf('select count(*) as num from %s where username = ?', $this->_table, $aUsername);
//        $query = $this->_getQuery('username_exists', array($aUsername));
        $result = $this->_db->fetchOne($query);

        return $result > 0;
    }

    /**
     * Validation of the entered name - $aUsername
     *
     * @param string $aUsername
     * @return bool
     */
    static public function IsValidUsername($aUsername) {
        $validator = new Zend_Validate_Alnum();
        return $validator->isValid($aUsername);
    }

    /**
     * Set value
     *
     * @param string $name
     * @param string $value
     * @return void
     */
    public function __set($name, $value) {
        switch ($name) {
            case 'password':
                $value = md5($value);
                break;
            case 'user_type':
                if (!array_key_exists($value, self::$userTypes))
                    $value = 'member';
                break;
        }
        return parent::__set($name, $value);
    }

    /**
     * Get a user by name
     *
     * @param  string $aUsername      //Имя пользователя (Login)
     * @return Zend_Db_Table_Row
     */
    public function loadByUsername($username) {
        $username = trim($username);
        if (strlen($username) == 0)
            return false;

        $query = sprintf('select %s from %s where username = ?', join(', ', $this->getSelectFields()), $this->_table);

        $query = $this->_db->quoteInto($query, $username);

        return $this->_load($query);
    }

}