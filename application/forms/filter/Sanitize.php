<?php

/**
 * Default_Form_Filter_Sanitize
 * 
 * Filter - clears HTML tags and spaces in front and behind the line
 *
 *
 * @uses       Zend_Filter
 * @package    Module-Default
 * @subpackage Forms.Filters
 * @author   Sergii Beskorovainyi <bsa2657@yandex.ru>
 * @license  MIT <http://www.opensource.org/licenses/mit-license.php>
 * @link     https://github.com/bsa-git/zf-myblog/
 */
class Default_Form_Filter_Sanitize implements Zend_Filter_Interface
{
    /**
     * An array of HTML tags are Allowed HTML code
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
        'code' => array('style'),// Определяет блок предварительно форматированного текста
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
     * Is filter tags
     *
     * @var bool
     */
    protected  $isFilterTags = true;


    /**
     * Constructor
     */
    public function __construct(array $options = NULL) {
        
        if($options){
            $this->isFilterTags = (bool) $options['isFilterTags'];
        }
    }
    
    /**
     * Performs filtering in accordance with the purpose of the filter
     *
     * @param string $value
     * @return string
     */
    public function filter($value) 
    {
        return $this->cleanHtml($value);
    }
    
     /**
     * Clear text from HTML tags and javascripts
     *
     * @param string $html
     * @return string
     */
    protected function cleanHtml($html) {
        $chain = new Zend_Filter();

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