<?php

/**
 * Default_Plugin_Breadcrumbs
 *
 * Класс создания текущего пути к странице
 * (хлебные крошки)
 *
 * @uses       
 * @package    Module-Default
 * @subpackage Plugins
 */
class Default_Plugin_Breadcrumbs {
    
    /**
     * Путь к ресурсу
     * @var array 
     */
    private $_trail = array();

    /**
     * Добавить шаг в путь доступа к ресурсу
     * 
     * @param string $title
     * @param string $link 
     */
    public function addStep($title, $link = '') {
        //$title = Zend_Registry::get('Zend_Translate')->_($title);
        $this->_trail[] = array('title' => $title,
            'link' => $link);
    }

    /**
     * Получить массив данных путей к ресурсу
     * 
     * @return array 
     */
    public function getTrail() {
        return $this->_trail;
    }

    /**
     * Получить наименование ресурса
     * 
     * @return string 
     */
    public function getTitle() {
        if (count($this->_trail) == 0)
            return null;

        return $this->_trail[count($this->_trail) - 1]['title'];
    }

}

?>