<?php

/*
 * Plugin - Default_Plugin_DomDocumentBox
 * 
 * дополнительные ф-ии для работы с классом PHP -> DomDocument
 * 
 * @uses       DOMDocument
 * @package    Module-Default
 * @subpackage Plugins
 */

class Default_Plugin_DomBox extends DOMDocument {

    public $doctype;
    public $head;
    public $title = 'Sensei Ninja';
    public $body;
    private $styles;
    private $metas;
    private $scripts;

    /**
     * These tags must always self-terminate. Anything else must never self-terminate.
     * 
     * @var array
     */
    public $selfTerminate = array(
        'area', 'base', 'basefont', 'br', 'col', 'frame', 'hr', 'img', 'input', 'link', 'meta', 'param'
    );

        
    //----------- Добавление узлов ---------//

    /**
     * appendChilds
     *
     * Добавляет в родительский узел  другой узел со всеми его дочерними узлами
     *
     * @param DOMNode $node   // родительский узел
     * @param DOMNode $appendChild   // другой узел
     */
    function appendChilds($node, $appendChild) {
        $cloneChild = $this->importNode($appendChild->cloneNode(false), true);
        $newNode = $node->appendChild($cloneChild);
        if ($appendChild->childNodes) {
            foreach ($appendChild->childNodes as $child) {
                $this->appendChilds($newNode, $child);
            }
        }
    }
    
    //------------ Удаление узлов --------------//

    /**
     * remove_children
     * 
     * Ф-ия удаляет все дочерние узлы из родительского узла $node
     * 
     * @param DOMNode $node // родительский узел, из которого будут удалены все дочерние узлы
     * 
     * @return void
     */
    static function remove_childrens(&$node) {
        while ($node->firstChild) {
            while ($node->firstChild->firstChild) {
                self::remove_childrens($node->firstChild);
            }
            $node->removeChild($node->firstChild);
        }
    }

    /**
     * deleteNode
     * 
     * Ф-ия удаляет дочерний узел $node и все его дочерние узлы
     * 
     * @param DOMNode $node // дочерний узел
     * 
     * @return DOMNode
     */
    static function deleteNode($node) {
        self::deleteChildren($node);
        $parent = $node->parentNode;
        $oldnode = $parent->removeChild($node);
        return $oldnode;
    }

    /**
     * deleteChildren
     * 
     * Ф-ия удаляет все дочерние узлы из родительского узла $node
     * 
     * @param DOMNode $node // родительский узел, из которого будут удалены все дочерние узлы
     * 
     * @return void
     */
    static function deleteChildren($node) {
        while (isset($node->firstChild)) {
            self::deleteChildren($node->firstChild);
            $node->removeChild($node->firstChild);
        }
    }
    
    //------------ Преобразование документа или узла --------------//
    
    /**
     * saveXHTML
     *
     * Dumps the internal XML tree back into an XHTML-friendly string.
     *
     * @param DOMNode $node
     *         Use this parameter to output only a specific node rather than the entire document.
     * @return string
     */
    public function saveXHTML(DOMNode $node = null) {

        if (!$node)
            $node = $this->firstChild;

        $doc = new DOMDocument('1.0');
        $clone = $doc->importNode($node->cloneNode(false), true);
        $nodeName = $clone->nodeName;
        $term = in_array(strtolower($nodeName), $this->selfTerminate);
        $inner = '';

        if (!$term) {
            $clone->appendChild(new DOMText(''));
            if ($node->childNodes)
                foreach ($node->childNodes as $child) {
                    $inner .= $this->saveXHTML($child);
                }
        }

        $doc->appendChild($clone);
        $out = $doc->saveXML($clone);

        return $term ? substr($out, 0, -2) . ' />' : str_replace('><', ">$inner<", $out);
    }

    /**
     * toArray
     *
     * Создание массива из DOM документа
     *
     * @param DOMNode $node
     *         Use this parameter to output only a specific node rather than the entire document.
     * 
     * @return array
     */
    public function toArray(DOMNode $oDomNode = null) {
        // return empty array if dom is blank
        if (is_null($oDomNode) && !$this->hasChildNodes()) {
            return array();
        }
        $oDomNode = (is_null($oDomNode)) ? $this->documentElement : $oDomNode;
        if (!$oDomNode->hasChildNodes()) {
            $mResult = $oDomNode->nodeValue;
        } else {
            $mResult = array();
            foreach ($oDomNode->childNodes as $oChildNode) {
                // how many of these child nodes do we have?
                // this will give us a clue as to what the result structure should be
                $oChildNodeList = $oDomNode->getElementsByTagName($oChildNode->nodeName);
                $iChildCount = 0;
                // there are x number of childs in this node that have the same tag name
                // however, we are only interested in the # of siblings with the same tag name
                foreach ($oChildNodeList as $oNode) {
                    if ($oNode->parentNode->isSameNode($oChildNode->parentNode)) {
                        $iChildCount++;
                    }
                }
                $mValue = $this->toArray($oChildNode);
                $sKey = ($oChildNode->nodeName{0} == '#') ? 0 : $oChildNode->nodeName;
                $mValue = is_array($mValue) ? $mValue[$oChildNode->nodeName] : $mValue;
                // how many of thse child nodes do we have?
                if ($iChildCount > 1) {  // more than 1 child - make numeric array
                    $mResult[$sKey][] = $mValue;
                } else {
                    $mResult[$sKey] = $mValue;
                }
            }
            // if the child is <foo>bar</foo>, the result will be array(bar)
            // make the result just 'bar'
            if (count($mResult) == 1 && isset($mResult[0]) && !is_array($mResult[0])) {
                $mResult = $mResult[0];
            }
        }
        // get our attributes if we have any
        $arAttributes = array();
        if ($oDomNode->hasAttributes()) {
            foreach ($oDomNode->attributes as $sAttrName => $oAttrNode) {
                // retain namespace prefixes
                $arAttributes["@{$oAttrNode->nodeName}"] = $oAttrNode->nodeValue;
            }
        }
        // check for namespace attribute - Namespaces will not show up in the attributes list
        if ($oDomNode instanceof DOMElement && $oDomNode->getAttribute('xmlns')) {
            $arAttributes["@xmlns"] = $oDomNode->getAttribute('xmlns');
        }
        if (count($arAttributes)) {
            if (!is_array($mResult)) {
                $mResult = (trim($mResult)) ? array($mResult) : array();
            }
            $mResult = array_merge($mResult, $arAttributes);
        }
        $arResult = array($oDomNode->nodeName => $mResult);
        return $arResult;
    }

    //------------ Создание HTML из документа --------------//

    /**
     * createDocXhtml
     *
     * Создать тело и заголовок документа HTML
     *
     */
    function createDocXhtml() {
        $this->head = $this->createElement('head', ' ');
        $this->body = $this->createElement('body', ' ');
    }

    /**
     * addStyleSheet
     *
     * Добавить StyleSheet в массив styles
     *
     * @param string $url
     * @param string $media
     * 
     * @return void
     */
    public function addStyleSheet($url, $media = 'all') {
        $element = $this->createElement('link');
        $element->setAttribute('type', 'text/css');
        $element->setAttribute('href', $url);
        $element->setAttribute('media', $media);
        $this->styles[] = $element;
    }

    /**
     * addScript
     *
     * Добавить скрипты в массив scripts
     *
     * @param string $url
     * 
     * @return void
     */
    public function addScript($url) {
        $element = $this->createElement('script', ' ');
        $element->setAttribute('type', 'text/javascript');
        $element->setAttribute('src', $url);
        $this->scripts[] = $element;
    }

    /**
     * addMetaTag
     *
     * Добавить мета теги в массив metas
     *
     * @param string $name
     * @param string $content
     * 
     * @return void
     */
    public function addMetaTag($name, $content) {
        $element = $this->createElement('meta');
        $element->setAttribute('name', $name);
        $element->setAttribute('content', $content);
        $this->metas[] = $element;
    }

    /**
     * setDescription
     *
     * Добавить мета тег описание
     *
     * @param string $dec
     * 
     * @return void
     */
    public function setDescription($dec) {
        $this->addMetaTag('description', $dec);
    }

    /**
     * setKeywords
     *
     * Добавить мета тег ключевые слова
     *
     * @param string $keywords
     * 
     * @return void
     */
    public function setKeywords($keywords) {
        $this->addMetaTag('keywords', $keywords);
    }

    /**
     * assemble
     *
     * Создать документ XHTML со всеми его атрибутами
     *
     * 
     * @return string
     */
    public function assemble() {
        // Doctype creation
        $doctype = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML TRANSITIONAL 1.0//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';

        // Create the head element
        $title = $this->createElement('title', $this->title);
        // Add stylesheets if needed
        if (is_array($this->styles))
            foreach ($this->styles as $element)
                $this->head->appendChild($element);
        // Add scripts if needed
        if (is_array($this->scripts))
            foreach ($this->scripts as $element)
                $this->head->appendChild($element);
        // Add meta tags if needed
        if (is_array($this->metas))
            foreach ($this->metas as $element)
                $this->head->appendChild($element);
        $this->head->appendChild($title);

        // Create the document
        $html = $this->createElement('html');
        $html->setAttribute('xmlns', 'http://www.w3.org/1999/xhtml');
        $html->setAttribute('xml:lang', 'en');
        $html->setAttribute('lang', 'en');
        $html->appendChild($this->head);
        $html->appendChild($this->body);


        $this->appendChild($html);
        return $doctype . $this->saveXML();
    }
    
    //------------ Отладочные ф-ии --------------//
    
    /**
     * dom_dump
     *
     * Получить информацию о DOM документе
     *
     * @param DOMDocument|DOMElement|DOMAttr|DOMNodeList $obj
     * 
     * @return string
     */
    function dom_dump($obj) {
        if ($classname = get_class($obj)) {
            $retval = "Instance of $classname, node list: \n";
            switch (true) {
                case ($obj instanceof DOMDocument):
                    $retval .= "XPath: {$obj->getNodePath()}\n" . $obj->saveXML($obj);
                    break;
                case ($obj instanceof DOMElement):
                    $retval .= "XPath: {$obj->getNodePath()}\n" . $obj->ownerDocument->saveXML($obj);
                    break;
                case ($obj instanceof DOMAttr):
                    $retval .= "XPath: {$obj->getNodePath()}\n" . $obj->ownerDocument->saveXML($obj);
                    //$retval .= $obj->ownerDocument->saveXML($obj);
                    break;
                case ($obj instanceof DOMNodeList):
                    for ($i = 0; $i < $obj->length; $i++) {
                        $retval .= "Item #$i, XPath: {$obj->item($i)->getNodePath()}\n" .
                                "{$obj->item($i)->ownerDocument->saveXML($obj->item($i))}\n";
                    }
                    break;
                default:
                    return "Instance of unknown class";
            }
        } else {
            return 'no elements...';
        }
        return htmlspecialchars($retval);
    }

}

?>