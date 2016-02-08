<?php

/**
 * AccountController
 *
 * Контроллер - Account
 * управляет регистрацией и аутентификацией пользователя
 *
 * @uses       Default_Plugin_BaseController
 * @package    Module-Default
 * @subpackage Controllers
 */
class AccountController extends Default_Plugin_BaseController {

    /**
     * Инициализация контроллера
     *
     */
    public function init() {
        parent::init();
        if ($this->_authenticated) {
            $this->_breadcrumbs->addStep($this->Translate('Ваш профиль'), $this->getUrl(null, 'account'));
        }
    }

    /**
     * Действие - index
     * 
     * Заходим в профиль автора
     * 
     * Доступ к действию возможем по следующим путям urls:
     * - /account/index
     *
     * @return void
     */
    public function indexAction() {
        // nothing to do here, index.tpl will be displayed
    }

    /**
     * Регистрация пользователя на сайте
     *
     * Доступ к действию возможем по следующим путям urls:
     * - /account/registration
     *
     * @return void
     */
    public function registerAction() {
        $result = true;
        //----------------------
        // Инициализируем форму регистрации
        $formRegister = new Default_Form_UserRegistration();

        // Проверим тип запроса, если POST значит пришли данные формы
        if ($this->_request->isPost()) {

            // Проверяем на валидность поля формы
            $result = $formRegister->isValid($this->_getAllParams());
            if ($result) {

                // Сохраним данные пользователя
                // и перейдем на действие завершения регистрации
                //Создадим пользователя и установим его данные
                $user = new Default_Model_DbTable_User($this->db);
                $user->username = $formRegister->getValue('username');
                $user->profile->first_name = $formRegister->getValue('first_name');
                $user->profile->last_name = $formRegister->getValue('last_name');
                $user->profile->email = $formRegister->getValue('email');

                //Сохраним данные пользователя
                if ($user->save()) {
                    //Сохраним в сессии признак регистрации пользователя
                    $session = new Zend_Session_Namespace('registration');
                    $session->user_id = $user->getId();
                    //Перейдем к завершению регистрации
                    $this->_redirect('/account/registercomplete');
                } else {
                    // Запишем в лог событие об ошибочной регистрации
                    $username = $formRegister->getValue('username');
                    $first_name = $formRegister->getValue('first_name');
                    $last_name = $formRegister->getValue('last_name');
                    $email = $formRegister->getValue('email');
                    $message = sprintf('Error registering! Can not save in the database or transmission registration data by e-mail: User Login - "%s"; First Name - "%s"; Last Name - "%s"; E-Mail - "%s".', $username, $first_name, $last_name, $email);
                    // Запомним в логе сообщений
                    $this->_logMsg->reg_err($message);
                }
            } else {//неверно заполнена форма!!!
                $errMessages = $this->getFormMessages($formRegister);
                $strMessage = var_export($errMessages, true);

                // Запишем в лог событие об ошибочной регистрации
                $username = $formRegister->getValue('username');
                $first_name = $formRegister->getValue('first_name');
                $last_name = $formRegister->getValue('last_name');
                $email = $formRegister->getValue('email');
                $message = sprintf('Error registering! User data: User Login - "%s"; First Name - "%s"; Last Name - "%s"; E-Mail - "%s".', $username, $first_name, $last_name, $email);
                $message .= "\n\n" . $strMessage;
                // Запомним в логе сообщений
                $this->_logMsg->reg_err($message);

                $this->view->class_message = 'warning';
                $this->view->message = $errMessages;
            }
        }

        // Передаем форму в скрипт вида
        $this->view->formRegister = $formRegister;
        //Добавим путь к действию
        $this->_breadcrumbs->addStep($this->Translate('Создать профиль'));
    }

    /**
     * Действие - registercomplete
     * завершение регистрации пользователя
     *
     * Доступ к действию возможем по следующим путям urls:
     * - /account/registercomplete
     *
     * @return void
     */
    public function registercompleteAction() {
        // retrieve the same session namespace used in register
        $session = new Zend_Session_Namespace('registration');

        // load the user record based on the stored user ID
        $user = new Default_Model_DbTable_User($this->db);
        if (!$user->load($session->user_id)) {
            $this->_forward('registration');
            return;
        }

        // Запишем в лог событие об успешной регистрации
        $username = $user->username;
        $first_name = $user->profile->first_name;
        $last_name = $user->profile->last_name;
        $email = $user->profile->email;
        $message = sprintf('User - "%s"(%s %s) successfully registered! The registration information will be sent to e-mail - "%s".', $username, $first_name, $last_name, $email);
        // Запомним в логе сообщений
        $this->_logMsg->reg_ok($message);

        // Задаем сообщение о успешной регистрации
        $this->view->user = $user;

        //Добавим путь к действию
        $this->_breadcrumbs->addStep($this->Translate('Создать профиль'), $this->getUrl('register'));
        $this->_breadcrumbs->addStep($this->Translate('Профиль создан'));
    }

    /**
     * Действие - login
     * Аутентификация пользователя
     * 
     * Доступ к действию возможем по следующим путям urls:
     * - /account/login
     *
     * @return void
     */
    public function loginAction() {
        $result = true;
        $message = NULL;
        //----------------------
        // if a user's already logged in, send them to their account home page
        $auth = Zend_Auth::getInstance();

        if ($auth->hasIdentity()) {
            $this->_redirect('/account');
        }

        $request = $this->getRequest();
        $params = $request->getParams();

        // Проверим наличие ошибки нарушения доступа к ресурсу
        if ($this->_isAjaxRequest && $params['class_message']) {
            $json = array(
                'class_message' => $params['class_message'],
                'messages' => $params['message']
            );
            $this->sendJson($json);
            return;
        }

        //Создадим обьект формы
        $loginForm = new Default_Form_UserLogin();

        // determine the page the user was originally trying to request
        $redirect = $request->getPost('redirect');


        if (strlen($redirect) == 0) {
            $url = $request->getServer('REQUEST_URI');
            $redirect = $url;
        }
        if (strlen($redirect) == 0) {
            $redirect = $this->getUrl(); //'/account'
        }

        // process login if request method is post
        if ($request->isPost()) {//Обработка формы - заполненной пользователем
            //Проверим правильность заполнения полей формы
            if ($loginForm->isValid($request->getPost())) {

                $db = Zend_Registry::get('db');

                // Найдем пользователя по его имени
                $user = new Default_Model_DbTable_User($db);
                $username = $loginForm->getValue('username');
                if ($user->loadByUsername($username)) {
                    // Определим актуальность пользователя на сайте
                    if ($user->actual) { // Пользователь актуален
                        // setup the authentication adapter
                        $adapter = new Zend_Auth_Adapter_DbTable($db, 'users', 'username', 'password', '?');


                        $password = $loginForm->getValue('password');
                        $password = md5($password);
                        $adapter->setIdentity($username);
                        $adapter->setCredential($password);

                        // Проверим правильность аутентификации
                        $result = $auth->authenticate($adapter);

                        if ($result->isValid()) {

                            //--- Проверим актуальность пользователя ---
                            //Получим данные о пользователе
                            $user = new Default_Model_DbTable_User($this->db);
                            $user_id = $adapter->getResultRowObject()->id;
                            $user->load($user_id);

                            // record login attempt
                            $user->loginSuccess();

                            // Создадим обьект идентификации пользователя
                            // и запишем его в сессию пользователя
                            $identity = $user->createAuthIdentity();
                            $auth->getStorage()->write($identity);

                            // Перейдем на страницу, которую запрашивал пользователь
                            // или на его авторскую страницу
                            $this->_redirector->gotoUrl($redirect, array('prependBase' => FALSE));
                        } else {
                            // record failed login attempt
                            $code = $result->getCode();
                            Default_Model_DbTable_User::LoginFailure($username, $code);
                            $this->view->class_message = 'warning';
                            $message = array(
                                '<em>' . $this->Translate("Ошибка аутентификации") . '!</em>',
                                $this->Translate("Имя или пароль клиента заданы неверно."));
                            $this->view->message = $message;
                        }
                    } else { // Пользователь не актуален (запрещен вход на сайт)
                        // record failed login attempt
                        Default_Model_DbTable_User::LoginFailure($username, Zend_Auth_Result::FAILURE_UNCATEGORIZED);
                        $this->view->class_message = 'warning';
                        $message = array(
                            '<em>' . $this->Translate("Ошибка аутентификации") . '!</em>',
                            $this->Translate("Пользователю запрещен вход на сайт."),
                            $this->Translate("За решением данного вопроса обратитесь к Администратору WEB сайта."),
                        );
                        $this->view->message = $message;
                    }
                } else {
                    // record failed login attempt
                    Default_Model_DbTable_User::LoginFailure($username, Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND);
                    $this->view->class_message = 'warning';
                    $message = array(
                        '<em>' . $this->Translate("Ошибка аутентификации") . '!</em>',
                        $this->Translate("Имя клиента задано неверно."));
                    $this->view->message = $message;
                }
            } else {//неверно введены параметры формы
                $this->view->class_message = 'warning';
                $message = $this->getFormMessages($loginForm);
                $this->view->message = $message;
            }
        } else {//Вывод формы - пустой
            //Установим значение скрытого поля - 'redirect'
            $loginForm->setDefault('redirect', $redirect);

            //Выведем сообщение
            $arrParams = $request->getParams();
            if (isset($arrParams['message'])) {

                //Выведем сообщение - с просьбой авторизироваться
                //из за недоступности ресурса для пользователя
                $this->view->class_message = $arrParams['class_message'];
                $this->view->message = $arrParams['message'];
            }
        }

        $this->view->loginForm = $loginForm;
        $this->view->redirect = $redirect;

        //Добавим путь к действию
        $this->_breadcrumbs->addStep($this->Translate('Вход'));
    }

    /**
     * Действие - logout
     * Выход пользователя
     * 
     * Доступ к действию возможем по следующим путям urls:
     * - /account/logout
     *
     * @return void
     */
    public function logoutAction() {

        // Запомним в логе сообщений
        $auth = Zend_Auth::getInstance();
        $identity = $auth->getIdentity();
        $message = "Logout the user=\"$identity->username\"";
        $this->_logMsg->logout($message);

        //Очистим идентификацию
        $auth->clearIdentity();
        $this->_redirect('/account/login');
    }

    /**
     * Действие - fetchpassword
     * Получить новый пароль
     * 
     * Доступ к действию возможем по следующим путям urls:
     * - /account/fetchpassword
     *
     * @return void
     */
    public function fetchpasswordAction() {

        //----------------------
        // if a user's already logged in, send them to their account home page
        if (Zend_Auth::getInstance()->hasIdentity()) {
            $this->_redirect('/account');
        }

        // Определим полный адрес активации нового пароля
        $ActivateURL = Default_Plugin_SysBox::getFullURL(array(
                    'controller' => 'account',
                    'action' => 'fetchpassword'));


        //Создадим обьект формы
        $fetchpasswordForm = new Default_Form_FetchPassword();

        $action = $this->getRequest()->getQuery('action');

        if ($this->getRequest()->isPost())
            $action = 'submit';

        switch ($action) {
            case 'submit':
                // Проверяем на валидность поля формы
                $params = $this->_getAllParams();
                $result = $fetchpasswordForm->isValid($params);

                if ($result) {

                    $username = trim($params['username']);
                    $user = new Default_Model_DbTable_User($this->db);
                    if ($user->load($username, 'username')) {
                        if ($user->fetchPassword()) {
                            // Запомним в логе сообщений
                            $message = "For user=\"$username\" created a new password, and sended to email";
                            $this->_logMsg->fetchpass_complete_ok($message);

                            // Запомним в логе статистики
                            $serialized = $this->_serializer->serialize(array(
                                'user' => $username
                            ));
                            $this->_logStat->fetchpass_complete_ok($serialized);

                            $url = '/account/fetchpassword?action=complete';
                            $this->_redirect($url);
                        } else {
                            // Запомним в логе сообщений
                            $message = "Error fetch the password for user=\"$username\"";
                            $this->_logMsg->fetchpass_complete_err($message);

                            // Запомним в логе статистики
                            $reason = "user->fetchPassword()->ERROR";
                            $serialized = $this->_serializer->serialize(array(
                                'user' => $username,
                                'reason' => $reason
                            ));
                            $this->_logStat->fetchpass_complete_err($serialized);
                        }
                    } else {

                        // Запомним в логе сообщений
                        $message = "User name - \"$username\" was not found";
                        $this->_logMsg->fetchpass_complete_err($message);

                        // Запомним в логе статистики
                        $serialized = $this->_serializer->serialize(array(
                            'user' => $username,
                            'reason' => $message
                        ));
                        $this->_logStat->fetchpass_complete_err($serialized);

                        //Выведем сообщение - что пользователь с таким именем не найден!
                        $this->view->class_message = 'warning';
                        $this->view->message = $this->Translate('Пользователь с таким именем не найден.');
                    }
                }
                break;

            case 'complete':
                //Выведем сообщение - что новый пароль пользователя был передан на e-mail!
                $this->view->class_message = 'message';
                $this->view->message = $this->Translate('Ваш новый пароль был передан на Emaile');
                break;

            case 'confirm':
                $id = $this->getRequest()->getQuery('id');
                $key = $this->getRequest()->getQuery('key');

                $user = new Default_Model_DbTable_User($this->db);
                if (!$user->load($id)) {

                    // Запомним в логе сообщений
                    $message = "User with this ID=\"$id\" is not found in the database";
                    $this->_logMsg->fetchpass_confirm_err($message);

                    $serialized = $this->_serializer->serialize(array(
                        'user_id' => $id,
                        'reason' => 'User is not found in the database'
                    ));
                    $this->_logStat->fetchpass_confirm_err($serialized);

                    //Выведем сообщение - что пользователь с таким ID не найден в базе данных
                    $this->view->class_message = 'warning';
                    $this->view->message = $this->Translate('Ошибка подтверждения нового пароля.');
                    break;
                } else if (!$user->confirmNewPassword($key)) {

                    // Запомним в логе сообщений
                    $message = "A user=\"$user->username\" has not been tested to confirm the password function";
                    $this->_logMsg->fetchpass_confirm_err($message);

                    $serialized = $this->_serializer->serialize(array(
                        'user' => $user->username,
                        'key' => $key,
                        'reason' => 'Error checking function to confirm the password'
                    ));
                    $this->_logStat->fetchpass_confirm_err($serialized);

                    //Выведем сообщение - что не прошла проверку ф-ия подтверждения пароля
                    $this->view->class_message = 'warning';
                    $this->view->message = $this->Translate('Ошибка подтверждения нового пароля.');
                    break;
                }

                // Запомним в логе сообщений
                $message = "User (\"$user->username\"), your new password has been successfully activated.";
                $this->_logMsg->fetchpass_confirm_ok($message);

                $serialized = $this->_serializer->serialize(array(
                    'user' => $user->username
                ));
                $this->_logStat->fetchpass_confirm_ok($serialized);

                //Выведем сообщение - что новый парoль пользователя был успешно активирован
                $this->view->class_message = 'message';
                $this->view->message = $this->Translate('Ваш новый пароль был успешно активирован.');
                break;
        }

        $this->view->ActivateURL = $ActivateURL;
        $this->view->action = $action;
        $this->view->fetchpasswordForm = $fetchpasswordForm;

        //Добавим путь к действию
        $this->_breadcrumbs->addStep($this->Translate('Вход'), $this->getUrl('login'));
        $this->_breadcrumbs->addStep($this->Translate('Получить пароль'));
    }

    /**
     * Действие - details
     * Детальная информация о пользователе
     * 
     * Доступ к действию возможем по следующим путям urls:
     * - /account/details
     *
     * @return void
     */
    public function detailsAction() {
        $changedPass = false;
        $auth = Zend_Auth::getInstance();
        //-------------------------------
        //Создадим форму редактирования личных данных пользователя
        //что бы редактировать свои данных пользователь уже должен войти на сайт
        if ($this->_identity) {
            $formUserDetails = new Default_Form_UserDetails();
        } else {
            $class_message = 'warning';
            $message = $this->Translate('У вас недостаточно прав на этот ресурс');
            $url = "/account/login?class_message=$class_message&message=$message";
            $this->_redirect($url);
        }

        // Проверяем тип запроса, если POST значит пришли данные формы
        if ($this->_request->isPost()) {
            // Проверяем на валидность поля формы
            $result = $formUserDetails->isValid($this->_getAllParams());
            if ($result) {

                //Получим данные о пользователе
                $user = new Default_Model_DbTable_User($this->db);
                $user->load($this->_identity->user_id);

                //Обновим личные данные пользователя
                $user->profile->first_name = $formUserDetails->getValue('first_name');
                $user->profile->last_name = $formUserDetails->getValue('last_name');
                $user->profile->email = $formUserDetails->getValue('email');

                //Настройки профиля
                $user->profile->num_posts = $formUserDetails->getValue('num_posts');
                $user->profile->blog_public = $formUserDetails->getValue('blog_public');

                //Публичные данные пользователя
                $user->profile->public_first_name = $formUserDetails->getValue('public_first_name');
                $user->profile->public_last_name = $formUserDetails->getValue('public_last_name');
                $user->profile->public_email = $formUserDetails->getValue('public_email');
                $user->profile->public_home_phone = $formUserDetails->getValue('public_home_phone');
                $user->profile->public_work_phone = $formUserDetails->getValue('public_work_phone');
                $user->profile->public_mobile_phone = $formUserDetails->getValue('public_mobile_phone');

                $password = $formUserDetails->getValue('password');
                $password_approve = $formUserDetails->getValue('password_approve');
                if (strlen($password) > 0 && strlen($password_approve) > 0) {
                    $user->password = $password;
                    $changedPass = TRUE;
                }

                //Проверим ошибочную ситуацию
                if (strlen($password) > 0 && strlen($password_approve) == 0) {
                    $this->view->class_message = 'warning';
                    $this->view->message = array(
                        $this->Translate('Ошибка формы! Неверно введены данные в форму.'),
                        $this->Translate('При заполненном поле пароль, поле формы подтверждение пароля не должно быть пустым.'));
                    $this->view->formUserDetails = $formUserDetails;
                    return;
                }

                //Запомним данные пользователя
                if ($user->save()) {
                    //Обновим данные идентификации пользователя
                    $auth->getStorage()->write($user->createAuthIdentity());

                    // Запомним в логе сообщений
                    $message = "The user-\"$user->username\" has changed their details.";
                    if ($changedPass) {
                        $message .= " Password has been changed.";
                    }
                    $this->_logMsg->details_ok($message);

                    $this->_redirect('/account/detailscomplete');
                } else {
                    $this->view->class_message = 'warning';
                    $this->view->message = array(
                        $this->Translate('Ошибка изменения детальной информации пользователя') . '!',
                        $this->Translate('Ошибка базы данных при сохранении информации')) . '.';
                    $this->view->formUserDetails = $formUserDetails;
                    return;
                }
            } else {// неверно заполнены поля формы!!!!!!!
                $this->view->class_message = 'warning';
                //$message = $this->getFormMessages($formUserDetails);
                //$this->view->message = $message['warning'];
                $this->view->message = $this->getFormMessages($formUserDetails);
            }
        } else {
            //Установим значения формы по умолчанию
            //--- Личные данные пользователя ---
            $formUserDetails->setDefault('email', $this->_identity->email);
            $formUserDetails->setDefault('first_name', $this->_identity->first_name);
            $formUserDetails->setDefault('last_name', $this->_identity->last_name);

            //--- Настройки профиля ---
            //Установим кол. записей отображаемых в блоге
            $formUserDetails->setDefault('num_posts', $this->_identity->num_posts);
            //Установим признак публикации записей для доступа к ним других пользователей
            $formUserDetails->setDefault('blog_public', $this->_identity->blog_public);

            //--- Публичные данные пользователя ---
            $formUserDetails->setDefault('public_first_name', $this->_identity->public_first_name);
            $formUserDetails->setDefault('public_last_name', $this->_identity->public_last_name);
            $formUserDetails->setDefault('public_email', $this->_identity->public_email);
            $formUserDetails->setDefault('public_home_phone', $this->_identity->public_home_phone);
            $formUserDetails->setDefault('public_work_phone', $this->_identity->public_work_phone);
            $formUserDetails->setDefault('public_mobile_phone', $this->_identity->public_mobile_phone);
        }

        $this->view->formUserDetails = $formUserDetails;

        //Добавим путь к действию
        $this->_breadcrumbs->addStep($this->Translate('Редактировать профиль'));
    }

    /**
     * Действие - detailscomplete
     * Окончание редактирования детальной информации о пользователе
     * 
     * Доступ к действию возможем по следующим путям urls:
     * - /account/detailscomplete
     *
     * @return void
     */
    public function detailscompleteAction() {
        $user = new Default_Model_DbTable_User($this->db);
        $user_id = Zend_Auth::getInstance()->getIdentity()->user_id;
        $user->load($user_id);

        $this->view->user = $user;

        //Добавим путь к действию
        $this->_breadcrumbs->addStep($this->Translate('Редактировать профиль'), $this->getUrl('details'));
        $this->_breadcrumbs->addStep($this->Translate('Профиль изменен'));
    }

}

?>