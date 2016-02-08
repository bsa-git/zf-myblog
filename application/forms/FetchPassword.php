<?php

/**
 * Default_Form_FetchPassword
 * 
 * Форма для тех, кто забыл паспорт
 *
 *
 * @uses       Default_Form_MyForm
 * @package    Module-Default
 * @subpackage Forms
 */
class Default_Form_FetchPassword extends Default_Form_MyForm {

    /**
     * Создание формы
     */
    public function init() {
        // Вызываем родительский метод
        parent::init();


        //---------------- Форма ----------------
        // Указываем action формы
        $urlAction = $this->getUrl('fetchpassword','account');
        
        $this->setAction($urlAction);

        //Зададим метод передачи данных
        $this->setMethod('post');

        // Задаем атрибут class для формы
        //$this->setAttrib('class', 'login');
        $this->setAttrib('class', 'myfrm');
        $this->setAttrib('id', 'fetchpassword-form');


        $this->addElement(
                'text', 'username', array(
            'label' => 'Имя входа пользователя',
            'required' => true,
            'filters' => array('StringTrim'),
        ));
        
        //Добавим кнопку 
        $this->addElement('submit', 'send', array(
            'ignore' => true,
            'label' => 'Получить пароль',
        ));
        
        $this->getElement('send')->setAttrib('class', 'btn btn-primary');

        //---------------- Группа Авторизационные данные ----------------
        // Группируем элементы
        // Группа полей связанных с авторизационными данными
        $this->addDisplayGroup(
                array('username', 'send'), 'authDataGroup', array(
            'legend' => 'Получить Ваш Новый Пароль'
                )
        );
    }

}