<?php

/**
 * Default_Model_Log
 * 
 * Model - logging tables (log_msg, log_error, log_stat)
 *
 *
 * @uses       Zend_Log_Writer_Db
 * @package    Module-Default
 * @subpackage Models
 * @author   Sergii Beskorovainyi <bsa2657@yandex.ru>
 * @license  MIT <http://www.opensource.org/licenses/mit-license.php>
 * @link     https://github.com/bsa-git/zf-myblog/
 */
class Default_Model_Log extends Zend_Log_Writer_Db {

    /**
     * Database adapter instance
     *
     * @var Zend_Db_Adapter
     */
    protected $_db;

    /**
     * Name of the log table in the database
     *
     * @var string
     */
    protected $_table;

    /*
     * The maximum number of rows in the table
     * @var array
     */
    protected $_max_rows = null;

    /**
     * Class constructor
     *
     * @param array $params   
     * @return void
     */
    public function __construct($params) {
        $db = $params['db'];
        $table = $params['table'];
        $columnMap = $params['columnMap'];
        $max_rows = $params['max_rows'];

        $this->_max_rows = (int) $max_rows;
        $this->_db = $db;
        $this->_table = $table;

        parent::__construct($db, $table, $columnMap);
    }

    /**
     * Write a message to the log.
     *
     * @param  array  $event  event data
     * @return void
     * @throws Zend_Log_Exception
     */
    protected function _write($event) {

        $config = Zend_Registry::get('config');
        $isLogMsg = (bool) $config['logging']['log']['enable'];
        $isLogStat = (bool) $config['logging']['statistics']['enable'];
        $isLogEx = (bool) $config['logging']['exeption']['enable'];
        // Проверим возможность логирования
        if ($this->_table == 'log_msg' && !$isLogMsg) {
            return;
        } elseif ($this->_table == 'log_stat' && !$isLogStat) {
            return;
        } elseif ($this->_table == 'log_error' && !$isLogEx) {
            return;
        }

        // Удалим лишние записи
        if ($this->_max_rows && $this->_max_rows !== -1) {
            $select = $this->_db->select();
            $select->from($this->_table, 'count(*)');
            $count_rows = (int) $this->_db->fetchOne($select);
            if ($count_rows >= $this->_max_rows) {
                // Получим массив ids для удаления строк в таблице
                $limit = $count_rows - $this->_max_rows;
                $limit++;
                $select = $this->_db->select();
                $select->from($this->_table, 'id');
                $select->limit($limit, 0);
                $row_ids = $this->_db->fetchCol($select);

                // Удалим строки из таблицы
                foreach ($row_ids as $id) {
                    $this->_db->delete($this->_table, 'id=' . $id);
                }
            }
        }
        // Запишем событие в лог
        parent::_write($event);
    }

    //=========== WORKING WITH LOGS ==================//
    /**
     * Write data to log
     *
     * @param  Zend_Controller_Request_Abstract $request
     * @return void
     */
    static function toLog($request) {
        //return;
        $params = $request->getParams();
        $serializer = Zend_Serializer::factory('PhpSerialize');
        $db = Zend_Registry::get('db');
        $logStat = Zend_Registry::get('Zend_LogStat');
        $auth = Zend_Auth::getInstance();
        $user_url = $_SERVER['REMOTE_ADDR'];
        //------------------
        // Получим адрес в виде: module/controller/action
        $module = $request->getModuleName();
        $controller = $request->getControllerName();
        $action = $request->getActionName();
        $url_action = "$module/$controller/$action";

        $arrLogURL = array(
            //----- Модуль=default; Контроллер=user -----
            'default/user/view', // Открыть сообщение
            'default/user/videos', // Играть видео
        );

        // Определим данные для сохранения в лог
        foreach ($arrLogURL as $urlLog) {
            switch ($urlLog) {
                case 'default/user/view': // Открыть сообщение
                    if ($url_action == $urlLog) {
                        $urlPost = trim($request->getUserParam('url'));
                        $username = trim($request->getUserParam('username'));

                        $arrStat = array(
                            'author' => $username,
                            'post_url' => $urlPost,
                            'user_url' => $user_url
                        );
                        if ($auth->hasIdentity()) {
                            $identity = $auth->getIdentity();
                            $arrStat['user'] = $identity->username;
                        }

                        $serialized = $serializer->serialize($arrStat);
                        $logStat->post_open($serialized);
                    }
                    break;
                case 'default/user/videos': // Открыть сообщение
                    if ($url_action == $urlLog && $params['type_action'] == 'play') {
                        $clip_id = $params['clip_id'];

                        $arrStat = array(
                            'clip_id' => $clip_id,
                            'user_url' => $user_url,
                        );
                        if ($auth->hasIdentity()) {
                            $identity = $auth->getIdentity();
                            $arrStat['user'] = $identity->username;
                        }

                        $serialized = $serializer->serialize($arrStat);
                        $logStat->video_play($serialized);
                    }
                    break;
                default:
                    break;
            }
        }
    }

}