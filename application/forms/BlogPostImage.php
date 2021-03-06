<?php

/**
 * Default_Form_BlogPostImage
 * 
 * Form - load images
 *
 *
 * @uses       Default_Form_MyForm
 * @package    Module-Default
 * @subpackage Forms
 * @author   Sergii Beskorovainyi <bsa2657@yandex.ru>
 * @license  MIT <http://www.opensource.org/licenses/mit-license.php>
 * @link     https://github.com/bsa-git/zf-myblog/
 */
class Default_Form_BlogPostImage extends Default_Form_MyForm {

    /**
     * Post id
     * 
     * @var int
     */
    public $post_id = 0;

    /**
     * Constructor
     *
     * @param Zend_Db_Adapter_Abstract $db
     * @param int $post_id
     */
    public function __construct($post_id = 0) {
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
        $urlAction = $this->getUrl('images', 'blogmanager');

        $this->setAction($urlAction);

        //Зададим метод передачи данных
        $this->setMethod('post');

        // Зададим тип передачи данных на сервер
        $this->setAttrib('enctype', 'multypart/form-data');

        // Задаем атрибут class для формы
        $this->setAttrib('class', 'myfrm');
        $this->setAttrib('id', 'blogpostimage-form');

        //--------- Элемент Hidden -----------------//

        //Добавим скрытый элемент для перенаправления входа пользователя
//        $elId = new Zend_Form_Element_Hidden('id');
//        $this->addElement($elId);
//
//         if ($this->post_id) {
//            $this->setDefault('id', $this->post_id);
//        }
        
        

        //--------- Элемент Файл -----------------//

        $elFile = new Zend_Form_Element_File('image');
        $elFile->setLabel('Выбрать файл с локального компьютера');
        $path = Default_Model_DbTable_BlogPostImage::GetUploadPath();
        $elFile->setDestination($path);

        //Будем грузить только один файл
        $elFile->addValidator('Count',false, 1);

        //Будем грузить файл размером - 1Мб
        $elFile->addValidator('Size',false, 1024000);

        //Будем грузить файл типа: JPEG, PNG, GIF
        $elFile->addValidator('Extension',false, 'jpg,png,gif');

        //$elFile->setDecorators(array('ViewHelper'));


        $this->addElement($elFile);

        //--------- Кнопка submit -----------------//

        //Добавим кнопку
        $this->addElement('submit', 'upload', array(
            'ignore' => true,
            'label' => 'Загрузить изображение',
        ));
        
        $this->getElement('upload')->setAttrib('class', 'btn btn-primary');

       $this->getElement('upload')->setDecorators(array('ViewHelper'));
    }

}