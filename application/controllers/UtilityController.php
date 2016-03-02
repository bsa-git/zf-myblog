<?php

/**
 * BaseController
 *
 * Controller - Utility
 * additional functions
 *
 * @uses       Default_Plugin_BaseController
 * @package    Module-Default
 * @subpackage Controllers
 * @author   Sergii Beskorovainyi <bsa2657@yandex.ru>
 * @license  MIT <http://www.opensource.org/licenses/mit-license.php>
 * @link     https://github.com/bsa-git/zf-myblog/
 */
class UtilityController extends Default_Plugin_BaseController {

    /**
     * Action - image
     * display images in the predetermined size or in the natural size
     *
     * Access to the action is possible in the following paths:
     * - /utility/image
     *
     * @return void
     */
    public function imageAction() {
        $request = $this->getRequest();
        $response = $this->getResponse();

        $username = $request->getQuery('username');
        $id = (int) $request->getQuery('id');
        $w = (int) $request->getQuery('w');
        $h = (int) $request->getQuery('h');
        $hash = $request->getQuery('hash');

        $realHash = Default_Model_DbTable_BlogPostImage::GetImageHash($id, $w, $h);

        // disable autorendering since we're outputting an image
        $this->_helper->viewRenderer->setNoRender();

        $image = new Default_Model_DbTable_BlogPostImage($this->db);
        if ($hash != $realHash || !$image->load($id)) {
            // image not found
            $response->setHttpResponseCode(404);
            return;
        }
        try {
            $fullpath = $image->createThumbnail($w, $h, $username);
        } catch (Exception $ex) {
            $fullpath = $image->getFullPath($username);
        }

        $info = getImageSize($fullpath);

        $response->setHeader('content-type', $info['mime']);
        $response->setHeader('content-length', filesize($fullpath));
        echo file_get_contents($fullpath);
    }

    /**
     * Action - url
     * check on the availability of URL
     *
     *
     * Access to the action is possible in the following paths:
     * - /utility/url
     *
     * @return void
     */
    public function urlAction() {
        // Получим обьект запроса
        $request = $this->getRequest();
        $params = $request->getParams();
        $type_action = $params['type_action'];
        if ($this->_isAjaxRequest) {
            $jsons = array();
            try {

                if ($type_action == 'check_exist') {
                    $url = $params['url'];
                    $client = new Zend_Http_Client();
                    $client->setUri($url);
                    $client->setConfig(array(
                        'maxredirects' => 0,
                        'timeout' => 5));
                    //Zend_Http_Request::
                    $response = $client->request(); //'CONNECT'
                    if ($response->isSuccessful()) {
                        $jsons['result'] = TRUE;
                    } else {
                        $jsons['result'] = FALSE;
                    }
                }

                $this->sendJson($jsons);
            } catch (Exception $exc) {
                $jsons['result'] = FALSE;
                $this->sendJson($jsons);
                return;
            }
        }
    }

    /**
     * Action - test
     *
     * Access to the action is possible in the following paths:
     * - /utility/test
     *
     * @return void
     */
    public function testAction() {

        $ini = Zend_Registry::get('config');
        $parserType = 'phpquery'; // simple, phpquery
        $b = new Default_Plugin_PGBrowser($parserType);
        $filename = $ini['http']['path_cookie_jar'];
        $proxy_user = $ini['proxy']['user'];
        $proxy_pass = $ini['proxy']['pass'];
        $b->setCookie($filename);
        $b->setProxy("proxy.azot.local", 3128, $proxy_user, $proxy_pass);
        $page = $b->get('http://www.google.com/');
        $form = $page->form();
        $form->set('q', 'foo');
        $page = $form->submit();
        $title = $page->title;
        $this->view->html = $title;
    }

}
