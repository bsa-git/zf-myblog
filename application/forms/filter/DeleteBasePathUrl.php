<?php

/**
 * Default_Form_Filter_DeleteBasePathUrl
 * 
 * Фильтр удаляет базовый путь из путей к ресурсам HTML
 *
 *
 * @uses       Zend_Filter
 * @package    Module-Default
 * @subpackage Forms.Filters
 */
class Default_Form_Filter_DeleteBasePathUrl implements Zend_Filter_Interface {

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
        return $this->deleteBasePathUrl($value);
    }

    /**
     * Удалить базовый путь из путей к ресурсам HTML
     *
     * @param string $html
     * @return string|bool при ошибке преобразования выдается FALSE
     */
    protected function deleteBasePathUrl($html) {
        //$domHtml
        $str_begin = '<?xml version="1.0" encoding="utf-8"?>' . "\n" . '<body>';
        $str_end = '</body>';
        //---------------------------

        // Заменим HTML сущность (не разрывного пробела) на его числовой код
        // т.к. иначе неверно формируется ХМЛ строка
//        $html = str_replace('&nbsp;', '&#160;', $html);
//        $html = str_replace('&mdash;', '&#8212;', $html);
//        $html = str_replace('&aelig;', '&#230;', $html);
//        $trans = get_html_translation_table(HTML_ENTITIES);
//        $html = html_entity_decode($html);
        //$a = htmlentities($orig);

//        $html = html_entity_decode($html);
        $filtrHtmlEntities = new Default_Form_Filter_HtmlEntities();
        $html = $filtrHtmlEntities->filter($html);
        // Добавим ХМЛ обертку
        $html = $str_begin . $html . $str_end;

        //Проверка на корректость XML
        $domDoc = new DOMDocument('1.0', 'utf-8');
        $success = $domDoc->loadXML($html);
        if(!$success){
            return FALSE;
        }

        // Скорректируем код
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
                            // было в виде: /zf-myblog/public/pic/1.gif
                            // стало в виде: /pic/1.gif
                            $baseURL = Default_Plugin_SysBox::getBaseURL();
                            $value = str_replace($baseURL, '', $value);
                            $result->setAttribute($attr, $value);
                        }  else {
                            // было в виде: https://mysite.com:8080/zf-myblog/public/pic/1.gif
                            // стало в виде: /pic/1.gif
                            $hostPortBaseURL = Default_Plugin_SysBox::getHostPortBaseURL();
                            $value = str_replace($hostPortBaseURL, '', $value);
                            $result->setAttribute($attr, $value);
                        }
                    }
                }
                $domDoc = $results->getDocument();
                $html = $domDoc->saveXml();
                $domQuery->setDocumentXml($html, "utf-8");
            }
        }
        // Удалим ХМЛ обертку
        $html = str_replace($str_begin, '', $html);
        $html = str_replace($str_end, '', $html);
//        $html = htmlentities($html);
        return $html;
    }

}