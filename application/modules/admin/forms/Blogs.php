<?php

/**
 * Admin_Form_Blogs
 * 
 * Форма проверки блогов пользователя
 *
 *
 * @uses       Admin_Form_MyForm
 * @package    Module-Admin
 * @subpackage Forms
 */
class Admin_Form_Blogs extends Default_Form_MyForm {

    /**
     * Создание формы
     */
    public function init() {
        // Вызываем родительский метод
        parent::init();


        //============ Форма ==============
        // Признак активности сообщения
        $actual = new Zend_Form_Element_Select('actual', array(
                    'label' => 'Актуальный',
                    'description' => 'Актуальность сообщения позволяет пользователям видеть это сообщение на сайте',
                    'multiOptions' => array('Да', 'Нет'),
                    'filters' => array('StringTrim'),
                ));

        $this->addElement($actual);

        // Username Text элемент "Имя входа пользователя". Проверяется на алфавитные символы и цифры, а также на длину
        // Валидатор Alnum использует установленную локаль для определения алфавита символов.
        $username = new Zend_Form_Element_Text('username', array(
                    'required' => FALSE,
                    'label' => $this->Translate('Имя входа пользователя') . ' (Login)',
                    'maxlength' => '30',
                    'validators' => array(
                        array('Alnum', true, array(true)),
                        array('UserName'),
                    ),
                    'filters' => array('StringTrim'),
                ));

        $this->addElement($username);

        //Уникальный код (ID) пользователя
        $user_id = new Zend_Form_Element_Text('user_id', array(
                    //'required' => true,
                    'label' => 'ID пользователя',
                    'validators' => array(
                        array('Int'),
                        array('NotEmpty'),
                    ),
                    'filters' => array('StringTrim'),
                ));
        $validator = new Zend_Validate_Db_RecordExists(
                        array(
                            'table' => 'users',
                            'field' => 'id'
                        )
        );
        $user_id->addValidator($validator);
        $this->addElement($user_id);

        //Уникальный код (ID) сообщения
        $post_id = new Zend_Form_Element_Text('post_id', array(
                    //'required' => true,
                    'label' => 'ID пользователя',
                    'validators' => array(
                        array('Int'),
                        array('NotEmpty'),
                    ),
                    'filters' => array('StringTrim'),
                ));
        $validator = new Zend_Validate_Db_RecordExists(
                        array(
                            'table' => 'blog_posts',
                            'field' => 'id'
                        )
        );
        $post_id->addValidator($validator);
        $this->addElement($post_id);
        
        //Название сообщения
        $title = new Zend_Form_Element_Text('title', array(
                    //'required' => true,
                    'label' => 'Название сообщения',
                    'maxlength' => '255',
                    'size' => '60',
                    'validators' => array(
                        array('Alnum', true, array(true)),
                        array('StringLength', true, array(0, 255))
                    ),
                    'filters' => array('StringTrim'),
                ));

        $this->addElement($title);

        // Статус статьи
        $status = new Zend_Form_Element_Select('status', array(
                    'required' => FALSE,
                    'label' => 'Статус сообщения',
                    'multiOptions' => array('D', 'L'),
                    'validators' => array(
                        array('PostStatus'),
                    ),
                    'filters' => array('StringTrim'),
                ));

        $this->addElement($status);

        // url уникальный адрес сообщения
        $url = new Zend_Form_Element_Text('url', array(
                    'required' => FALSE,
                    'label' => 'URL сообщения',
                    'filters' => array('StringTrim'),
                ));

        // Добавление элемента в форму
        $this->addElement($url);


        //Дата создания сообщения
        $ts_created = new Zend_Form_Element_Text('ts_created', array(
                    'label' => 'Дата регистрации',
                    'maxlength' => '10',
                    'required' => FALSE,
                    'validators' => array(array('Date', true, array('format' => 'yyyy-MM-dd'))),
                    'filters' => array('StringTrim'),
                ));

        $this->addElement($ts_created);

        //Дата публикации сообщения
        $ts_published = new Zend_Form_Element_Text('ts_published', array(
                    'label' => 'Дата регистрации',
                    'maxlength' => '19',
                    'required' => FALSE,
                    'validators' => array(array('Date', true, array('format' => 'yyyy-MM-dd HH:mm:ss'))),
                    'filters' => array('StringTrim'),
                ));

        $this->addElement($ts_published);

        //Метка сообщения
        $tag = new Zend_Form_Element_Text('tag', array(
                    //'required' => true,
                    'label' => 'Метка',
                    'maxlength' => '255',
                    'size' => '60',
                    'validators' => array(
                        array('StringLength', true, array(0, 255))
                    ),
                    'filters' => array('StringTrim'),
                ));
        $tag->addValidator(new Zend_Validate_Regex(array('pattern' => '/^[\w-]+$/')));
        $this->addElement($tag);

        //Комментарий
        $comment = new Zend_Form_Element_Text('comment', array(
                    //'required' => true, ї Ї є Є
                    'label' => 'Комментарий',
                    'maxlength' => '255',
                    'size' => '60',
                    'validators' => array(
                        array('StringLength', true, array(0, 255))
                    ),
                    'filters' => array('StringTrim'),
                ));
        $this->addElement($comment);

        //Описание
        $description = new Zend_Form_Element_Text('description', array(
                    //'required' => true,
                    'label' => 'Описание',
                    'maxlength' => '255',
                    'size' => '60',
                    'validators' => array(
                        array('StringLength', true, array(0, 255))
                    ),
                    'filters' => array('StringTrim'),
                ));
        $this->addElement($description);
    }

}