<?php
/**
 * Admin_IndexController
 *
 * Controller - Index
 *
 * @uses       Default_Plugin_BaseController
 * @package    Module-Admin (administration of site)
 * @subpackage Controllers
 */
class Admin_IndexController extends Default_Plugin_BaseController {

     /**
     * Initialization controller
     *
     */
    public function init() {
        parent::init();
    }
    
    /**
     * Action - index
     * displays all module operations
     *
     */
    public function indexAction() {
        
    }

    /**
     * Action - out
     * Output from the module
     *
     */
    public function outAction() {
        $this->_redirect('/index');
    }

}

