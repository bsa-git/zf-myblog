<?php

/**
 * IndexController
 *
 * Controller - Index
 *
 * @uses       Default_Plugin_BaseController
 * @package    Module-Default
 * @subpackage Controllers
 * @author   Sergii Beskorovainyi <bsa2657@yandex.ru>
 * @license  MIT <http://www.opensource.org/licenses/mit-license.php>
 * @link     https://github.com/bsa-git/zf-myblog/
 */
class IndexController extends Default_Plugin_BaseController {

    /**
     * Initialization controller
     *
     */
    public function init() {
        parent::init();
    }

    /**
     * 
     * Action - index
     * выводятся сообщения для всех авторов 
     * 
     * Access to the action is possible in the following paths:
     *   /
     * index/
     * index/index
     */
    public function indexAction() {

        $request = $this->getRequest();
        $params = $request->getParams();
        $itemCountPerPage = isset($params['itemCountPerPage']) ? $params['itemCountPerPage'] : 0;
        $page = isset($params['page']) ? $params['page'] : 0;

        // define the options for retrieving blog posts
        $options = array(
            'status' => Default_Model_DbTable_BlogPost::STATUS_LIVE,
            'order' => 'p.ts_created desc',
            'actuals' => array(1)
        );

        // Будем проверять признак публикации сообщений, только у гостей
        if (!$this->_authenticated) {
            $options['public_only'] = TRUE;
        }

        // Установим в параметры данные для Paginator
        if ($page) {
            $options['page'] = (int) $page;
        }
        if ($itemCountPerPage) {
            $options['itemCountPerPage'] = (int) $itemCountPerPage;
        }

        // retrieve the blog posts
        $arrData = Default_Model_DbTable_BlogPost::GetPaginatorPosts($this->db, $options);
        $posts = $arrData['items'];
        $pages = $arrData['pages'];

        // determine which users' posts were retrieved
        $user_ids = array();
        foreach ($posts as $post)
            $user_ids[$post->user_id] = $post->user_id;

        // load the user records
        if (count($user_ids) > 0) {
            $options = array(
                'user_id' => $user_ids
            );

            $users = Default_Model_DbTable_User::GetUsers($this->db, $options);
        } else
            $users = array();

        // assign posts and users to the template
        $this->view->posts = $posts;
        $this->view->users = $users;
        $this->view->pages = $pages;
    }

    /**
     * Action - tag
     * displays messages which are marked this tag for all authors
     * 
     * Access to the action is possible in the following paths:
     * router pattern - user/all/tag/:tag/*
     * - /user/all/tag/reports
     *
     * @return void
     */
    public function tagAction() {
        $request = $this->getRequest();
        $params = $request->getParams();
        $itemCountPerPage = isset($params['itemCountPerPage']) ? $params['itemCountPerPage'] : 0;
        $page = isset($params['page']) ? $params['page'] : 0;

        //Получим параметр метки
        $tag = trim($request->getUserParam('tag'));

        if (strlen($tag) == 0) {
            $this->_redirect('/index/index');
        }

        $options = array(
            'tag' => $tag,
            'status' => Default_Model_DbTable_BlogPost::STATUS_LIVE,
            'order' => 'p.ts_created desc',
        );

        // Будем проверять признак публикации сообщений, только у гостей
        if (!$this->_authenticated) {
            $options['public_only'] = TRUE;
        }

        // Установим в параметры данные для Paginator
        if ($page) {
            $options['page'] = (int) $page;
        }
        if ($itemCountPerPage) {
            $options['itemCountPerPage'] = (int) $itemCountPerPage;
        }

        $arrData = Default_Model_DbTable_BlogPost::GetPaginatorPosts($this->db, $options);
        $posts = $arrData['items'];
        $pages = $arrData['pages'];

        if (count($posts) == 0) {
            $this->view->class_message = 'information';
            $label = Default_Model_DbTable_BlogPost::getLabelForTag($this->db, $tag);
            $this->view->message = $this->Translate('Ни одной записи в блоге не было найдено для метки') . " - <em>$label</em>";
        } else {
            // determine which users' posts were retrieved
            $user_ids = array();
            foreach ($posts as $post)
                $user_ids[$post->user_id] = $post->user_id;

            // load the user records
            if (count($user_ids) > 0) {
                $options = array(
                    'user_id' => $user_ids
                );

                $users = Default_Model_DbTable_User::GetUsers($this->db, $options);
            } else
                $users = array();
        }

        $tagLabel = Default_Model_DbTable_BlogPost::getLabelForTag($this->db, $tag);

        $this->_breadcrumbs->addStep($this->Translate('Сообщения для метки'));
        $this->view->tag = $tag;
        $this->view->tagLabel = $tagLabel;
        $this->view->posts = $posts;
        $this->view->users = $users;
        $this->view->pages = $pages;
    }

    /**
     * Action - feed 
     * actions with news user tape 
     * you can get all the news 
     * and the user can receive news only on a separate tag
     *
     * Access to the action is possible in the following paths:
     * router pattern - user/all/feed/:tag/* or controller/:action/*
     *
     * - /user/all/feed/reports
     * or
     * - /index/feed
     *
     * @return void
     */
    public function feedAction() {


        //Получим параметр метки
        $tag = trim($this->_request->getUserParam('tag'));

        // first retrieve all recent posts
        $options = array(
            'status' => Default_Model_DbTable_BlogPost::STATUS_LIVE,
            'limit' => 10,
            'order' => 'p.ts_created desc',
            'public_only' => true
        );

        if ($tag) {
            $options['tag'] = $tag;
        }

        $recentPosts = Default_Model_DbTable_BlogPost::GetPosts($this->db, $options);

        // base URL for generated links
        if ($this->getRequest()->getServer('HTTPS') == 'on') {
            $domain = 'https://';
        } else {
            $domain = 'http://';
        }
        $domain .= $this->getRequest()->getServer('HTTP_HOST');

        // url for web feed
        if ($tag) {
            $url = $this->getCustomUrl(
                    array('tag' => $tag,
                    ), 'feed_tag_all'
            );
        } else {
            $url = $this->getUrl('feed');
        }

        $feedData = array(
            'link' => $domain . $url,
            'charset' => 'UTF-8',
            'entries' => array()
        );

        if ($tag) {
            $tagLabel = Default_Model_DbTable_BlogPost::getLabelForTag($this->db, $tag);
            $title = $this->Translate('Сообщения авторов') . ' ' .
                    $this->Translate('для метки') . ' - ' . $tagLabel;
        } else {
            $title = $this->Translate('Сообщения всех авторов');
        }

        $feedData['title'] = $title;

        // determine which users' posts were retrieved
        $user_ids = array();
        foreach ($recentPosts as $post)
            $user_ids[$post->user_id] = $post->user_id;

        // load the user records
        if (count($user_ids) > 0) {
            $options = array(
                'user_id' => $user_ids
            );

            $users = Default_Model_DbTable_User::GetUsers($this->db, $options);
        } else
            $users = array();


        // build feed entries based on returned posts
        foreach ($recentPosts as $post) {
            $user = $users[$post->user_id];
            $url = $this->getCustomUrl(
                    array('username' => $user->username,
                'url' => $post->url), 'post'
            );

            $entry = array(
                'title' => $post->profile->title,
                'link' => $domain . $url,
                'description' => $post->getTeaser(200),
                'lastUpdate' => $post->ts_created,
                'category' => array()
            );

            // attach tags to each entry
            foreach ($post->getTags() as $tag) {
                $entry['category'][] = array('term' => $tag);
            }

            $feedData['entries'][] = $entry;
        }

        // create feed based on created data
        $feed = Zend_Feed::importArray($feedData, 'atom');

        // disable auto-rendering since we're outputting an image
        $this->_helper->viewRenderer->setNoRender();

        // output the feed to the browser
        $feed->send();
    }

    /**
     * Action - modules
     * show site modules
     *
     * Access to the action is possible in the following paths:
     * - /index/modules
     *
     * @return void
     */
    public function modulesAction() {
        $this->_breadcrumbs->addStep($this->Translate('Модули'));
    }

    /**
     * 
     * Action - readme
     * short product description
     *
     * Access to the action is possible in the following paths:
     * - /index/readme
     *
     * @return void
     */
    public function readmeAction() {
        $this->_breadcrumbs->addStep($this->Translate('Обзор продукта'));

        $filename = APPLICATION_BASE . "\README.md";
        $this->view->markdown = $this->getMarkdown(array('filename' => $filename));
    }

    /**
     * Action - license
     * product license
     *
     * Access to the action is possible in the following paths:
     * - /index/license
     *
     * @return void
     */
    public function licenseAction() {
        $this->_breadcrumbs->addStep($this->Translate('Лицензия'));

        $filename = APPLICATION_BASE . "\LICENSE.md";
        $this->view->markdown = $this->getMarkdown(array('filename' => $filename));
    }

}
