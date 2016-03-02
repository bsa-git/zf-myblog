<?php
/**
 * Hr_IndexController
 *
 * Controller - Index 
 * 
 *
 * @uses       Default_Plugin_BaseController
 * @package    Module-HR (Personnel Management Module)
 * @subpackage Controllers
 * @author   Sergii Beskorovainyi <bsa2657@yandex.ru>
 * @license  MIT <http://www.opensource.org/licenses/mit-license.php>
 * @link     https://github.com/bsa-git/zf-myblog/
 */
class Hr_IndexController extends Default_Plugin_BaseController {

     /**
     * Initialization controller
     *
     */
    public function init() {
        parent::init();
    }
    
    /**
     * Action - index
     *
     */
    public function indexAction() {
        
    }

    /**
     * Action - out
     *
     */
    public function outAction() {
        $this->_redirect('/index');
    }

}

