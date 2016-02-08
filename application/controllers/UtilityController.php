<?php

/**
 * BaseController
 *
 * Контроллер - Utility
 * реализует дополнительные ф-ии
 *
 * @uses       Default_Plugin_BaseController
 * @package    Module-Default
 * @subpackage Controllers
 */
class UtilityController extends Default_Plugin_BaseController {

    /**
     * Действие - image
     * 
     * Отобразить изображение в заданом размере
     * или в натуральном размере
     *
     * Доступ к действию возможем по следующим путям urls:
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
     * Действия с URL
     * - проверим на доступность URL
     *
     *
     * Доступ к действию возможем по следующим путям urls:
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
     * Действия - Test
     * 
     *
     *
     * Доступ к действию возможем по следующим путям urls:
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
