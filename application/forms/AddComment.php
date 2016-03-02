<?php

/**
 * Default_Form_AddComment
 * 
 * Form - add user comment 
 *
 *
 * @uses       Default_Form_MyForm
 * @package    Module-Default
 * @subpackage Forms
 * @author   Sergii Beskorovainyi <bsa2657@yandex.ru>
 * @license  MIT <http://www.opensource.org/licenses/mit-license.php>
 * @link     https://github.com/bsa-git/zf-myblog/
 */
class Default_Form_AddComment extends Default_Form_MyForm {
    
    /**
     * Post id
     * 
     * @var int
     */
    public $post_id = 0;
    
    /**
     * Username
     * 
     * @var string
     */
    public $username = 0;

    /**
     * Constructor
     *
     * @param Zend_Db_Adapter_Abstract $db
     * @param string $username
     * @param int $post_id
     */
    public function __construct($username,$post_id = 0) {
        $this->username = $username;
        $this->post_id = $post_id;
        
        parent::__construct();
    }
    
    /**
     * Initialization form
     */
    public function init() {

        parent::init();

        //---------------- Форма ----------------
        // Указываем action формы
        $urlAction = $this->getRouteUrl('get_comments', array(
            'username'=>$this->username,
            'post_id'=>$this->post_id
        ));
        
        $this->setAction($urlAction);

        // Указываем метод формы
        $this->setMethod('post');

        // Задаем атрибут class для формы
        //$this->setAttrib('class', 'register');
        $this->setAttrib('class', 'myfrm');
        $this->setAttrib('id', 'form-add-comment');
        
        //---------------- Комментарий ----------------
        // Text элемент 
        $ckeditor_comment = new Zend_Form_Element_Textarea('ckeditor_comment', array(
                    'label' => $this->Translate('Текст комментария') . ':',
                    'required' => TRUE,
                    'rows' => 12,
                    'cols' => 60,
                    'validators' => array(
                        array('StringLength', true, array(0, 1024)),
                    ),
                    'filters' => array('Sanitize'),
                ));

        $this->addElement($ckeditor_comment);

        //---------------- Captcha ----------------
        // Элемент CAPTCHA, защита от спама
//        $urlBase = Default_Plugin_SysBox::getBaseURL();
//        $captcha = new Zend_Form_Element_Captcha('captcha', array(
//                    'label' => 'Введите символы',
//                    'captcha' => array(
//                        'captcha' => 'Image', // Тип CAPTCHA
//                        'wordLen' => 4, // Количество генерируемых символов
//                        'width' => 260, // Ширина изображения
//                        'timeout' => 120, // Время жизни сессии хранящей символы 120
//                        'expiration' => 300, // Время жизни изображения в файловой системе
//                        'font' => APPLICATION_PUBLIC . '/fonts/arial.ttf', // Путь к шрифту
//                        'imgDir' => APPLICATION_PUBLIC . '/images/captcha/', // Путь к изобр.
//                        'imgUrl' => $urlBase . '/images/captcha/', // Адрес папки с изображениями
//                        'gcFreq' => 5        // Частота вызова сборщика мусора
//                    ),
//                ));
//
//        $this->addElement($captcha);

        //----------- Скрытый элемент уникального кода автора сообщения -----------

//        $user_id = new Zend_Form_Element_Hidden('user_id');
//        $user_id->setValue($this->user_id);
//        $this->addElement($user_id);
        
        
        //---------------- Submit ----------------
        // Кнопка Submit
        $send_comment = new Zend_Form_Element_Submit('send_comment', array(
                    'label' => 'Комментировать',
                ));
        
        $send_comment->setAttrib('class', 'btn btn-primary');

        // Перезаписываем декораторы, что-бы
        //перезаписать стандартный набор декораторов для кнопки 'submit'
        $send_comment->setDecorators(array('ViewHelper'));

        $this->addElement($send_comment);
        
        //---------------- Reset ----------------
        // Кнопка Reset, возвращает форму в начальное состояние
        $reset_comment = new Zend_Form_Element_Reset('reset_comment', array(
                    'label' => 'Очистить',
                ));

        $reset_comment->setAttrib('class', 'btn');
        
        // Перезаписываем декораторы, что-бы выставить две кнопки в ряд
        $reset_comment->setDecorators(array('ViewHelper'));
        $this->addElement($reset_comment);


        //---------------- Группа комментария ----------------
        // Защита от спама
        $this->addDisplayGroup(//,'captcha' ,'type_action' , , 'user_id'
                array('ckeditor_comment' ), 'commentGroup', array(
            'legend' => 'Оставить свой комментарий'
                )
        );

        //---------------- Группа полей кнопок ----------------
        $this->addDisplayGroup(
                array('send_comment', 'reset_comment'), 'buttonsGroup'
        );
    }

}