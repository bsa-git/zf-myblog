<?php

/**
 * Default_Form_BlogPost
 * 
 * Форма блога
 *
 *
 * @uses       Default_Form_MyForm
 * @package    Module-Default
 * @subpackage Forms
 */
class Default_Form_BlogPost extends Default_Form_MyForm {

    /**
     * Адаптер базы данных
     * 
     * @var Zend_Db_Adapter_Abstract
     */
    protected $db = null;
    /**
     * Обьект пользователя
     * 
     * @var Default_Model_DbTable_User
     */
    public $user = null;
    /**
     * Обьект блога пользователя
     * 
     * @var Default_Model_DbTable_BlogPost
     */
    public $post = null;

    /**
     * Конструктор обьекта
     * 
     * @param Zend_Db_Adapter_Abstract $db
     * @param int $user_id
     * @param int $post_id 
     */
    public function __construct($db, $user_id, $post_id = 0) {

        $this->db = $db;

        $this->user = new Default_Model_DbTable_User($db);
        $this->user->load($user_id);

        $this->post = new Default_Model_DbTable_BlogPost($db);
        $this->post->loadForUser($this->user->getId(), $post_id);

        if (!$this->post->isSaved()) {
            $this->post->user_id = $this->user->getId();
        }

        parent::__construct();
    }

    /**
     * Создание формы
     */
    public function init() {
        // Вызываем родительский метод
        parent::init();


        //---------------- Форма ----------------
        // Указываем action формы
        $urlAction = $this->getUrl('edit', 'blogmanager');
        $urlAction .= '?id=' . $this->post->getId();

        $this->setAction($urlAction);

        //Зададим метод передачи данных
        $this->setMethod('post');

        // Задаем атрибут class для формы
        $this->setAttrib('class', 'myfrm');
        $this->setAttrib('id', 'blogpost-form');


        //------------ Добавим краткое название блога --------------

        $elTitle = new Zend_Form_Element_Text('title', array(
                    'label' => 'Название',
                    'maxlength' => '255',
                    'size' => '60',
                    'required' => true,
                    'validators' => array(
//                        array('Alnum', true, array(true)),
                        array('StringLength', true, array(0, 255)),
                    ),
                    'filters' => array(
                        array('StringTrim'),
                        array('StripTags'),),
                ));

        //Установим название, если блог существует
        $this->addElement($elTitle);

        if ($this->post->profile->title) {
            $this->setDefault('title', $this->post->profile->title);
        }

        //---------------- Дата создания записи в блоге ----------------
        // Элемент "Дата рождения". Элемент содержит нестандартный декоратор - javascript календарь
        $elDatePost = new Zend_Form_Element_Text('ts_created', array(
                    'label' => 'Дата создания',
                    'maxlength' => '10',
                    'required' => true,
                    'validators' => array(array('Date', true, array('locale' => $this->_language))),
                    'filters' => array('StringTrim'),
                ));

        // Удаляем все существующие декораторы, назначенные по умолчанию
        $elDatePost->clearDecorators();

        // Назначаем новые, включая наш декоратор Calendar
        // Это необходимо для того что бы изображение календаря размещалось сразу за полем ввода
        $elDatePost
                ->addDecorator('ViewHelper')
                ->addDecorator('Calendar')
                ->addDecorator('Errors')
                ->addDecorator('HtmlTag', array('tag' => 'dd'))
                ->addDecorator('Label', array('tag' => 'dt'));

        $this->addElement($elDatePost);

        //Установим дату по умолчанию
        $tsCreated = $this->post->ts_created;
        $sd = new Default_Plugin_SimpleDate($tsCreated);
        $this->setDefault('ts_created', $sd->to_display_date());
        
        //---------- Добавим элемент описания сообщения в блоге ------------
        
        //$this->addElement('textarea', 'content', array(
        $elDescription = new Zend_Form_Element_Textarea('ckeditor_description', array(
                    'label' => $this->Translate('Краткое описание сообщения') . ':',
                    'required' => false,
//                    'rows' => 8,
//                    'cols' => 55,
                    'validators' => array(
//                        array('StringLength', true, array(0, 1024)),
                    ),
                    'filters' => array('Sanitize'),
                ));
        

        $this->addElement($elDescription);
        
        //Установим краткое описание сообщения, если оно существует
        if ($this->post->profile->description) {
            $this->setDefault('ckeditor_description', $this->post->profile->description);
        }
        //---------- Добавим элемент содержания блога ------------
        
        //$this->addElement('textarea', 'content', array(
        $elContent = new Zend_Form_Element_Textarea('ckeditor_content', array(
                    'label' => $this->Translate('Ваше сообщение') . ':',
                    'required' => true,
//                    'rows' => 24,
//                    'cols' => 55,
                        'filters' => array('Sanitize'),
                ));
        

        $this->addElement($elContent);

        //Установим содержания блога, если оно существует
        if ($this->post->profile->content) {
            $this->setDefault('ckeditor_content', $this->post->profile->content);
        }


        //-------- Добавим кнопки --------------
        //Установим значение кнопки
        if ($this->post->isLive()) {
            $valueSubmit = 'Сохранить изменения';
        } elseif ($this->post->isSaved()) {
            $valueSubmit = 'Сохранить изменения и опубликовать';
        } else {
            $valueSubmit = 'Создать и опубликовать';
        }

        $this->addElement('submit', 'send', array(
            'ignore' => true,
            'label' => $valueSubmit,
        ));
        
        $this->getElement('send')->setAttrib('class', 'btn btn-primary');

        //Перезапишем стандартные декораторы, для размещения двух кнопок рядом
        $this->getElement('send')->setDecorators(array('ViewHelper'));

        if (!$this->post->isLive()) {
            $this->addElement('submit', 'preview', array(
                'ignore' => true,
                'label' => 'Предварительный просмотр',
            ));

            //Перезапишем стандартные декораторы, для размещения двух кнопок рядом
            $this->getElement('preview')->setDecorators(array('ViewHelper'));
        }

        //---------------- Группа данные блога ----------------
        // Группируем элементы
        // Группа полей связанных с авторизационными данными
        $this->addDisplayGroup(
                array('title', 'ts_created', 'ckeditor_description','ckeditor_content', 'send', 'preview'), 'blogDataGroup', array(
            'legend' => 'Подробная информация'
                )
        );
    }

}