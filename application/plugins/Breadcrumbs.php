<?php

/**
 * Default_Plugin_Breadcrumbs
 *
 * Plugin - the creation of the current path to the page (breadcrumbs)
 *
 * @uses       
 * @package    Module-Default
 * @subpackage Plugins
 * @author   Sergii Beskorovainyi <bsa2657@yandex.ru>
 * @license  MIT <http://www.opensource.org/licenses/mit-license.php>
 * @link     https://github.com/bsa-git/zf-myblog/
 */
class Default_Plugin_Breadcrumbs {
    
    /**
     * The path to the resource
     * @var array 
     */
    private $_trail = array();

    /**
     * Add a step in the path to a resource
     * 
     * @param string $title
     * @param string $link 
     */
    public function addStep($title, $link = '') {
        $this->_trail[] = array('title' => $title,
            'link' => $link);
    }

    /**
     * Get path to the resource
     * 
     * @return array 
     */
    public function getTrail() {
        return $this->_trail;
    }

    /**
     * Get resource title
     * 
     * @return string 
     */
    public function getTitle() {
        if (count($this->_trail) == 0)
            return null;

        return $this->_trail[count($this->_trail) - 1]['title'];
    }

}