<?php

/**
 * Admin_Form_Users
 * 
 * Form - users
 * editing user data
 *
 *
 * @uses       Admin_Form_MyForm
 * @package    Module-Admin
 * @subpackage Forms
 * @author   Sergii Beskorovainyi <bsa2657@yandex.ru>
 * @license  MIT <http://www.opensource.org/licenses/mit-license.php>
 * @link     https://github.com/bsa-git/zf-myblog/
 */
class Admin_Form_Users extends Default_Form_MyForm {
    
    /**
     * Constructor
     *
     * @param mixed $options
     * @return void
     */
    public function __construct($options = null)
    {
        parent::__construct($options);
    }
    
    /**
     * Initialization form
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
        $this->setAttrib('class', 'myfrm');
        $this->setAttrib('id', 'details-form');

        //---------------- Группа Обновляемые данные пользователя ----------------

        // Username Text элемент "Имя входа пользователя". Проверяется на алфавитные символы и цифры, а также на длину
        // Валидатор Alnum использует установленную локаль для определения алфавита символов.
        $username = new Zend_Form_Element_Text('username', array(
                    'required' => FALSE,
                    'label' => $this->Translate('Имя входа пользователя') . ' (Login)',
                    'maxlength' => '30',
                    'validators' => array(
                        array('Alnum', true, array(true)),
                        array('UserName'),
                        array('NoDbRecordExists', true, array('users', 'username')),
                    ),
                    'filters' => array('StringTrim'),
                ));

        $this->addElement($username);

        // Тип пользователя (роль)
        $user_type = new Zend_Form_Element_Select('user_type', array(
                    'required' => FALSE,
                    'label' => 'Тип пользователя (роль)',
                    'multiOptions' => array('member', 'editor', 'administrator'),
                    'validators' => array(
                        array('UserType'),
                    ),
                    'filters' => array('StringTrim'),
                ));

        $this->addElement($user_type);

        // Признак активности пользователя
        $actual = new Zend_Form_Element_Select('actual', array(
                    'label' => 'Активен',
                    'description' => 'Активность пользователя позволяет ему заходить на сайт и создавать свои сообщения',
                    'multiOptions' => array('Да', 'Нет'),
                    'filters' => array('StringTrim'),
                ));

        $this->addElement($actual);


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
                    'validators' => array('PasswordAdmin'),
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

        //Дата регистрации пользователя
        // Элемент "Дата рождения". Элемент содержит нестандартный декоратор - javascript календарь
        $ts_created = new Zend_Form_Element_Text('ts_created', array(
                    'label' => 'Дата регистрации',
                    'maxlength' => '10',
                    'required' => FALSE,
                    //'validators' => array(array('Date', true, array('locale' => $this->_language))),
                    'validators' => array(array('Date', true, array('format' => 'yyyy-MM-dd'))),
                    'filters' => array('StringTrim'),
                ));

        // Удаляем все существующие декораторы, назначенные по умолчанию
        $ts_created->clearDecorators();

        // Назначаем новые, включая наш декоратор Calendar
        // Это необходимо для того что бы изображение календаря размещалось сразу за полем ввода
        $ts_created
                ->addDecorator('ViewHelper')
                ->addDecorator('Calendar')
                ->addDecorator('Errors')
                ->addDecorator('HtmlTag', array('tag' => 'dd'))
                ->addDecorator('Label', array('tag' => 'dt'));

        $this->addElement($ts_created);


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

        //---------------- Группа публичные данные пользователя ----------------
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

        // Перезаписываем декораторы, что-бы
        //перезаписать стандартный набор декораторов для кнопки 'submit'
        $submit->setDecorators(array('ViewHelper'));

        $this->addElement($submit);

        //---------------- Группа Обновляемые данные пользователя ----------------
        // Группируем элементы
        // Группа полей связанных с авторизационными данными
        $this->addDisplayGroup(
                array('username', 'user_type', 'actual','email', 'first_name', 'last_name', 'password', 'password_approve','ts_created'), 'authDataGroup', array(
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