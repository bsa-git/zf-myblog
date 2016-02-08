<?php

/**
 * Admin_UserController
 *
 * Контроллер - User
 * Управление пользователями
 *
 * @uses       Default_Plugin_TableGrid
 * @package    Module-Admin (Администрирование сайта)
 * @subpackage Controllers
 */
class Admin_UserController extends Default_Plugin_TableGridController {

    /**
     * Инициализация контроллера
     *
     */
    public function init() {
        parent::init(); //Управление пользователями сайта
        $this->_breadcrumbs->addStep($this->Translate('Пользователи'), $this->getUrl(null, 'user', 'admin'));
    }

    /**
     * Действие по умолчанию
     *
     * Доступ к действию возможем по следующим путям urls:
     * - /admin/user/index
     * - /admin/user
     *
     * @return void
     */
    public function indexAction() {
        
    }

    /**
     * Действие - users
     * Получить список пользователей
     *
     * Доступ к действию возможем по следующим путям urls:
     * - /admin/user/users
     *
     * @return void
     */
    public function usersAction() {
        //Добавим путь к действию
        $params = $this->_request->getParams();
        $this->view->ajax_post = isset($params['ajax_post']) ? TRUE : FALSE;
        $this->_breadcrumbs->addStep($this->Translate('Список пользователей'));
    }

    /**
     * Действие рассылка новостей
     *
     * Доступ к действию возможем по следующим путям urls:
     * - /admin/user/news
     *
     * @return void
     */
    public function newsAction() {
        //Добавим путь к действию
        $this->_breadcrumbs->addStep($this->Translate('Рассылка новостей'));
        $this->view->message = $this->Translate('Раздел сайта находится в разработке') . '!';
        $this->view->class_message = 'caution';
    }

    /**
     * Действие - login
     * Аутентификация пользователя
     * 
     * Доступ к действию возможем по следующим путям urls:
     * - /admin/user/login
     *
     * @return void
     */
    public function loginAction() {
        $result = true;
        $message = NULL;
        //----------------------
        // Получим обьект авторизации пользователя
        $auth = Zend_Auth::getInstance();

        // Получим параметры запроса
        $request = $this->getRequest();
        $params = $request->getParams();


        //Создадим обьект формы
        $loginForm = new Default_Form_UserLogin();
        $urlAction = $this->getUrl('login', 'user', 'admin');
        $loginForm->setAction($urlAction);

        $redirect = $params['redirect'];

        //Проверим правильность заполнения полей формы
        if ($loginForm->isValid($params)) {

            $db = Zend_Registry::get('db');

            // Найдем пользователя по его имени
            $user = new Default_Model_DbTable_User($db);
            $username = $loginForm->getValue('username');
            if ($user->loadByUsername($username)) {
                // Определим актуальность пользователя на сайте
                // setup the authentication adapter
                $adapter = new Zend_Auth_Adapter_DbTable($db, 'users', 'username', 'password', '?');


                $password = $loginForm->getValue('password');
                $password = $password;
                $adapter->setIdentity($username);
                $adapter->setCredential($password);

                // Проверим правильность аутентификации
                $result = $auth->authenticate($adapter);

                if ($result->isValid()) {//ОК - вход пользователя прошел успешно
                    //--- Проверим актуальность пользователя ---
                    //Получим данные о пользователе
                    $user = new Default_Model_DbTable_User($this->db);
                    $user_id = $adapter->getResultRowObject()->id;
                    $user->load($user_id);

                    // Очистим данные идентификации пользователя
                    $auth->clearIdentity();

                    // Сохраним обьект идентификации пользователя
                    // и перейдем на действие завершения авторизации
                    $user->loginSuccess();

                    // Создадим обьект идентификации пользователя
                    // и запишем его в сессию пользователя
                    $identity = $user->createAuthIdentity();
                    $auth->getStorage()->write($identity);

                    // Перейдем на страницу, которую запрашивал пользователь
                    // или на его авторскую страницу
                    $this->_redirector->gotoUrl($redirect, array('prependBase' => FALSE));
                } else {//ERROR - ошибка валидации параметров входа
                    // record failed login attempt
                    Default_Model_DbTable_User::LoginFailure($username, $result->getCode());
                    $message = array($this->Translate("Ошибка регистрации! Имя или пароль клиента заданы неверно."));
                }
            } else {//ERROR - нет такого пользователя
                // record failed login attempt
                Default_Model_DbTable_User::LoginFailure($username, Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND);
                $message = array($this->Translate("Ошибка регистрации! Имя клиента задано неверно."));
            }
        } else {//ERROR - неверно введены параметры формы
            $message = $this->getFormMessages($loginForm);
        }

        if ($message) {
            $this->view->class_message = 'warning';
            $this->view->message = $message;
        }

        //Добавим путь к действию
        $this->_breadcrumbs->addStep($this->Translate('Вход'));
    }

    //=============== РАБОТА С ОТЧЕТАМИ =================//

    /**
     * Действие - report
     * Отчеты по документам
     * 
     * Доступ к действию возможем по следующим путям urls:
     * - /admin/user/report
     *
     * @return void
     */
    public function reportAction() {
        parent::reportAction();
    }

    /**
     * 
     * Получить данные для отчета
     * 
     * @param string $table
     *
     * @return array
     */
    protected function _getReportData($table) {
        $arrResultData = array();
        $rows_footer = array();
        $footer_colspan = 2;
        //-------------------
        // Получим данные для отчета
        $arrData = parent::_getReportData($table);

        // Установим параметры PDF
        $arrResultData['pdf']['logo_report'] = Default_Plugin_SysBox::getFullURL_Res('/images/system/users48x48.png');
        $arrResultData['pdf']['title_report'] = $this->Translate('Пользователи сайта');
        $arrResultData['pdf']['pageFormat'] = 'A4';

        // Установим параметры для HTML
        $arrResultData['html']['column_model'] = $arrData['column_model'];
        $arrResultData['html']['is_group_head'] = $arrData['is_group_head'];
        $arrResultData['html']['rows_body'] = isset($arrData['rows']) ? $arrData['rows'] : array();
        $arrResultData['html']['is_row_header'] = false;
        $arrResultData['html']['footer_colspan'] = $footer_colspan;

        // Получим данные для отчета
        switch ($table) {
            case 'admin.users':

                // Исключим в отчете некоторые поля
                $fieldsExcluded = array('id', 'actual', 'password');
                $newModelColumns = parent::_excludeFieldsFromReport($fieldsExcluded, $arrData['column_model']);
                $arrResultData['html']['column_model'] = $newModelColumns;

                // Получим массив записей для нижнего колонтитула
                $footers[] = array(
                    'username' => $this->Translate('Всего записей') . ':',
                    'email' => 'count',
                );


                $rows_footer = parent::_footerForReport(array(
                            'footer_colspan' => $footer_colspan,
                            'rows' => $arrData['rows'],
                            'column_model' => $newModelColumns,
                            'footers' => $footers
                ));

                // Установим параметры для HTML
                $arrResultData['html']['logo_report'] = Default_Plugin_SysBox::getFullURL_Res('/images/system/users34x34.png');
                $arrResultData['html']['title_report'] = $this->Translate('Список пользователей сайта');
                $arrResultData['html']['rows_footer'] = $rows_footer;
                $arrResultData['html']['is_row_header'] = true;
//                $arrResultData['html']['footer_colspan'] = $footer_colspan;
                // Установим параметры PDF
                $arrResultData['pdf']['pageFormat'] = 'A4-L';

                break;
            default:
                break;
        }
        return $arrResultData;
    }

    //=============== РАБОТА С ТАБЛИЦЕЙ =================//

    /**
     * Действие rows (получить данные)
     * по этому действию происходит вывод всех данных
     * в соответствии с параметрами запроса
     *
     * Доступ к действию возможем по следующим путям urls:
     * - /admin/user/rows
     *
     * @return void
     */
    public function rowsAction() {
        parent::rowsAction();
    }

    /**
     * Действие data (данные)
     * по этому действию происходит запрос к данным по определенным
     * полям таблицы. Затем эти данные помещаются в ComboBox
     * для удобного редактирования этих полей таблицы
     *
     * Доступ к действию возможем по следующим путям urls:
     * - /admin/user/data
     *
     * @return void
     */
    public function dataAction() {
        parent::dataAction();
    }

    /**
     * Действие validate (проверка значения)
     * по этому действию происходит проверка соответсвующего значения
     * параметры значения передаются в параметрах запроса
     *
     * Доступ к действию возможем по следующим путям urls:
     * - /admin/user/validate
     *
     * @return void
     */
    public function validateAction() {
        parent::validateAction();
    }

    /**
     * Действие save (сохранение данных)
     * по этому действию происходит сохранение измененных данных
     * или добавленых данных
     *
     * Доступ к действию возможем по следующим путям urls:
     * - /admin/user/save
     *
     * @return void
     */
    public function saveAction() {
        parent::saveAction();
    }

    /**
     * Действие delete (удаление данных)
     * по этому действию происходит удаление данных из базы данных
     *
     * Доступ к действию возможем по следующим путям urls:
     * - /admin/user/delete
     *
     * @return void
     */
    public function deleteAction() {
        parent::deleteAction();
    }

    /**
     * Загрузить данными обьект записи таблицы
     *
     * @param array $row
     * @param int $id
     *
     * @return bool
     */
    public function loadRowTable($row, $id) {
        $result = parent::loadRowTable($row, $id);
        if ($result) {
            // Загрузим все записи пользователя
            $options = array(
                'user_id' => array($id)
            );
            $row->posts = Default_Model_DbTable_BlogPost::GetPosts(
                            $this->db, $options);
        }

        return $result;
    }

    /**
     * Изменить/Вставить запись таблицы
     *
     * @param array $row
     *
     * @return bool
     */
    public function saveRowTable($row) {

        $isSaved = $row->isSaved();
        $result = parent::saveRowTable($row);

        // Сохраним событие в лог
        if ($result) {
            $username = $this->_identity->username;
            if ($isSaved) {
                $message = "User - \"$username\" updated a row in the table-\"users\" with username=" . "\"$row->username\"";
                $this->_logMsg->admin_row_update($message);
            } else {
                $message = "User - \"$username\" inserted a row into a table-\"users\" with username=" . "\"$row->username\"";
                $this->_logMsg->admin_row_insert($message);
            }
        }
        return $result;
    }

    /**
     * Удалить обьект записи таблицы
     *
     * @param array $row
     *
     * @return bool
     */
    public function deleteRowTable($row) {

        $result = parent::deleteRowTable($row);
        // Сохраним событие в лог
        if ($result) {
            $username = $this->_identity->username;
            $message = "User - \"$username\" deleted a row from table-\"users\" with username=" . "\"$row->username\"";
            $this->_logMsg->admin_row_delete($message);
        }
        return $result;
    }

    /**
     * Действие search (поиск значения в таблице)
     * по этому действию происходит поиск строки
     * в таблице и возвращается номер стр. поиска
     * если поиск произошел успешно
     *
     * Доступ к действию возможем по следующим путям urls:
     * - /admin/user/search
     *
     * @return void
     */
    public function searchAction() {
        parent::searchAction();
    }

    /**
     * Получить кол. строк в таблице
     *
     * @param array $options
     *
     * @return int
     */
    public function getCountRowsTable($options = NULL) {
        return Default_Model_DbTable_User::GetUsersCount($this->db, $options);
    }

    /**
     * Создать обьект записи таблицы
     *
     * @param array $options
     *
     * @return object
     */
    public function createRowTable($options = NULL) {
        return new Default_Model_DbTable_User($this->db);
    }

    /**
     * Получить записи таблицы
     *
     * @param array $options
     *
     * @return array
     */
    public function getRowsArraysTable($options = NULL) {
        return Default_Model_DbTable_User::GetUsers_Array($this->db, $options);
    }

    /**
     * Получить значения полей в Jason
     *
     * @param array $fields
     *
     * @return array
     */
    public function getValuesForData($fields) {
        $params = array();
        $jsons = array();
        //----------------------------
        $_params = $this->_request->getParams();
        foreach ($fields as $field => $joinTable) {
            switch ($field) {
                // Тип пользователя (роль)
                case "user_type":
                    $roles = Default_Plugin_AclManager::$roles;
                    $jsons[$field][] = array('value' => $roles['admin'], 'text' => $roles['admin']);
                    $jsons[$field][] = array('value' => $roles['editor'], 'text' => $roles['editor']);
                    $jsons[$field][] = array('value' => $roles['member'], 'text' => $roles['member']);
                    break;
                default :
                    $params['field'] = $field;
                    $params['type'] = $_params['type'];
                    $params['joinTableForSort'] = $joinTable;
                    $params['order'] = $field . ' ASC';
                    if ($params['type'] == 'search') {
                        $params['group'] = FALSE;
                        if (isset($_params['filter'])) {
                            $strFilter = stripslashes($_params['filter']);
                            $arrFilter = Zend_Json::decode($strFilter);
                            $params['filter'] = $arrFilter;
                        }
                    }
                    $jsons = $this->getValueForData($params, $jsons);
                    break;
            }
        }
        return $jsons;
    }

    /**
     * Получить значения поля
     *
     * @param array $fields
     *
     * @return array
     */
    public function getValuesForCol($options = NULL) {
        $rows = Default_Model_DbTable_User::GetValuesForCol($this->db, $options);
        return $rows;
    }

    /**
     * Создать форму для проверки значений таблицы
     *
     * @param array $options
     *
     * @return Default_Form_MyForm
     */
    public function createFormForValidation($options = NULL) {
//        if(!class_exists('Admin_Form_Users')){
//            require_once APPLICATION_PATH . '/modules/admin/forms/Users.php';
//        }
        return new Admin_Form_Users($options);
    }

    /**
     * Проверить значение при записи данных
     *
     * @param array $params
     *
     * @return array
     */
    public function validRowForSave($row) {
        $jsons = array();
        $newRow = array();

        $rowTable = $this->createRowTable();
        if ($row['id']) {
            $rowTable->load($row['id']);
        }

        // Проверим редактируем существующую или втавляем новую запись
        if ($row['id']) { // редактируем существующую запись
            if ($row['id'] && $rowTable->username !== $row['username']) {
                $jsons = array(
                    'class_message' => 'warning',
                    'messages' => array(
                        '<em>' . $this->Translate('Ошибка формы! Неверно введены данные в форму.') . '</em>',
                        '<em> ' . $this->Translate('Логин') . ': </em> ' . $this->Translate('Нельзя изменять имя (Login) зарегистрированного пользователя'))
                );
            } else {

                // Удалим из строки поле "username"
                foreach ($row as $key => $value) {
                    if ($key !== 'username') {
                        $newRow[$key] = $value;
                    }
                }
                // Проверим строку на валидность
                $jsons = $this->_isValidRow($newRow);
            }
        } else { // втавляем новую запись
            // Проверим строку на валидность
            $jsons = $this->_isValidRow($row);
        }

        return $jsons;
    }

    /**
     * Получить отформатированное значение
     *
     * @param string $key
     * @param string $value
     *
     * @return string
     */
    public function getFormattedValue($key, $value) {

        if ($key == 'ts_created') {
            if ($value) {
                $value = $this->dtFormat($value, 'U', 'yyyy-MM-dd');
            } else {
                $value = time();
            }
        }
        if ($key == 'ts_last_login') {
            if ($value) {
                $value = $this->dtFormat($value, 'U', 'yyyy-MM-dd HH:mm:ss');
            } else {
                $value = 0;
            }
        }



        return $value;
    }

    /**
     * Получить отформатированные строки значений
     *
     * @param array $rows
     *
     * @return array
     */
    public function getFormattedRows($rows) {
        $formatRows = array();
        $formatRow = array();
        foreach ($rows as $row) {
            foreach ($row as $key => $value) {
                if ($key == 'ts_created') {
                    if ($value) {
                        $value = $this->dtFormat($value, 'yyyy-MM-dd', 'U');
                    } else {
                        $value = $this->dtFormat(time(), 'yyyy-MM-dd', 'U');
                    }
                }
                if ($key == 'ts_last_login') {
                    if ($value) {
                        $value = $this->dtFormat($value, 'yyyy-MM-dd HH:mm:ss', 'U');
                    } else {
                        $value = $this->dtFormat(0, 'yyyy-MM-dd HH:mm:ss', 'U');
                    }
                }
                $formatRow[$key] = $value;
            }
            $formatRows[] = $formatRow;
            $formatRow = array();
        }

        return $formatRows;
    }

}
