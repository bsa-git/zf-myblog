<?php

/**
 * Default_Form_Decorator_Calendar
 * 
 * Декоратор отображения javascript календаря
 *
 *
 * @uses       Zend_Form_Decorator_Abstract
 * @package    Module-Default
 * @subpackage Forms.Decorators
 */
class Default_Form_Decorator_Calendar extends Zend_Form_Decorator_Abstract {

    /**
     * Получение строк подключения Javacript и CSS для календаря
     * Статическая переменная $jsAndCss отвечает за то, чтобу подключение
     * осуществлялось только один раз
     *
     * @return string
     */
    private function _getJsAndCss() {
        static $jsAndCss = null;
        $locale = Default_Plugin_SysBox::getTranslateLocale();
        $locale = ($locale == 'uk') ? 'ru' : $locale;
        $request = new Zend_Controller_Request_Http();
        $basePath = $request->getBasePath();
        if ($jsAndCss === null) {

            $jsAndCss = '
            <style type="text/css">@import url(' . Default_Plugin_SysBox::getUrlRes('/js/calendar/skins/aqua/theme.css') . ');</style>' .
                    '<script type="text/javascript" src="' . Default_Plugin_SysBox::getUrlRes('/js/calendar/calendar.js') . '"></script>' .
                    '<script type="text/javascript" src="' . Default_Plugin_SysBox::getUrlRes("/js/calendar/lang/calendar-{$locale}.js") . '"></script>' .
                    '<script type="text/javascript" src="' . Default_Plugin_SysBox::getUrlRes('/js/calendar/calendar-setup.js') . '"></script>';

            return $jsAndCss;
        }
        return '';
    }

    /**
     * Получение кода ссылки и изображения каледаря. Настройка календаря
     *
     * @return string
     */
    private function _getCalendarLink() {
        $request = new Zend_Controller_Request_Http();
        $basePath = $request->getBasePath();

        $calendarLink = '
            <a href="#" id="' . $this->getElement()->getName() . '_calendar">' .
                '<img class="calendar-image" src = "' . Default_Plugin_SysBox::getUrlRes('/js/calendar/calendar.gif') . '">
            </a>
    
            <script type="text/javascript">
                Calendar.setup(
                  {
                    inputField  : "' . $this->getElement()->getName() . '",
                    ifFormat    : "%d.%m.%Y",
                    button      : "' . $this->getElement()->getName() . '_calendar",
                    firstDay    : 1
                  }
                );
            </script>
        ';

        return $calendarLink;
    }

    /**
     * Рендеринг декоратора
     *
     * @param string $content
     * @return string
     */
    public function render($content) {
        // Получаем объект элемента к которому применяется декоратор
        $element = $this->getElement();
        if (!$element instanceof Zend_Form_Element) {
            return $content;
        }
        // Проверяем объект вида зарегистрированного для формы
        if (null === $element->getView()) {
            return $content;
        }

        // Расположение декоратора, "после" или "перед" элементом, по умолчанию "после"
        $placement = $this->getPlacement();
        // Разделитель между элементом и декоратором
        $separator = $this->getSeparator();

        // Взависимости от настроек расположения декоратора возвращаем содержимое
        switch ($placement) {
            // После элемента
            case 'APPEND':
                return $content . $separator . $this->_getJsAndCss() . $this->_getCalendarLink();
            // Перед элементом
            case 'PREPEND':
                return $this->_getJsAndCss() . $this->_getCalendarLink() . $separator . $content;
            case null:
            // По умолчанию просто возвращаем содержимое календаря
            default:
                return $this->_getJsAndCss() . $this->_getCalendarLink();
        }
    }

}
