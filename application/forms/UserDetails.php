<?php

/**
 * Default_Form_UserDetails
 * 
 * Форма редактирования личных данных пользователя
 *
 *
 * @uses       Default_Form_MyForm
 * @package    Module-Default
 * @subpackage Forms
 */
class Default_Form_UserDetails extends Default_Form_MyForm {

    /**
     * Создание формы
     */
    public function init() {
        // Вызываем родительский метод
        parent::init();


        //---------------- Форма ----------------
        // Указываем action формы
        $urlAction = $this->getUrl('details', 'account');

        $this->setAction($urlAction);

        // Указываем метод формы
        $this->setMethod('post');

        // Задаем атрибут class для формы
        //$this->setAttrib('class', 'register');
        $this->setAttrib('class', 'myfrm span5');
        $this->setAttrib('id', 'details-form');


        //---------------- Группа Обновляемые данные пользователя ----------------
        // Email Используемый собственный элемент App_Form_Element_Email
        $email = new Default_Form_Element_Email('email', array(
                    'required' => FALSE,
                ));

        // Добавление элемента в форму
        $this->addElement($email);

        // Password элемент "Пароль". Значение проверяется валидатором App_Validate_Password
        $password = new Zend_Form_Element_Password('password', array(
                    'required' => FALSE,
                    'label' => 'Пароль',
                    'maxlength' => '30',
                    'validators' => array('Password'),
                ));

        $this->addElement($password);

        // Элемент "Подтверждение пароля".
        // Проверяется на совпадение с полем "Пароль" валидатором App_Validate_EqualInputs
        $passwordApprove = new Zend_Form_Element_Password('password_approve', array(
                    'required' => FALSE,
                    'label' => 'Подтвердите пароль',
                    'maxlength' => '30',
                    'validators' => array(array('EqualInputs', true, array('password'))),
                ));


        $this->addElement($passwordApprove);

        //First name user
        // Text элемент "Имя пользователя". Проверяется на алфавитные символы и цифры, а также на длину
        // Валидатор Alnum использует установленную локаль для определения алфавита символов.
        $first_name = new Zend_Form_Element_Text('first_name', array(
                    //'required' => true,
                    'label' => 'Имя пользователя',
                    'maxlength' => '150',
                    'size' => '60',
                    'validators' => array(
                        array('Alnum', true, array(true)),
                        array('StringLength', true, array(0, 150))
                    ),
                    'filters' => array('StringTrim'),
                ));

        $this->addElement($first_name);

        //Last name user
        // Text элемент "Фамилия пользователя". Проверяется на алфавитные символы и цифры, а также на длину
        // Валидатор Alnum использует установленную локаль для определения алфавита символов.
        $last_name = new Zend_Form_Element_Text('last_name', array(
                    //'required' => true,
                    'label' => 'Фамилия пользователя',
                    'maxlength' => '150',
                    'size' => '60',
                    'validators' => array(
                        array('Alnum', true, array(true)),
                        array('StringLength', true, array(0, 150))
                    ),
                    'filters' => array('StringTrim'),
                ));

        $this->addElement($last_name);


        //---------------- Настройки профиля ----------------
        // "Кол. сообщений в блоге"
        $num_posts = new Zend_Form_Element_Text('num_posts', array(
                    //'required' => true,
                    'label' => 'Количество сообщений в блоге',
                    'description' => 'Количество сообщений в блоге, которое Вы хотели бы показать, на вашей домашней странице',
                    'maxlength' => '20',
                    'size' => '20',
                    'validators' => array(
                        array('Int'),
                        array('StringLength', true, array(0, 20))
                    ),
                    'filters' => array('StringTrim'),
                ));

        $this->addElement($num_posts);

        // Признак публикации сообщений
        $blog_public = new Zend_Form_Element_Select('blog_public', array(
                    'label' => 'Публиковать Ваши сообщения?',
                    'description' => 'Хотите ли вы позволить просматривать ваши сообщения незарегистрированным пользователям?',
                    'multiOptions' => array('Нет', 'Да',),
                    'filters' => array('StringTrim'),
                ));

        $this->addElement($blog_public);

        //---------------- Группа Обновляемые данные пользователя ----------------
        //First name user
        // Text элемент "Имя пользователя". Проверяется на алфавитные символы и цифры, а также на длину
        // Валидатор Alnum использует установленную локаль для определения алфавита символов.
        $public_first_name = new Zend_Form_Element_Text('public_first_name', array(
                    //'required' => true,
                    'label' => 'Имя пользователя',
                    'maxlength' => '150',
                    'size' => '60',
                    'validators' => array(
                        array('Alnum', true, array(true)),
                        array('StringLength', true, array(0, 150))
                    ),
                    'filters' => array('StringTrim'),
                ));

        $this->addElement($public_first_name);

        //Last name user
        // Text элемент "Фамилия пользователя". Проверяется на алфавитные символы и цифры, а также на длину
        // Валидатор Alnum использует установленную локаль для определения алфавита символов.
        $public_last_name = new Zend_Form_Element_Text('public_last_name', array(
                    //'required' => true,
                    'label' => 'Фамилия пользователя',
                    'maxlength' => '150',
                    'size' => '60',
                    'validators' => array(
                        array('Alnum', true, array(true)),
                        array('StringLength', true, array(0, 150))
                    ),
                    'filters' => array('StringTrim'),
                ));

        $this->addElement($public_last_name);

        // Email Используемый собственный элемент App_Form_Element_Email
        $public_email = new Default_Form_Element_Email('public_email', array(
                    'required' => FALSE,
                ));

        // Добавление элемента в форму
        $this->addElement($public_email);


        //Public home phome
        // Text элемент "домашний телефон пользователя". Проверяется на цифры, а также на длину
        // 14 символов -> 0 380 044 XXX-XXXX
        $public_home_phone = new Zend_Form_Element_Text('public_home_phone', array(
                    //'required' => true,
                    'label' => 'Домашний телефон',
                    'maxlength' => '14',
                    'size' => '20',
                    'validators' => array(
                        array('Int'),
                        array('StringLength', true, array(0, 14))
                    ),
                    'filters' => array('StringTrim'),
                ));

        $this->addElement($public_home_phone);

        //Public home phome
        // Text элемент "рабочий телефон пользователя". Проверяется на цифры, а также на длину
        // 14 символов -> 0 380 044 XXX-XXXX
        $public_work_phone = new Zend_Form_Element_Text('public_work_phone', array(
                    //'required' => true,
                    'label' => 'Рабочий телефон',
                    'maxlength' => '14',
                    'size' => '20',
                    'validators' => array(
                        array('Int'),
                        array('StringLength', true, array(0, 14))
                    ),
                    'filters' => array('StringTrim'),
                ));

        $this->addElement($public_work_phone);

        //Public home phome
        // Text элемент "мобильный телефон пользователя". Проверяется на цифры, а также на длину
        // 14 символов -> 096 XXXX XXX
        $public_mobile_phone = new Zend_Form_Element_Text('public_mobile_phone', array(
                    //'required' => true,
                    'label' => 'Мобильный телефон',
                    'maxlength' => '10',
                    'size' => '20',
                    'validators' => array(
                        array('Int'),
                        array('StringLength', true, array(0, 10))
                    ),
                    'filters' => array('StringTrim'),
                ));

        $this->addElement($public_mobile_phone);


        //---------------- Submit ----------------
        // Кнопка Submit
        $submit = new Zend_Form_Element_Submit('send', array(
                    'label' => 'Сохранить',
                ));
        
        $submit->setAttrib('class', 'btn btn-primary');
        
        // Перезаписываем декораторы, что-бы
        //перезаписать стандартный набор декораторов для кнопки 'submit'
        $submit->setDecorators(array('ViewHelper'));

        $this->addElement($submit);

        //---------------- Группа Обновляемые данные пользователя ----------------
        // Группируем элементы
        // Группа полей связанных с авторизационными данными
        $this->addDisplayGroup(
                array('email', 'first_name', 'last_name', 'password', 'password_approve'), 'authDataGroup', array(
            'legend' => 'Личные данные пользователя',
                )
        );

        //---------------- Группа настроек профиля ----------------
        // Группа полей связанных с настройками профиля
        $this->addDisplayGroup(
                array('num_posts', 'blog_public'), 'configProfile', array(
            'legend' => 'Настройки профиля'
                )
        );

        //---------------- Группа Публичные данные пользователя ----------------
        // Группа полей связанных с личной информацией
        $this->addDisplayGroup(
                array('public_first_name', 'public_last_name',
                    'public_email', 'public_home_phone',
                    'public_work_phone', 'public_mobile_phone'), 'publicDataGroup', array(
            'legend' => 'Публичные данные пользователя'
                )
        );

        //---------------- Группа полей кнопок ----------------
        // Группа полей кнопок
        $this->addDisplayGroup(
                array('send'), 'buttonsGroup'
        );
    }

}