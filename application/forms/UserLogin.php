<?php

/**
 * Default_Form_Login
 * 
 * Form - user login
 *
 *
 * @uses       Default_Form_MyForm
 * @package    Module-Default
 * @subpackage Forms
 * @author   Sergii Beskorovainyi <bsa2657@yandex.ru>
 * @license  MIT <http://www.opensource.org/licenses/mit-license.php>
 * @link     https://github.com/bsa-git/zf-myblog/
 */
class Default_Form_UserLogin extends Default_Form_MyForm {

    /**
     * Initialization form
     */
    public function init() {
        // Вызываем родительский метод
        parent::init();


        //---------------- Форма ----------------
        // Указываем action формы
        $urlAction = $this->getUrl('login', 'account');
        
        $urlForgotPassword = $this->getUrl('fetchpassword', 'account');
        
        $this->setAction($urlAction);

        //Зададим метод передачи данных
        $this->setMethod('post');

        // Задаем атрибут class для формы
        $this->setAttrib('class', 'myfrm span5');
        $this->setAttrib('id', 'login-form');
        //$this->setAttrib('class', 'row');

        //---------- Имя пользователя --------------

        $this->addElement(
                'text', 'username', array(
            'label' => 'Имя входа пользователя',
            'required' => true,
            'filters' => array('StringTrim'),
        ));

        
        //---------- Пароль пользователя --------------

        $this->addElement('password', 'password', array(
            'label' => 'Пароль',
            'required' => true,
        ));
        
        //----------- Скрытый элемент для создания ссылки -----------
        //если пользователь забыл свой пароль
        $forgot_password = new Zend_Form_Element_Hidden('forgot_password', array(
                    'label' => $this->Translate('Забыли пароль') . '?',
                ));
        
        $forgot_password->clearDecorators();
        
        $forgot_password
                ->addDecorator('AnchorLabel', array('href' => $urlForgotPassword));
        
        $this->addElement($forgot_password);
        
        //--------- Добавим кнопку -----------
        $this->addElement('submit', 'send', array(
            'ignore' => true,
            'label' => 'Вход',
        ));
        
        $this->getElement('send')->setAttrib('class', 'btn btn-primary');

        //---------------- Группа Авторизационные данные ----------------
        // Группируем элементы
        // Группа полей связанных с авторизационными данными
        $this->addDisplayGroup(
                array('username', 'password', 'forgot_password','send'), 'authDataGroup', array(
            'legend' => 'Авторизационные данные'
                )
        );

        //Добавим скрытый элемент для перенаправления входа пользователя
        $this->addElement(
                'hidden', 'redirect', array(
        ));
        
    }

}