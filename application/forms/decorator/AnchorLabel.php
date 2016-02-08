<?php

/**
 * Default_Form_Decorator_AnchorLabel
 * 
 * Декоратор отображения метки в виде ссылки
 *
 *
 * @uses       Zend_Form_Decorator_Abstract
 * @package    Module-Default
 * @subpackage Forms.Decorators
 */
class Default_Form_Decorator_AnchorLabel extends Zend_Form_Decorator_Abstract {

    /**
     * Cоздание метки в виде ссылки
     *
     * @return string
     */
    private function _buildLabel() {
        $href = $this->getOption('href');
        //------------------------
        $element = $this->getElement();
        $label = $element->getLabel();
        $label = '<a href="'. $href .'" id="' . $this->getElement()->getName() . '_label">' . $label . '</a>';
        return $label;
    }

    /**
     * Рендеринг декоратора
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
        $label = $this->_buildLabel();

        switch ($placement) {
            case (self::PREPEND):
                return $label . $separator . $content;
            case (self::APPEND):
            default:
                return $content . $separator . $label;
        }
    }

}