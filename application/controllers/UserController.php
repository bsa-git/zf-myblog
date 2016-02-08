<?php

/**
 * UserController
 *
 * Контроллер - User
 * предназначен для отображения публичной домашней страницы пользователя
 *
 * @uses       Default_Plugin_BaseController
 * @package    Module-Default
 * @subpackage Controllers
 */
class UserController extends Default_Plugin_BaseController {

    /**
     * Обьект пользователя
     *
     * @var Default_Model_DbTable_User
     */
    protected $user = null;

    /**
     * Событие перед диспетчеризацией
     * 
     * @return void 
     */
    public function preDispatch() {
        // call parent method to perform standard predispatch tasks
        parent::preDispatch();

        // retrieve request object so we can access requested user and action
        $request = $this->getRequest();

        // check if already dispatching the user not found action. if we are
        // then we don't want to execute the remainder of this method
        if (strtolower($request->getActionName()) == 'usernotfound')
            return;

        // retrieve username from request and clean the string
        $username = trim($request->getUserParam('username'));

        // if no username is present, redirect to site home page
        if (strlen($username) == 0) {
            //$this->_redirect($this->getUrl('index', 'index'));
            $this->_redirect('/index/index');
        }

        // load the user, based on username in request. if the user record
        // is not loaded then forward to notFoundAction so a 'user not found'
        // message can be shown to the user.

        $this->user = new Default_Model_DbTable_User($this->db);

        if (!$this->user->loadByUsername($username)) {
            $this->_forward('usernotfound');
            return;
        }

        // Add a link to the breadcrumbs so all actions in this controller
        // link back to the user home page
        $this->_breadcrumbs->addStep(
                $this->Translate('Автор') . '-' . $this->user->username, $this->getCustomUrl(
                        array('username' => $this->user->username,
                    'action' => 'index'), 'user'
                )
        );

        // Make the user data available to all templates in this controller
        $this->view->user = $this->user;
        $this->view->username = $this->user->username;
    }

    /**
     * Действие - index
     *
     * Отображаются все сообщения конкретного пользователя 
     * 
     *
     * 
     * Доступ к действию возможем по следующим путям urls:
     * шаблон раутера - user/:username/:action/*
     * - /user/user1
     * @return void
     */
    public function indexAction() {
        $limit = 0;
        //------------------
        $request = $this->getRequest();
        $params = $request->getParams();
        $itemCountPerPage = $params['itemCountPerPage'];
        $page = $params['page'];

        if (isset($this->user->profile->num_posts)) {
            $limit = max(1, (int) $this->user->profile->num_posts);
        }


        $options = array(
            'user_id' => $this->user->getId(),
            'status' => Default_Model_DbTable_BlogPost::STATUS_LIVE,
            'order' => 'p.ts_created desc',
            'actuals' => array(1)
        );

        // Установим в параметры данные для Paginator
        if ($page) {
            $options['page'] = (int) $page;
        }
        if ($itemCountPerPage) {
            $options['itemCountPerPage'] = (int) $itemCountPerPage;
        } else {
            $options['itemCountPerPage'] = $limit;
        }

        // retrieve the blog posts
        $arrData = Default_Model_DbTable_BlogPost::GetPaginatorPosts($this->db, $options);
        $posts = $arrData['items'];
        $pages = $arrData['pages'];

        //Если записей не найдено то выведем сообщение
        if (count($posts) == 0) {
            $this->view->class_message = 'information';
            $this->view->message = $this->Translate('Ни одной записи в блоге не было найдено для этого пользователя.');
        }

        $this->view->posts = $posts;
        $this->view->pages = $pages;
    }

    /**
     * Пользователь не нашел действия контроллера
     * 
     * Доступ к действию возможем по следующим путям urls:
     * - /user/usernotfound
     *
     * @return void
     */
    public function usernotfoundAction() {
        $username = trim($this->getRequest()->getUserParam('username'));

        $this->view->class_message = 'warning';
        $this->view->message = "Пользователь <em>$username</em> не найден!";

        $this->_breadcrumbs->addStep($this->Translate('Пользователь не найден'));
        $this->view->requestedUsername = $username;
    }

    /**
     * Просмотр страницы
     * 
     * Доступ к действию возможем по следующим путям urls:
     * шаблон роутера - user/:username/view/:url/*
     * - /user/user1/view/prosto-fleshka
     * 
     * @return void
     */
    public function viewAction() {

        // Определим какую закладку нужно открыть
        $viewTab = isset($this->_params["view"]) ? $this->_params["view"] : "post";

        $url = trim($this->_request->getUserParam('url'));

        // if no URL was specified, return to the user home page
        if (strlen($url) == 0) {
            $urlCustom = $this->getCustomUrl(
                    array('username' => $this->user->username,
                'action' => 'index'), 'user'
            );
            $this->_redirector->gotoUrl($urlCustom, array('prependBase' => FALSE));
        }

        // try and load the post
        $post = new Default_Model_DbTable_BlogPost($this->db);
        $post->loadLivePost($this->user->getId(), $url);

        // if the post wasn't loaded redirect to postNotFound
        if (!$post->isSaved()) {
            $this->_forward('postnotfound');
            return;
        }

        // Получим дерево комментариев
        $treeComments = $post->getTreeComments();
        // Получим количество комментариев
        $countComments = $post->getCommentsCount();
        // Инициализируем форму добавления комментария
        $formAddComment = new Default_Form_AddComment($this->user->username, $post->getId());

        // build options for the archive breadcrumbs link
        $archiveOptions = array(
            'username' => $this->user->username,
            'year' => date('Y', $post->ts_created),
            'month' => date('m', $post->ts_created)
        );

        // определим дату 
        $date = new Zend_Date($post->ts_created, 'U');
        $dtFormat = $date->get('YYYY MMMM');

        $this->_breadcrumbs->addStep(
                $dtFormat, $this->getCustomUrl($archiveOptions, 'archive')
        );
        $this->_breadcrumbs->addStep($post->profile->title);

        // make the post available to the template
        $this->view->post = $post;
        $this->view->treeComments = $treeComments;
        $this->view->countComments = $countComments;
        $this->view->formAddComment = $formAddComment;
        $this->view->viewTab = $viewTab;
    }

    /**
     * Запись не найдена
     *
     * Доступ к действию возможем по следующим путям urls:
     * - /user/post-not-found
     *
     * @return void
     */
    public function postnotfoundAction() {

        $url = trim($this->getRequest()->getUserParam('url'));
        $this->view->class_message = 'warning';
        $this->view->message = "Выбранная запись <em>$url</em> не найдена!";

        $this->_breadcrumbs->addStep($this->Translate('Запись не найдена'));
    }

    /**
     * Архив блога пользователя за месяц
     *
     * Доступ к действию возможем по следующим путям urls:
     * шаблон раутера - user/:username/archive/:year/:month/*
     * - /user/user1/archive/2011/07
     *
     * @return void
     */
    public function archiveAction() {
        $request = $this->getRequest();
        $params = $request->getParams();
        $itemCountPerPage = $params['itemCountPerPage'];
        $page = $params['page'];

        // initialize requested date or month
        $m = (int) trim($request->getUserParam('month'));
        $y = (int) trim($request->getUserParam('year'));

        // ensure month is in range 1-12
        $m = max(1, min(12, $m));

        // generate start and finish timestamp for the given month/year
        $from = mktime(0, 0, 0, $m, 1, $y);
        $to = mktime(0, 0, 0, $m + 1, 1, $y) - 1;


        // get live posts based on timestamp with newest posts listed first
        $options = array(
            'user_id' => $this->user->getId(),
            'from' => date('Y-m-d H:i:s', $from),
            'to' => date('Y-m-d H:i:s', $to),
            'status' => Default_Model_DbTable_BlogPost::STATUS_LIVE,
            'order' => 'p.ts_created desc',
            'actuals' => array(1)
        );

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

        // определим дату
        $date = new Zend_Date($from, 'U');
        $dtFormat = $date->get('YYYY MMMM');

        //Если записей не найдено то выведем сообщение
        if (count($posts) == 0) {
            $this->view->class_message = 'information';
            $this->view->message = $this->Translate('Ни одной записи в блоге не было найдено для этого пользователя.');
        }

        $this->_breadcrumbs->addStep($this->Translate('Архив блога'));

        // assign the requested month and the posts found to the template
        $this->view->month = $from;
        $this->view->posts = $posts;
        $this->view->pages = $pages;
    }

    /**
     * Действия с метками
     *
     * Доступ к действию возможем по следующим путям urls:
     * шаблон раутера - user/:username/tag/:tag/*
     * - /user/user1/tag/reports
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
            $urlCustom = $this->getCustomUrl(
                    array('username' => $this->user->username,
                'action' => 'index'), 'user'
            );
            $this->_redirector->gotoUrl($urlCustom, array('prependBase' => FALSE));
        }

        $options = array(
            'user_id' => $this->user->getId(),
            'tag' => $tag,
            'status' => Default_Model_DbTable_BlogPost::STATUS_LIVE,
            'order' => 'p.ts_created desc',
            'actuals' => array(1)
        );

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
            $this->view->message = $this->Translate('Ни одной записи в блоге не было найдено для метки') . " - <em>$tag</em>";
        }

        $tagLabel = Default_Model_DbTable_BlogPost::getLabelForTag($this->db, $tag);

        $this->_breadcrumbs->addStep($this->Translate('Сообщения для метки'));
        $this->view->tag = $tag;
        $this->view->tagLabel = $tagLabel;
        $this->view->posts = $posts;
        $this->view->pages = $pages;
    }

    /**
     * Действия с новостной лентой пользователя
     * можно получить все новости пользователя
     * а можно получить новости только по отдельной метке
     *
     *
     *
     * Доступ к действию возможем по следующим путям urls:
     * шаблон раутера - user/:username/feed/:tag/*
     * или
     * шаблон раутера - user/:username/:action/*
     *
     * - /user/user1/feed/reports
     * или
     * - /user/user1/feed
     *
     * @return void
     */
    public function feedAction() {


        //Получим параметр метки
        $tag = trim($this->_request->getUserParam('tag'));

        // first retrieve all recent posts
        $options = array(
            'user_id' => $this->user->getId(),
            'status' => Default_Model_DbTable_BlogPost::STATUS_LIVE,
            'limit' => 10,
            'order' => 'p.ts_created desc',
            'actuals' => array(1)
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
                    array('username' => $this->user->username,
                'tag' => $tag,
                    ), 'feed_tag'
            );
        } else {
            $url = $this->getCustomUrl(
                    array('username' => $this->user->username,
                'action' => 'index'), 'user'
            );
        }

        $feedData = array(
            //'title' => $this->Translate('Автор блога') . ' - ' . $this->user->username,
            'link' => $domain . $url,
            'charset' => 'UTF-8',
            'entries' => array()
        );

        if ($tag) {
            $tagLabel = Default_Model_DbTable_BlogPost::getLabelForTag($this->db, $tag);
            $title = $this->Translate('Сообщения автора') . ' - ' . $this->user->username . ' ' .
                    $this->Translate('для метки') . ' - ' . $tagLabel;
        } else {
            $title = $this->Translate('Автор блога') . ' - ' . $this->user->username;
        }

        $feedData['title'] = $title;

        // build feed entries based on returned posts
        foreach ($recentPosts as $post) {
            $url = $this->getCustomUrl(
                    array('username' => $this->user->username,
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
     * Действие - получить изображения в HTML виде через ajax запрос
     *
     * Доступ к действию возможем по следующим путям urls:
     * шаблон раутера - user/:username/post/:post_id/images/*
     * 
     * - /user/user1/post/27/images
     *
     * @return void
     */
    public function imagesAction() {
        if ($this->_isAjaxRequest) {
            $jsons = array();
            try {
                $request = $this->getRequest();
                $username = trim($request->getUserParam('username'));
                $post_id = (int) $request->getUserParam('post_id');

                $params = $request->getParams();
                $_from = (int) $params['from'];
                $_to = (int) $params['to'];

                // Получим сообщение
                $posts = Default_Model_DbTable_BlogPost::GetPosts($this->db, array('post_id' => array($post_id)));
                if ($posts) {
                    $images = $posts[$post_id]->images;
                    $images = array_values($images);
                    $indexMax = count($images) - 1;

                    // Создадим обьект шаблона
                    $templater = Default_Plugin_SysBox::createViewSmarty();

                    //Установим параметры шаблона
                    $templater->images = $images;
                    $templater->username = $username;
                    $templater->_from = $_from;
                    $templater->_to = $_to;

                    // Получим результат шаблона
                    $html = $templater->render('user/images.tpl');
                    $jsons['html'] = $html;
                    $more = $indexMax > $_to;
                    $jsons['more'] = $more;
                    if ($_to > $indexMax) {
                        $_to = $indexMax;
                    }
                    $jsons['to'] = $_to;
                    $jsons['from'] = $_from;
                }
                $this->sendJson($jsons);
            } catch (Exception $exc) {
                $jsons = array(
                    'class_message' => 'warning',
                    'messages' => array(
                        '<em>' . $this->Translate('Ошибка получения изображений') . '</em>',
                        Default_Plugin_StrBox::getMessageError($exc)
                    )
                );
                $this->sendJson($jsons);
                return;
            }
        }
    }

    /**
     * Действия с аудио данными
     * получим список файлов mp3 и их описание 
     *
     * Доступ к действию возможем по следующим путям urls:
     * шаблон раутера - user/:username/post/:post_id/audios/*
     * 
     * - /user/user1/post/27/audios
     *
     * @return void
     */
    public function audiosAction() {
        $json = array();
        $caption = '';
        $file = '';
        $comment = '';
        //-----------------------
        // Получим обьект запроса
        $request = $this->getRequest();
        $username = trim($request->getUserParam('username'));
        $post_id = (int) $request->getUserParam('post_id');

        // Получим файлы музыки для статьи
        $audios = Default_Model_DbTable_BlogPostAudio::GetAudio($this->db, array('post_id' => $post_id));
        // Получим путь где находяться файлы музыка автора статьи
        foreach ($audios as $audio) {
            $arrFilename = explode('.', $audio->filename);
            $caption = $arrFilename[0];
            $comment = $audio->comment;
            $file = $audio->getFullUrl_Res($username); //Default_Plugin_SysBox::getFullURL_Res($audio->getFullUrl($username));
            $json[] = array('caption' => $caption, 'file' => $file, 'comment' => $comment);
        }
        if ($this->_isAjaxRequest) {
            $this->sendJson($json);
        }
    }

    /**
     * Действия с видео данными
     * получим список видеофайлов
     *
     * Доступ к действию возможем по следующим путям urls:
     * шаблон раутера - user/:username/post/:post_id/videos/*
     * 
     * - /user/user1/post/27/videos
     *
     * @return void
     */
    public function videosAction() {
        $playlist = array();
        //-----------------------
        // Получим файл конфигурации
        $ini = Zend_Registry::get('config');
        $adapter = $ini['http']['adapter'];
        $proxy_host = $ini['proxy']['host'];

        // Получим обьект запроса
        $request = $this->getRequest();
        $params = $request->getParams();
        $type_action = $params['type_action'];
        $username = trim($request->getUserParam('username'));
        $post_id = (int) $request->getUserParam('post_id');


        if ($type_action == 'playlist') {
            // Получим файлы видео для сообщения
            $videos = Default_Model_DbTable_BlogPostVideo::GetVideo($this->db, array('post_id' => $post_id));
            // Получим список видео данных для статьи
            foreach ($videos as $video) {

                // Получим URL ресурса
                $type = $video->type;
                $arrType = explode('-', $type);
                if ($arrType[0] == 'file') {
                    $url = $video->getFullUrl_Res($username);
                } else {
                    $url = $video->identifier;
                }

                $path = $video->GetUploadPath($username) . '/' . $video->getId() . '.json';
                if (is_file($path)) {
                    $strJson = file_get_contents($path);
                    $strJson = stripslashes($strJson);
                    try {

                        // Получим пути к изобржаениям и флеш для пользователя
                        $pathImages = Default_Model_DbTable_BlogPostImage::GetUploadUrl($username);
                        $pathFlash = Default_Model_DbTable_BlogPostVideo::GetUploadUrlForFlash($username);

                        // Преобразуем Json в PHP массив
                        $itemPlaylist = Zend_Json::decode($strJson);
                        // Изменим данные в массиве
                        $itemPlaylist['clip_id'] = $video->getId();
                        $itemPlaylist['clip_type'] = $video->type;
                        $itemPlaylist['url'] = $url;
                        $itemPlaylist['title'] = $video->name;
                        if (isset($itemPlaylist['cuepoints'])) {
                            $cuepoints = $itemPlaylist['cuepoints'];
                            $newCuepoints = array();
                            foreach ($cuepoints as $cuepoint) {
                                if (isset($cuepoint['image'])) {
                                    $cuepoint['image'] = $this->getUrlRes($pathImages . '/' . ltrim($cuepoint['image'], '/'));
                                }
                                if (isset($cuepoint['flash'])) {
                                    $cuepoint['flash'] = $this->getUrlRes($pathFlash . '/' . ltrim($cuepoint['flash'], '/'));
                                }
                                $newCuepoints[] = $cuepoint;
                            }
                            $itemPlaylist['cuepoints'] = $newCuepoints;
                        }
                    } catch (Exception $exc) {
                        $jsons = array(
                            'class_message' => 'warning',
                            'messages' => array(
                                '<em>' . $this->Translate('Ошибка получения видео') . '</em>',
                                Default_Plugin_StrBox::getMessageError($exc)
                            )
                        );
                        $this->sendJson($jsons);
                        return;
                    }
                } else {
                    $itemPlaylist = array();
                    $itemPlaylist['clip_id'] = $video->getId();
                    $itemPlaylist['clip_type'] = $video->type;
                    $itemPlaylist['url'] = $url;
                    $itemPlaylist['title'] = $video->name;
                    $itemPlaylist['description'] = $video->comment;
                }
                $playlist[] = $itemPlaylist;
            }
            if ($this->_isAjaxRequest) {
                $this->sendJson($playlist);
            }
        } elseif ($type_action == 'godtv_url') {

            // Получим параметры клипа
            $clip_name = $params['clip_name'];
            $clip_id = $params['clip_id'];

            // Получим файлы видео для сообщения
            $videos = Default_Model_DbTable_BlogPostVideo::GetVideo($this->db, array('post_id' => $post_id));
            // Найдем нужное видео и обновим "identifier"
            foreach ($videos as $video) {
                if ($video->getId() == $clip_id) {

                    // Получим уникальный URL для фильма
                    $arrBox = new Default_Plugin_ArrayBox();
                    $url_video = $arrBox->set($video->identifier, '/')->getLast();
                    // Получим новый URL для этого видео
                    $new_url = $this->_getGodtvURL($clip_name, $url_video);
                    if ($new_url === FALSE) {
                        $jsons = array(
                            'class_message' => 'warning',
                            'messages' => array(
                                '<em>' . $this->Translate('Ошибка URL') . '</em>',
                                $this->Translate('Ошибка получения URL для видео')
                            )
                        );
                        $this->sendJson($jsons);
                        return;
                    }
                    $video->identifier = $new_url;
                    if ($video->save()) {
                        $json = array(
                            'url' => $new_url
                        );
                    } else {
                        $json = array(
                            'class_message' => 'warning',
                            'messages' => array(
                                '<em>' . $this->Translate('Ошибка при сохранении данных') . '</em>'
                            )
                        );
                    }
                    $this->sendJson($json);
                    return;
                }
            }
        } elseif ($type_action == 'play') {
            $json = array(
                'result' => 'OK'
            );
            $this->sendJson($json);
            return;
        }
    }

    /**
     * Получить URL видео для сайта Got-tv.ru
     *
     * @param  string $nameVideo
     * @param  string $urlVideo
     * @return string|FALSE //url or ERROR
     */
    private function _getGodtvURL($nameVideo, $urlVideo) {
        $result = FALSE;
        $suffix = '.godtv.ru:85';
        $config = array();
        $strBox = new Default_Plugin_String();
        //-------------------
        // Получим URL страницы загрузки видео
        $encodeNameVideo = urlencode($nameVideo);
        $url = "http://god-tv.ru/" . $encodeNameVideo;

        try {
            $http = new Default_Plugin_HttpBox($config);
            $page = $http->get($url);
            foreach ($page->links() as $link) {
                $href = $link->getAttribute('href');
                $strBox->set($href);
//                if ($strBox->indexOf($suffix) !== FALSE && $strBox->indexOf($urlVideo) !== FALSE) {
//                    $result = $strBox->get();
//                }
                if ($strBox->indexOf($suffix) !== FALSE) {
                    if ($strBox->indexOf($urlVideo) !== FALSE) {
                        $result = $strBox->get();
                    }
                }
            }

            if ($result === FALSE) {
                $html = $page->html;
            }
        } catch (Exception $exc) {
            return FALSE;
        }

        return $result;
    }

    /**
     * Действия с комментариями пользователей
     *
     * Доступ к действию возможем по следующим путям urls:
     * шаблон раутера - user/:username/post/:post_id/comments/*
     * 
     * - /user/user1/post/27/comments
     *
     * @return void
     */
    public function commentsAction() {
        $json = array();
        $result = TRUE;
        //-----------------------
        // Получим обьект запроса
        $request = $this->getRequest();
        $params = $request->getParams();
        $type_action = $params['type_action'];
        $post_id = (int) $request->getUserParam('post_id');
        $username = trim($request->getUserParam('username'));

        try {
            if ($type_action == 'delete') {// Удалим комментарий
                // Получим массив комментариев для удаления
                $comment_ids = $params["comment_ids"];
                $comment_ids = Zend_Json::decode($comment_ids);
                $parent_comment_id = $comment_ids[0];

                // Удалим комментарии из базы данных
                $comment = new Default_Model_DbTable_BlogPostComment($this->db);
                foreach ($comment_ids as $comment_id) {
                    if ($comment->loadForPost($post_id, $comment_id)) {
                        $comment->delete();
                    } else {
                        $result = FALSE;
                        break;
                    }
                }
                if ($result) {
                    $json = array(
                        'deleted' => true,
                        'result' => $this->Translate('Комментарий удален из сообщения блога'),
                        'comment_id' => $parent_comment_id
                    );
                } else {
                    $json = array(
                        'class_message' => 'warning',
                        'messages' => array(
                            '<em>' . $this->Translate('Ошибка при добавлении / удалении комментария в блог') . '</em>'
                        )
                    );
                }
            } else if ($type_action == 'add' || $type_action == 'reply' || $type_action == 'edit') {// Добавим/изменим комментарий на сообщение
                $allParams = $this->_getAllParams();
                $reply_id = $params["reply_id"];

                $formAddComment = new Default_Form_AddComment($username, $post_id);
                $result = $formAddComment->isValid($allParams);
                if ($result) {

                    $comment = new Default_Model_DbTable_BlogPostComment($this->db);
                    if ($type_action == 'edit') {
                        if ($comment->loadForPost($post_id, $reply_id)) {
                            $comment->comment = $formAddComment->getValue('ckeditor_comment');
                        } else {
                            $json = array(
                                'class_message' => 'warning',
                                'messages' => array(
                                    '<em>' . $this->Translate('Ошибка при сохранении данных') . '</em>'
                                )
                            );
                        }
                    } else {
                        $comment->user_id = $this->_identity->user_id;
                        $comment->post_id = $post_id;
                        $comment->reply_id = $reply_id;
                        $comment->comment = $formAddComment->getValue('ckeditor_comment');
                    }

                    if ($comment->save()) {

                        if ($type_action == 'edit') {
                            $html = $formAddComment->getValue('ckeditor_comment');
                            $result = $this->Translate('Комментарий изменен');
                        } else {
                            // Получим параметр для шаблона
                            $treeComments = Default_Model_DbTable_BlogPostComment::getTreeComments(
                                            $this->db, $this->user->getId(), array(
                                        'post_id' => $post_id,
                                        'comment_id' => $comment->getId()
                            ));
                            // Создадим обьект шаблона
                            $templater = Default_Plugin_SysBox::createViewSmarty();

                            //Установим параметры шаблона
                            $templater->treeComments = $treeComments;
                            $templater->authenticated = true;
                            $templater->isAdmin = $this->_isAdmin;
                            $templater->identity = $this->_identity;

                            // Получим результат шаблона
                            $html = $templater->render('user/lib/comment-item.tpl');
                            $result = $this->Translate('Добавлен комментарий к сообщению блога');
                        }

                        $json = array(
                            'added' => true,
                            'result' => $result,
                            'comment_id' => $comment->getId(),
                            'html' => $html
                        );
                    } else {// Ошибка записи в базу данных
                        $json = array(
                            'class_message' => 'warning',
                            'messages' => array(
                                '<em>' . $this->Translate('Ошибка при сохранении данных') . '</em>'
                            )
                        );
                    }
                } else {// неверно заполнены поля формы
                    $json = array(
                        'class_message' => 'warning',
                        'messages' => $this->getFormMessages($formAddComment)
                    );
                }
            }
        } catch (Exception $e) {

            $json = array(
                'class_message' => 'warning',
                'messages' => array(
                    '<em>' . $this->Translate('Ошибка при добавлении / удалении комментария в блог') . '</em>',
                    Default_Plugin_StrBox::getMessageError($e)
                )
            );
        }

        $this->sendJson($json);
    }

}

?>