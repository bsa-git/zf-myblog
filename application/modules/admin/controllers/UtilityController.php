<?php

/**
 * Admin_UtilityController
 *
 * Controller - Utility
 * implementing additional functions
 *
 * @uses       Default_Plugin_BaseController
 * @package    Module-Admin
 * @subpackage Controllers
 * @author   Sergii Beskorovainyi <bsa2657@yandex.ru>
 * @license  MIT <http://www.opensource.org/licenses/mit-license.php>
 * @link     https://github.com/bsa-git/zf-myblog/
 */
class Admin_UtilityController extends Default_Plugin_BaseController {

    /**
     * Action - get user password
     *
     * Access to the action is possible in the following paths:
     * - /utility/userpassword
     *
     * @return void
     */
    public function userpasswordAction() {
        if ($this->_isAjaxRequest) {

            $request = $this->getRequest();
            $params = $request->getParams();

            //Создадим обьект формы
            $loginForm = new Default_Form_UserLogin();

            //Проверим правильность заполнения полей формы
            if ($loginForm->isValid($params)) {
                $user = new Default_Model_DbTable_User($this->db);
                $username = $loginForm->getValue('username');
                if ($user->loadByUsername($username)) {
                    if ($this->_isAdmin && ($user->user_type == 'editor' || $user->user_type == 'member')) {
                        $json = array(
                            'password' => $user->password,
                        );
                    } elseif ($this->_isEditor && $user->user_type == 'member') {
                        $json = array(
                            'password' => $user->password,
                        );
                    } else {
                        $json = array(
                            'class_message' => 'warning',
                            'messages' => array(
                                '<em>' . $this->Translate('Запрещено редактировать сообщение пользователя -') . $username . '"</em>',
                                $this->Translate('У вас недостаточно прав для редактирования сообщения этого пользователя')
                            )
                        );
                    }
                }
            } else {
                $json = array(
                    'class_message' => 'warning',
                    'messages' => $this->getFormMessages($loginForm)
                );
            }

            // Запишем в лог
            if (isset($json['password'])) {
                $my_usertype = $this->_identity->user_type;
                $my_username = $this->_identity->username; 
                $username = $user->username;
                $usertype = $user->user_type;
                $post_url = $params['url'];
                $post_title = $params['title'];
                $message = sprintf('User - "%s"(%s) login to site with a user name - "%s"(%s), to edit the post - "%s"(%s)', 
                        $my_username, $my_usertype, $username, $usertype, $post_title, $post_url);
                // Запомним в логе сообщений
                $this->_logMsg->admin_post_edit($message);
            }

            $this->sendJson($json);
        }
    }

}

?>