<?php

/**
 * Default_Form_Filter_Sanitize
 * 
 * Фильтр очищает от тегов HTML
 * и пробелов спереди и сзади строки
 *
 *
 * @uses       Zend_Filter
 * @package    Module-Default
 * @subpackage Forms.Filters
 */
class Default_Form_Filter_Sanitize implements Zend_Filter_Interface
{
    /**
     * Массив HTML тегов
     * которые разрешены HTML кода
     * 
     * @var array 
     */
    static $tags = array(
        // Блочные элементы
        'div' => array('id','class','style'),
        //---- текст ----
        'h1' => array('style'),
        'h2' => array('style'),
        'h3' => array('style'),
        'h4' => array('style'),
        'h5' => array('style'),
        'h6' => array('style'),
        'pre' => array('style'),// Определяет блок предварительно форматированного текста
        'span' => array('style'),// Предназначен для определения строчных элементов документа
        'sub' => array(),// Отображает шрифт в виде нижнего индекса
        'sup' => array(),// Отображает шрифт в виде верхнего индекса
        'b' => array('style'),// Устанавливает жирное начертание шрифта
        'strong' => array('style'),// Предназначен для акцентирования текста
        'em' => array('style'),// Предназначен для акцентирования текста
        'i' => array('style'),// Устанавливает курсивное начертание шрифта
        'p' => array('style'),// Определяет текстовый абзац
        'br' => array(),// Перевод строки
        'hr' => array(),// Горизонтальная разделительная линия
        'u' => array(),//Добавляет подчеркивание к тексту.
        'blockquote' => array(),// Цитата
        //--- список ----
        'ul' => array('style'),
        'li' => array('style'),
        'ol' => array('style'),
        //---- ссылка ----
        'a' => array('href', 'target', 'name'),
        //---- изображение ----
        'img' => array('src', 'alt', 'style'),
        //---- iframe ----
        'iframe' => array('id', 'width', 'height','frameborder', 'scrolling','src', 'style'),
        //---- видео ----
        'object' => array('classid','codebase','height','width'),
        'param' => array('name','value'),
        'embed' => array('height','width','pluginspage','quality','src','type'),
        //---- таблица ----
        'table' => array('align','background','bgcolor','border','bordercolor','cellpadding','cellspacing','cols','height','width','style'),
        'caption' => array('style'),
        'tbody' => array('align','bgcolor','valign','style'),
        'tr' => array('align','bgcolor','bordercolor','valign','style'),
        'td' => array('align','background','bgcolor','border','bordercolor','colspan','height','nowrap','rowspan','valign','width','style'),
        'th' => array('align','background','bgcolor','border','bordercolor','colspan','height','nowrap','rowspan','valign','width','style'),
    );

    /**
     * Признак использования фильтра тегов
     *
     * @var bool
     */
    protected  $isFilterTags = true;


    /**
     * Конструктор класса
     */
    public function __construct(array $options = NULL) {
        
        if($options){
            $this->isFilterTags = (bool) $options['isFilterTags'];
        }
    }
    
    /**
     * Производит фильтрацию в соответствии с назначением фильтра
     *
     * @param string $value
     * @return string
     */
    public function filter($value) 
    {
        return $this->cleanHtml($value);
    }
    
     /**
     * Очистить текст от HTML тегов и javascripts
     * временная ф-ия
     *
     * @param string $html
     * @param array $tags
     * @return string
     */
    protected function cleanHtml($html) {
        $chain = new Zend_Filter();
        //$this->isFilterTags = $this->getOption('isFilterTags');
        if($this->isFilterTags){
            $chain->addFilter(new Zend_Filter_StripTags(self::$tags));
        }
        $chain->addFilter(new Zend_Filter_StringTrim());

        $html = $chain->filter($html);

        $tmp = $html;
        while (1) {
            // Try and replace an occurrence of javascript:
            $html = preg_replace('/(<[^>]*)javascript:([^>]*>)/i', '$1$2', $html);
            

            // If nothing changed this iteration then break the loop
            if ($html == $tmp)
                break;

            $tmp = $html;
        }

        return $html;
    }

}