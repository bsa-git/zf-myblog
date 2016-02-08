<?php

/**
 * Default_Form_Filter_AddBasePathUrl
 * 
 * Фильтр добавляет базовый путь к URL ресурсам HTML
 *
 *
 * @uses       Zend_Filter
 * @package    Module-Default
 * @subpackage Forms.Filters
 */
class Default_Form_Filter_AddBasePathUrl implements Zend_Filter_Interface {

    /**
     * Массив HTML тегов
     * которые нужно корректировать
     * 
     * @var array 
     */
    static $tags = array(
        // ссылка
        'a' => 'href',
        // изображение
        'img' => 'src',
        // iframe
        'iframe' => 'src',
        // видео
        'param' => 'value',
        'embed' => 'src',
    );
    /**
     * Признак использования фильтра тегов
     *
     * @var array
     */
    protected $_options;

    /**
     * Конструктор класса
     */
    public function __construct(array $options = NULL) {

        if ($options) {
            $this->_options = $options;
        }
    }

    /**
     * Производит фильтрацию в соответствии с назначением фильтра
     *
     * @param string $value
     * @return string
     */
    public function filter($value) {
        return $this->addBasePathUrl($value);
    }

    /**
     * Удалить базовый путь из путей к ресурсам HTML
     *
     * @param string $html
     * @return string
     */
    protected function addBasePathUrl($html) {
        //$domHtml
        $str_begin = '<?xml version="1.0" encoding="utf-8"?>' . "\n" . '<body>';
        $str_end = '</body>';
        //---------------------------
        $html = str_replace('&nbsp;', '&#160;', $html);
        $html = $str_begin . $html . $str_end;

        //Проверка на корректость XML
        $domDoc = new DOMDocument('1.0', 'utf-8');
        $success = $domDoc->loadXML($html);
        if(!$success){
            return FALSE;
        }
        $domQuery = new Zend_Dom_Query();
        $domQuery->setDocumentXml($html,"utf-8");
        foreach (self::$tags as $tag => $attr) {
            $results = $domQuery->query($tag);
            if ($results->count()) {
                foreach ($results as $result) { // переменная $result имеет тип DOMElement
                    if ($result->hasAttribute($attr)) {
                        $value = $result->getAttribute($attr);
                        $st = new Default_Plugin_String($value);
                        if($st->beginsWith('/')){
                            $baseURL = Default_Plugin_SysBox::getUrlRes($value);
                            $result->setAttribute($attr, $baseURL);
                        }
                    }
                }
                $domDoc = $results->getDocument();
                $html = $domDoc->saveXml();
                $domQuery->setDocumentXml($html, "utf-8");
            }
        }
        $html = str_replace($str_begin, '', $html);
        $html = str_replace($str_end, '', $html);
        return $html;
    }

}