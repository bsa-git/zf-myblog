<?php

/**
 * Default_Form_Registration
 * 
 * Form - user registration
 *
 *
 * @uses       Default_Form_MyForm
 * @package    Module-Default
 * @subpackage Forms
 * @author   Sergii Beskorovainyi <bsa2657@yandex.ru>
 * @license  MIT <http://www.opensource.org/licenses/mit-license.php>
 * @link     https://github.com/bsa-git/zf-myblog/
 */
class Default_Form_UserRegistration extends Default_Form_MyForm {

    /**
     * Initialization form
     */
    public function init() {

        parent::init();

        //---------------- Форма ----------------
        // Указываем action формы
        $urlAction = $this->getUrl('register', 'account');
        
        $this->setAction($urlAction);

        // Указываем метод формы
        $this->setMethod('post');

        // Задаем атрибут class для формы
        //$this->setAttrib('class', 'register');
        $this->setAttrib('class', 'myfrm span5');
        $this->setAttrib('id', 'registration-form');

        //---------------- Username ----------------
        // Text элемент "Имя входа пользователя". Проверяется на алфавитные символы и цифры, а также на длину
        // Валидатор Alnum использует установленную локаль для определения алфавита символов.
        $username = new Zend_Form_Element_Text('username', array(
                    'required' => true,
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

        //---------------- Email ----------------
        // Используемый собственный элемент App_Form_Element_Email
        $email = new Default_Form_Element_Email('email', array(
                    'required' => true,
                ));

        // Добавление элемента в форму
        $this->addElement($email);

        //---------------- First name user ----------------
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

        //---------------- Last name user ----------------
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


        //---------------- Captcha ----------------
        // Элемент CAPTCHA, защита от спама
        $urlBase = Default_Plugin_SysBox::getBaseURL();
        $captcha = new Zend_Form_Element_Captcha('captcha', array(
                    'label' => 'Введите символы',
                    'captcha' => array(
                        'captcha' => 'Image', // Тип CAPTCHA
                        'wordLen' => 4, // Количество генерируемых символов
                        'width' => 260, // Ширина изображения
                        'timeout' => 120, // Время жизни сессии хранящей символы 120
                        'expiration' => 300, // Время жизни изображения в файловой системе
                        'font' => APPLICATION_PUBLIC . '/fonts/arial.ttf', // Путь к шрифту
                        'imgDir' => APPLICATION_PUBLIC . '/images/captcha/', // Путь к изобр.
                        'imgUrl' => $urlBase . '/images/captcha/', // Адрес папки с изображениями
                        'gcFreq' => 5        // Частота вызова сборщика мусора
                    ),
                ));

        $this->addElement($captcha);


        //---------------- Submit ----------------
        // Кнопка Submit
        $submit = new Zend_Form_Element_Submit('send', array(
                    'label' => 'Зарегистрироваться',
                ));
        
        $submit->setAttrib('class', 'btn btn-primary');

        // Перезаписываем декораторы, что-бы
        //перезаписать стандартный набор декораторов для кнопки 'submit'
        $submit->setDecorators(array('ViewHelper'));

        $this->addElement($submit);

        //---------------- Reset ----------------
        // Кнопка Reset, возвращает форму в начальное состояние
        $reset = new Zend_Form_Element_Reset('reset', array(
                    'label' => 'Очистить',
                ));

        $reset->setAttrib('class', 'btn');
        
        // Перезаписываем декораторы, что-бы выставить две кнопки в ряд
        $reset->setDecorators(array('ViewHelper'));
        $this->addElement($reset);

        //---------------- Группа Авторизационные данные ----------------
        // Группируем элементы
        // Группа полей связанных с авторизационными данными
        $this->addDisplayGroup(
                array('username', 'email'), 'authDataGroup', array(
            'legend' => 'Авторизационные данные'
                )
        );

        //---------------- Группа Личная информация ----------------
        // Группа полей связанных с личной информацией
        $this->addDisplayGroup(
                array('first_name', 'last_name'), 'privateDataGroup', array(
            'legend' => 'Личная информация'
                )
        );

        //---------------- Группа Защита от спама ----------------
        // Защита от спама
        $this->addDisplayGroup(
                array('captcha'), 'captchaGroup', array(
            'legend' => 'Защита от спама'
                )
        );

        //---------------- Группа полей кнопок ----------------
        $this->addDisplayGroup(
                array('send', 'reset'), 'buttonsGroup'
        );
    }

}