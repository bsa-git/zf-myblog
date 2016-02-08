<?php

/**
 * Default_Form_BlogPostLocation
 * 
 * Форма для управления географическими координатами
 *
 *
 * @uses       Default_Form_MyForm
 * @package    Module-Default
 * @subpackage Forms
 */
class Default_Form_BlogPostLocation extends Default_Form_MyForm {


    /**
     * Конструктор обьекта
     *
     * @param int $post_id
     */
    public function __construct() {
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
        //$urlAction = $this->getUrl('locationsmanage', 'blogmanager');

        //$this->setAction($urlAction);

        //Зададим метод передачи данных
        //$this->setMethod('post');


        //------------ Добавим краткое описание координаты --------------

        $elDescription = new Zend_Form_Element_Text('description', array(
                'label' => 'Описание координаты',
                'validators' => array(
                        //array('Alnum', true, array('allowWhiteSpace' => true)),
                    ),
                    'filters' => array(
                        array('StringTrim'),
                        array('StripTags'),
                        ),
                ));

        $this->addElement($elDescription);
        
        //------------ Добавим содержание координаты --------------

        $elContent = new Zend_Form_Element_Textarea('content', array(
                'label' => 'Содержание координаты',
                'validators' => array(
                        //array('Alnum', true, array('allowWhiteSpace' => true)),
                    ),
                    'filters' => array(
                        array('Sanitize'),
                        ),
                ));

        $this->addElement($elContent);

        //------------ Добавим поле подробнее... --------------

        $elDetails = new Zend_Form_Element_Textarea('details', array(
                'label' => 'Подробная информация',
                'validators' => array(
                        //array('Alnum', true, array('allowWhiteSpace' => true)),
                    ),
                    'filters' => array(
                        array('Sanitize'),
                        ),
                ));

        $this->addElement($elDetails);


        //------------ Добавим координату по долготе --------------

        $elLongitude = new Zend_Form_Element_Text('longitude', array(
                    'label' => 'Долгота',
                    'validators' => array(
                        array('Float', true, array('locale' => 'en')),
                    ),
                    'filters' => array(
                        array('StringTrim'),),
                ));

        $this->addElement($elLongitude);

        //------------ Добавим координату по широте --------------

        $elLatitude = new Zend_Form_Element_Text('latitude', array(
                    'label' => 'Широта',
                    'validators' => array(
                        array('Float', true, array('locale' => 'en')),
                    ),
                    'filters' => array(
                        array('StringTrim'),),
                ));

        $this->addElement($elLatitude);

        //------------ Добавим коррекцию для правильного отображения окна --------------

        $elCorrection = new Zend_Form_Element_Text('correction', array(
                    'label' => 'Коррекция',
                    'validators' => array(
                        array('Int', true, array('locale' => 'en')),
                    ),
                    'filters' => array(
                        array('StringTrim'),),
                ));

        $this->addElement($elCorrection);

    }

}