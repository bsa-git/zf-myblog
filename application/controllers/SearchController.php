<?php

/**
 * SearchController
 *
 * Controller - Search
 * manages the search for information on the site
 *
 * @uses       Default_Plugin_BaseController
 * @package    Module-Default
 * @subpackage Controllers
 * @author   Sergii Beskorovainyi <bsa2657@yandex.ru>
 * @license  MIT <http://www.opensource.org/licenses/mit-license.php>
 * @link     https://github.com/bsa-git/zf-myblog/
 */
class SearchController extends Default_Plugin_BaseController {

    /**
     * Action - index
     * search posts for request
     * 
     * Access to the action is possible in the following paths:
     * - /search/index
     * - /search/
     * @return void
     */
    public function indexAction() {
        $searchZendAuth = $this->_sessZendAuth->search;
        //------------------
        $request = $this->getRequest();
        $params = $request->getParams();
        $itemCountPerPage = isset($params['itemCountPerPage']) ? $params['itemCountPerPage'] : 0;
        $page = isset($params['page']) ? $params['page'] : 0;

        // Получим запрос для поиска
        $query = trim($params['q']);

        // Получим  url MVC
        $urlMVC = $this->_url_mvc;

        // Скорректируем  url MVC
        $addQuery = '/q/' . $query;
        $arrMVC = explode('/q/', $urlMVC);
        $urlMVC = $arrMVC[0] . $addQuery;

        // Подготовка запроса для поиска
        $q = Zend_Search_Lucene_Search_QueryParser::parse($query, 'utf-8');

        // Параметры поиска
        $search = array(
            'performed' => false,
            'limit' => $this->_config['paginator']['itemCountPerPage'],
            'total' => 0,
            'start' => 0,
            'finish' => 0,
            'page' => 1,
            'pages' => 1,
            'results' => array()
        );

        // Установим параметры для Paginator
        if ($itemCountPerPage) {
            $search['limit'] = (int) $itemCountPerPage;
        }

        if ($page) {
            $search['page'] = (int) $page;
        }

        // Поиск по запросу
        try {
            if (strlen($q) == 0)
                throw new Exception('No search term specified');

            // Преобразуем строку запроса через translit();
            $search_query = new Default_Plugin_String($query);
            $search_query = (string) $search_query->translit()->Strip('-');

            // Проверим если результаты запроса в сессии пользователя
            // если есть, то берем их из сессии и отсылаем пользователю
            if (isset($searchZendAuth[$search_query])) {
                $post_ids = $searchZendAuth[$search_query];
                $post_ids = explode(';', $post_ids);
            } else {
                $path = Default_Model_DbTable_BlogPost::getIndexFullpath();
                $index = Zend_Search_Lucene::open($path);
                $hits = $index->find($q);
                $post_ids = array();
                foreach ($hits as $hit) {
                    $post_ids[] = (int) $hit->post_id;
                }
                // ВАЖНО!!! привильно запоминать
                $this->_sessZendAuth->search[$search_query] = implode(";", $post_ids);
                // НЕ ПРАВИЛЬНО!!!
            }   // $searchZendAuth[$search_query] = implode(";", $post_ids);
            //------ Создадим обьект Zend_Paginator

            $paginator = Zend_Paginator::factory($post_ids);

            // Установим максимальное количество отображаемых на странице элементов
            $paginator->setItemCountPerPage($search['limit']);

            // Установим текущую страницу
            $paginator->setCurrentPageNumber($search['page']);

            // Получим массив "ids" для заданной страницы
            $post_ids = array();
            foreach ($paginator as $post_id) {
                $post_ids[] = $post_id;
            }

            // Получим обьект управления страницами
            $pages = $paginator->getPages();

            $search['performed'] = true;
            $search['total'] = $pages->totalItemCount;
            $search['pages'] = $pages->pageCount;
            $search['start'] = $pages->firstItemNumber;
            $search['finish'] = $pages->lastItemNumber;

            // Получим найденные сообщения для текущей страницы
            $options = array(
                'status' => Default_Model_DbTable_BlogPost::STATUS_LIVE,
                'post_id' => $post_ids);

            $posts = Default_Model_DbTable_BlogPost::GetPosts($this->db, $options);

            foreach ($post_ids as $post_id) {
                if (array_key_exists($post_id, $posts)) {
                    $search['results'][$post_id] = $posts[$post_id];
                }
            }

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
        } catch (Exception $ex) {
            // no search performed or an error occurred
        }

        if ($search['performed'])
            $this->_breadcrumbs->addStep($this->Translate('Поиск'));
        else
            $this->_breadcrumbs->addStep($this->Translate('Поиск'));

        $this->view->q = $query;
        $this->view->search = $search;
        $this->view->users = $users;
        $this->view->pages = $pages;
        $this->view->url_mvc = $urlMVC;
    }

    /**
     * Action - suggestion
     * get a list of suggested tags to a request entered by the user
     *
     * Access to the action is possible in the following paths:
     * - /search/suggestion
     * @return void
     */
    public function suggestionAction() {
        $q = trim($this->getRequest()->getPost('q'));

        $suggestions = Default_Model_DbTable_BlogPost::GetTagSuggestions($this->db, $q, 10);
        $this->sendJson($suggestions);
    }

    /**
     * Action - rebuild
     * rebuild the search index
     *
     * Access to the action is possible in the following paths:
     * - /search/rebuild
     * @return void
     */
    public function rebuildAction() {
        $result = TRUE;
        $err_msg = '';
        $json = array();
        //-------------------
        try {

            // Задержка условная
            sleep(3);

            $indexFullpath = Default_Model_DbTable_BlogPost::getIndexFullpath();
            if (is_dir($indexFullpath)) {
                // Получим обьект построения дерева файлов
                $ft = new Default_Plugin_FileTree($indexFullpath);
                // создадим дерево файлов
                $ft->readTree();
                // удалим файлы и директории
                $result = $ft->delFiles();
                if ($result) {
                    // удалим пустую директорию
                    $result = rmdir($indexFullpath);
                }
            }
            $index = Zend_Search_Lucene::create($indexFullpath);
            $options = array('status' => Default_Model_DbTable_BlogPost::STATUS_LIVE);
            $posts = Default_Model_DbTable_BlogPost::GetPosts(Zend_Registry::get('db'), $options);

            foreach ($posts as $post) {
                $index->addDocument($post->getIndexableDocument());
            }
            $index->commit();
            $message = array(
                '<em>' . $this->Translate("Восстановление поискового индекса") . '!</em>',
                $this->Translate("Восстановление поискового индекса завершилось успешно") . '.'
            );
            if ($this->_isAjaxRequest) {
                $json['class_message'] = 'information';
                $json['messages'] = $message;
                $json['$result'] = $result;
            }
        } catch (Exception $ex) {

            $err_msg = $ex->getMessage();
            $message = array(
                '<em>' . $this->Translate("Ошибка восстановления поискового индекса") . '!</em>', $err_msg
            );

            if ($this->_isAjaxRequest) {
                $json['class_message'] = 'warning';
                $json['messages'] = $message;
                $json['$result'] = $result;
            } else {
                $logger = Zend_Registry::get('Zend_Log');
                $logger->warn('Error rebuilding search index: ' .
                        $ex->getMessage());
                $result = FALSE;
            }
        }
        if ($this->_isAjaxRequest) {
            $this->sendJson($json);
        } else {
            $this->view->result = $result;
            if ($result) {
                $this->view->class_message = 'information';
            } else {
                $this->view->err_msg = $err_msg;
                $this->view->class_message = 'warning';
            }
            $this->view->message = $message;
            $this->_breadcrumbs->addStep($this->Translate('Восстановление поискового индекса'));
        }
    }

    /**
     * Action - optimize
     * optimize the search index
     *
     * Access to the action is possible in the following paths:
     * - /search/optimize
     * @return void
     */
    public function optimizeAction() {
        $result = TRUE;
        $err_msg = '';
        $json = array();
        //-------------------
        try {

            // Задержка условная
            sleep(3);

            $indexFullpath = Default_Model_DbTable_BlogPost::getIndexFullpath();

            // Откроем поисковый индекс
            $index = Zend_Search_Lucene::open($indexFullpath);
            // Оптимизация индекса
            $index->optimize();
            $message = array(
                '<em>' . $this->Translate("Оптимизация поискового индекса") . '!</em>',
                $this->Translate("Оптимизация поискового индекса завершилась успешно") . '.'
            );
            if ($this->_isAjaxRequest) {
                $json['class_message'] = 'information';
                $json['messages'] = $message;
                $json['$result'] = $result;
            }
        } catch (Exception $ex) {

            $err_msg = $ex->getMessage();
            $message = array(
                '<em>' . $this->Translate("Ошибка оптимизации поискового индекса") . '!</em>', $err_msg
            );

            if ($this->_isAjaxRequest) {
                $json['class_message'] = 'warning';
                $json['messages'] = $message;
                $json['$result'] = $result;
            } else {
                $logger = Zend_Registry::get('Zend_Log');
                $logger->warn('Error optimizing search index: ' .
                        $ex->getMessage());
                $result = FALSE;
            }
        }
        if ($this->_isAjaxRequest) {
            $this->sendJson($json);
        } else {
            $this->view->result = $result;
            if ($result) {
                $this->view->class_message = 'information';
            } else {
                $this->view->err_msg = $err_msg;
                $this->view->class_message = 'warning';
            }
            $this->view->message = $message;
            $this->_breadcrumbs->addStep($this->Translate('Оптимизация поискового индекса'));
        }
    }

}

?>