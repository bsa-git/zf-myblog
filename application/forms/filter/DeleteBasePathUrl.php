<?php

/**
 * Default_Form_Filter_DeleteBasePathUrl
 * 
 * Filter - removes the base path of the URL to HTML resources
 *
 *
 * @uses       Zend_Filter
 * @package    Module-Default
 * @subpackage Forms.Filters
 * @author   Sergii Beskorovainyi <bsa2657@yandex.ru>
 * @license  MIT <http://www.opensource.org/licenses/mit-license.php>
 * @link     https://github.com/bsa-git/zf-myblog/
 */
class Default_Form_Filter_DeleteBasePathUrl implements Zend_Filter_Interface {

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
        return $this->deleteBasePathUrl($value);
    }

    /**
     * Removes the base path of the URL to HTML resources
     *
     * @param string $html
     * @return string|bool If error, then output FALSE
     */
    protected function deleteBasePathUrl($html) {
        //$domHtml
        $str_begin = '<?xml version="1.0" encoding="utf-8"?>' . "\n" . '<body>';
        $str_end = '</body>';
        //---------------------------

        $filtrHtmlEntities = new Default_Form_Filter_HtmlEntities();
        $html = $filtrHtmlEntities->filter($html);
        $html = $str_begin . $html . $str_end;

        // Check for correct XML
        $domDoc = new DOMDocument('1.0', 'utf-8');
        $success = $domDoc->loadXML($html);
        if(!$success){
            return FALSE;
        }

        // Correct code
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
                            // it was: /zf-myblog/public/pic/1.gif
                            // it is: /pic/1.gif
                            $baseURL = Default_Plugin_SysBox::getBaseURL();
                            $value = str_replace($baseURL, '', $value);
                            $result->setAttribute($attr, $value);
                        }  else {
                            // it was: https://mysite.com:8080/zf-myblog/public/pic/1.gif
                            // it is: /pic/1.gif
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

        $html = str_replace($str_begin, '', $html);
        $html = str_replace($str_end, '', $html);
        return $html;
    }

}