<?php

/**
 * Default_Form_Filter_AddBasePathUrl
 * 
 * Filter - add the base path to the URL of HTML Resources
 *
 *
 * @uses       Zend_Filter
 * @package    Module-Default
 * @subpackage Forms.Filters
 * @author   Sergii Beskorovainyi <bsa2657@yandex.ru>
 * @license  MIT <http://www.opensource.org/licenses/mit-license.php>
 * @link     https://github.com/bsa-git/zf-myblog/
 */
class Default_Form_Filter_AddBasePathUrl implements Zend_Filter_Interface {

    /**
     * Array of HTML tags that need to be corrected
     * 
     * @var array 
     */
    static $tags = array(
        // link
        'a' => 'href',
        // image
        'img' => 'src',
        // iframe
        'iframe' => 'src',
        // video
        'param' => 'value',
        'embed' => 'src',
    );
    /**
     * Options
     *
     * @var array
     */
    protected $_options;

    /**
     * Constructor
     */
    public function __construct(array $options = NULL) {

        if ($options) {
            $this->_options = $options;
        }
    }

    /**
     * Performs filtering in accordance with the purpose of the filter
     *
     * @param string $value
     * @return string
     */
    public function filter($value) {
        return $this->addBasePathUrl($value);
    }

    /**
     * add the base path to the URL of HTML Resources
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

        // Check for correct XML
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
                foreach ($results as $result) { // $result variable is of DOMElement type 
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