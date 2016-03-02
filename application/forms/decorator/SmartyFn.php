<?php

/**
 * Default_Form_Decorator_SmartyFn
 * 
 * Decorator - Smarty function outputs the result
 *
 *
 * @uses       Zend_Form_Decorator_Abstract
 * @package    Module-Default
 * @subpackage Forms.Decorators
 * @author   Sergii Beskorovainyi <bsa2657@yandex.ru>
 * @license  MIT <http://www.opensource.org/licenses/mit-license.php>
 * @link     https://github.com/bsa-git/zf-myblog/
 */
class Default_Form_Decorator_SmartyFn extends Zend_Form_Decorator_Abstract {

    /**
     * Create Smarty function
     *
     * @return string
     */
    private function _buildSmartyFn() {
        $options = $this->getOptions();
        //------------------------
        $element = $this->getElement();
        $name = $element->getName();
        $label = $element->getLabel();
        if($label){
            $label = '<dt id="' . $name . '-label"><label class="required">' . $label . ':</label></dt>';
        }
        
        switch ($name) {
            case ('html_select_date'):
                $prefix = $options['prefix'];
                $time = $options['time'];
                $start_year = $options['start_year'];
                $end_year = $options['end_year'];
                
                $fnSmarty = "{html_select_date prefix='$prefix'
                          time=$time
                          start_year=$start_year
                          end_year=$end_year}";
                $fnSmarty = '<dd id="' . $name . '-element">' . $fnSmarty . '</dd>';
            case ('html_select_time'):
                $prefix = $options['prefix'];
                $time = $options['time'];
                $display_seconds = $options['display_seconds'];
                $use_24_hours = $options['use_24_hours'];
                
                $fnSmarty = "{html_select_time prefix='$prefix'
                          time=$time
                          display_seconds=$display_seconds
                          use_24_hours=$use_24_hours}";
                $fnSmarty = '<dd id="' . $name . '-element">' . $fnSmarty . '</dd>';
            default:
                $fnSmarty = "";
        }
        return $fnSmarty;
    }

    /**
     * Rendering decorator
     *
     * @param string $content
     * @return string
     */
    public function render($content) {
        $element = $this->getElement();
        if (!$element instanceof Zend_Form_Element) {
            return $content;
        }
        if (null === $element->getView()) {
            return $content;
        }

        $separator = $this->getSeparator();
        $placement = $this->getPlacement();
        $fnSmarty = $this->_buildSmartyFn();

        switch ($placement) {
            case (self::PREPEND):
                return $fnSmarty . $separator . $content;
            case (self::APPEND):
            default:
                return $content . $separator . $fnSmarty;
        }
    }

}