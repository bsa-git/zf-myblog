<?php

/**
 * BlogmanagerController
 *
 * Controller - Blogmanager
 * manages user blog posts
 *
 * @uses       Default_Plugin_BaseController
 * @package    Module-Default
 * @subpackage Controllers
 * @author   Sergii Beskorovainyi <bsa2657@yandex.ru>
 * @license  MIT <http://www.opensource.org/licenses/mit-license.php>
 * @link     https://github.com/bsa-git/zf-myblog/
 */
class BlogmanagerController extends Default_Plugin_BaseController {

    /**
     * Initialization controller
     *
     */
    public function init() {
        parent::init();
        $this->_breadcrumbs->addStep($this->Translate('Ваш профиль'), $this->getUrl(null, 'account'));
        $this->_breadcrumbs->addStep($this->Translate('Управление блогом'), $this->getUrl(null, 'blogmanager'));
    }

    /**
     * Action - index
     * 
     * A list of all posts of the author by month and labels and posts in the current month
     * 
     * Access to the action is possible in the following paths:
     * - /blogmanager/index
     * - /blogmanager/
     * @return void
     */
    public function indexAction() {

        $request = $this->getRequest();
        $params = $request->getParams();
        $itemCountPerPage = isset($params['itemCountPerPage']) ? $params['itemCountPerPage'] : 0;
        $page = isset($params['page']) ? $params['page'] : 0;

        // Получим  url MVC
        $urlMVC = $this->_url_mvc;

        // initialize the tag
        $tag = isset($params['tag']) ? $params['tag'] : '';
        if ($tag) {
            $options = array(
                'user_id' => $this->_identity->user_id,
                'tag' => $tag,
                'order' => 'p.ts_created desc'
            );

            // Скорректируем  url MVC
            $addTag = '/index/tag/' . $tag;
            $urlMVC = str_replace($addTag, '', $urlMVC);
            $urlMVC .= $addTag;

            $tagLabel = Default_Model_DbTable_BlogPost::getLabelForTag($this->db, $tag);
            $this->view->tag = $tag;
            $this->view->tagLabel = $tagLabel;
        } else {

            // initialize the month
            $month = isset($params['month']) ? $params['month'] : '';
            if ($month && preg_match('/^(\d{4})-(\d{2})$/', $month, $matches)) {
                $y = $matches[1];
                $m = max(1, min(12, $matches[2]));

                // Скорректируем  url MVC
                $addMonth = '/index/month/' . $month;
                $urlMVC = str_replace($addMonth, '', $urlMVC);
                $urlMVC .= $addMonth;
            } else {
                $y = date('Y'); // current year
                $m = date('n'); // current month
            }

            $from = mktime(0, 0, 0, $m, 1, $y);
            $to = mktime(0, 0, 0, $m + 1, 1, $y) - 1;

            $options = array(
                'user_id' => $this->_identity->user_id,
                'from' => date('Y-m-d H:i:s', $from),
                'to' => date('Y-m-d H:i:s', $to),
                'order' => 'p.ts_created desc'
            );

            $this->view->month = $from;
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
        $recentPosts = isset($arrData['items']) ? $arrData['items'] : array();
        $pages = isset($arrData['pages']) ? $arrData['pages'] : 0;

        // get the total number of posts for this user
        $totalPosts = Default_Model_DbTable_BlogPost::GetPostsCount(
                        $this->db, array('user_id' => $this->_identity->user_id)
        );


        $this->view->recentPosts = $recentPosts;
        $this->view->totalPosts = $totalPosts;
        $this->view->pages = $pages;
        $this->view->url_mvc = $urlMVC;
    }

    /**
     * Action - edit
     * edit post
     * 
     * 
     * Access to the action is possible in the following paths:
     * - /blogmanager/edit
     *
     * @return void
     */
    public function editAction() {
        $request = $this->getRequest();
        $post_id = (int) $request->getQuery('id');
        $username = $this->_identity->username;

        $formBlogPost = new Default_Form_BlogPost($this->db, $this->_identity->user_id, $post_id);



        // Проверим тип запроса, если POST значит пришли данные формы
        if ($this->_request->isPost()) {
            
            // Если выходим из редактора сообщения
            if($this->_request->getPost('close')){
                if ($formBlogPost->post->isSaved()) {
                    //Перейдем на предварительный просмотр сообщения
                    $url = '/blogmanager/preview' . '?id=' . $formBlogPost->post->getId();
                    $this->_redirect($url);
                }  else {
                   //Перейдем на управление блогом
                    $url = '/blogmanager';
                    $this->_redirect($url); 
                }
            }
            
            // Проверяем на валидность поля формы
            $result = $formBlogPost->isValid($this->_getAllParams());
            if ($result) {
                
                // Get locale
                $locale = Default_Plugin_SysBox::getTranslateLocale();
                // Get timestamp
                $post_created = $formBlogPost->getValue('ts_created');
                $zDate = new Zend_Date($post_created, null, $locale);
                $timestamp = $zDate->get(Zend_date::TIMESTAMP);
                // Set post 'ts_created' to timestamp
                $formBlogPost->post->ts_created = $timestamp;
                //Получим заголовок сообщения
                $formBlogPost->post->profile->title = $formBlogPost->getValue('title');
                //Получим краткое описание сообщения
                $formBlogPost->post->profile->description = $formBlogPost->getValue('ckeditor_description');
                //Получим тело сообщение
                $content = $formBlogPost->getValue('ckeditor_content');
                $formBlogPost->post->profile->content = $content;

                // Если нет предварительного просмотра, то 
                // установи признак публикации статьи в блоге
                $title = $formBlogPost->getValue('title');
                if ($formBlogPost->post->isSaved()) {
                    if ($formBlogPost->post->isLive()) {
                        $message = 'Сообщение отредактировано и опубликовано в блог.';
                        $message_log = "User-\"$username\" message-\"$title\" has been edited and published it in the blog";
                    }  else {
                        $message = 'Сообщение отредактировано но не опубликовано в блог.';
                        $message_log = "User-\"$username\" message-\"$title\" has been edited but not posted to the blog";
                    }
                } else {
                    if ($formBlogPost->post->isLive()) {
                        $message = 'Создано новое сообщение и опубликовано в блог.';
                        $message_log = "User-\"$username\" creates a new message-\"$title\" and posted to the blog";
                    }  else {
                        $message = 'Создано новое сообщение но не опубликовано в блог.';
                        $message_log = "User-\"$username\" creates a new message-\"$title\" but not published in the blog";
                    }
                }

                //Сохраним данные post
                if ($formBlogPost->post->save()) {

                    // Запомним в логе сообщений
                    $this->_logMsg->post_edit($message_log);

                    //Добавим сообщение о создании сообщения
                    $this->_flashMessenger->addMessage($this->Translate($message));

                    //Перейдем на предварительный просмотр сообщения
                    $url = '/blogmanager/preview' . '?id=' . $formBlogPost->post->getId();
                    $this->_redirect($url);
                } else { // Ошибка в сообщении пользователя! Сообщение пользователя не соответствует стандарту XHTML.
                    $this->view->class_message = 'warning';
                    $this->view->message = $this->Translate('Ошибка в сообщении пользователя! Сообщение пользователя не соответствует стандарту XHTML.');
                }
            } else {// неверно заполнены поля формы
                $this->view->class_message = 'warning';
                $this->view->message = $this->getFormMessages($formBlogPost);
            }
        }

        //Добавим путь к действию
        if ($formBlogPost->post->isSaved()) {
            $this->_breadcrumbs->addStep(
                    $this->Translate('Просмотр сообщения') . ': ' . $formBlogPost->post->profile->title, $this->getUrl('preview') . '?id=' . $formBlogPost->post->getId()
            );
            $this->_breadcrumbs->addStep($this->Translate('Редактировать сообщение в блоге'));
        } else {
            $this->_breadcrumbs->addStep($this->Translate('Создать новое сообщение в блоге'));
        }


        $this->view->formBlogPost = $formBlogPost;
    }

    /**
     * Action - preview
     * viewing messages in edit mode
     * 
     * Access to the action is possible in the following paths:
     * - /blogmanager/preview
     *
     * @return void
     */
    public function previewAction() {

        // Получим обьект сообщения
        $request = $this->getRequest();
        $post_id = (int) $request->getQuery('id');
        $post = new Default_Model_DbTable_BlogPost($this->db);

        if (!$post->loadForUser($this->_identity->user_id, $post_id)) {
            $this->_redirect('/blogmanager');
        }
        // Отображение ошибок
        if ($request->getQuery('message')) {
            $this->view->class_message = $request->getQuery('class_message');
            $this->view->message = $request->getQuery('message');
        }

        // Set PHP config
        Default_Plugin_FileUploader::iniSetConfig_PHP(array('image', 'audio', 'video'));

        $this->_breadcrumbs->addStep($this->Translate('Просмотр сообщения') . ': ' . $post->profile->title);

        $this->view->username = $this->_identity->username;
        $this->view->post = $post;
    }

    /**
     * Action - setstatus
     * set post ststus
     * 
     * Access to the action is possible in the following paths:
     * - /blogmanager/setstatus
     *
     * @return void
     */
    public function setstatusAction() {
        $username = $this->_identity->username;
        //--------------------------
        // Получим обьект сообщения
        $request = $this->getRequest();
        $post_id = (int) $request->getPost('id');
        $post = new Default_Model_DbTable_BlogPost($this->db);

        if (!$post->loadForUser($this->_identity->user_id, $post_id)) {
            $this->_redirect('/blogmanager');
        }

        $title = $post->profile->title;

        // URL to redirect back to
        $url = '/blogmanager/preview' . '?id=' . $post->getId();

        if ($request->getPost('edit')) {
            $this->_redirect('/blogmanager/edit' . '?id=' . $post->getId());
        } else if ($request->getPost('publish')) {
            $post->sendLive();
            $post->save();

            $this->_flashMessenger->addMessage($this->Translate('Сообщение успешно опубликовано в блоге'));
            $message_log = "User-\"$username\" has successfully published a blog post-\"$title\"";
        } else if ($request->getPost('unpublish')) {
            $post->sendBackToDraft();
            $post->save();

            $this->_flashMessenger->addMessage($this->Translate('Сообщение переведено в состояние не опубликовано'));
            $message_log = "User-\"$username\" set message-\"$title\" to the state is not published";
        } else if ($request->getPost('delete')) {
            $post->delete();

            // Preview page no longer exists for this page so go back to index
            $url = '/blogmanager';

            $this->_flashMessenger->addMessage($this->Translate('Сообщение удалено из блога'));
            $message_log = "User-\"$username\" deletes a message-\"$title\" from the blog";
        }

        // Запомним в логе сообщений
        $this->_logMsg->post_set_status($message_log);

        $this->_redirect($url);
    }

    /**
     * Action - tags
     * add/delete tags
     * 
     * Access to the action is possible in the following paths:
     * - /blogmanager/tags
     *
     * @return void
     */
    public function tagsAction() {
        $json = array();
        //-----------------------
        // Получим обьект записи
        $request = $this->getRequest();
        $post_id = (int) $request->getPost('id');
        $post = new Default_Model_DbTable_BlogPost($this->db);

        // Если конкретной записи нет, то перейдем к странице по умолчанию
        if (!$post->loadForUser($this->_identity->user_id, $post_id))
            $this->_redirect('/blogmanager');

        $tag = $request->getPost('tag');

        try {
            if ($request->getPost('add')) {
                $addedTags = $post->addTags($tag);
                if (count($addedTags) == 0) {
                    throw new Exception("Значение добавляемой метки уже существует");
                }
                if ($this->_isAjaxRequest) {
                    // Создадим обьект шаблона
                    $templater = Default_Plugin_SysBox::createViewSmarty();

                    //Установим параметры шаблона
                    $templater->tags = $addedTags;

                    // Получим результат шаблона
                    $html = $templater->render('blogmanager/lib/download-tags.tpl');
                    $json = array(
                        'result' => $this->Translate('Метка добавлена к записи блога'),
                        'tag' => $addedTags[0]['tag'],
                        'html' => $html
                    );
                } else {
                    $this->_flashMessenger->addMessage($this->Translate('Метка добавлена к записи блога'));
                }
            } else if ($request->getPost('delete')) {
                $post->deleteTags($tag);

                if ($this->_isAjaxRequest) {
                    $json = array(
                        'result' => $this->Translate('Метка удалена из записи блога'),
                        'tag' => $tag
                    );
                } else {
                    $this->_flashMessenger->addMessage($this->Translate('Метка удалена из записи блога'));
                }
            }
        } catch (Exception $e) {
            if ($this->_isAjaxRequest) {
                $json = array(
                    'class_message' => 'warning',
                    'messages' => array(
                        '<em>' . $this->Translate('Ошибка при добавлении / удалении метки в блог') . '</em>',
                        Default_Plugin_SysBox::getMessageError($e)
                    )
                );
            } else {
                $this->_flashMessenger->addMessage($this->Translate('Ошибка при добавлении / удалении метки в блог'));
            }
        }

        if ($this->_isAjaxRequest) {
            $this->sendJson($json);
        } else {
            $this->_redirect('/blogmanager/preview' . '?id=' . $post->getId());
        }
    }

    /**
     * Action - images
     * upload/download/ordering images
     * 
     * Access to the action is possible in the following paths:
     * - /blogmanager/images
     *
     * @return void
     */
    public function imagesAction() {
        $json = array();
        $filterSanitize = new Default_Form_Filter_Sanitize();
        //-----------------------
        // Получим обьект записи
        $request = $this->getRequest();

        $post_id = (int) $request->getPost('id');
        if (!$post_id) {
            $post_id = (int) $request->getParam('id');
        }

        $post = new Default_Model_DbTable_BlogPost($this->db);

        // Если конкретной записи нет, то перейдем к странице по умолчанию
        if (!$post->loadForUser($this->_identity->user_id, $post_id)) {
            $this->_redirect('/blogmanager');
        }

        // Определим тип операции над изображением: 'upload', 'reorder', 'delete'
        // и выполним ее
        if ($request->getPost('upload')) {// Загрузка файла через форму, если javascript отключен...
            $allParams = $this->_getAllParams();

            $formBlogPostImage = new Default_Form_BlogPostImage($post->getId());
            $result = $formBlogPostImage->isValid($allParams);
            if ($result) {

                // Проверим загружен ли файл?
                if ($formBlogPostImage->image->receive()) {
                    // Создадим обьект изображения для базы данных
                    $image = new Default_Model_DbTable_BlogPostImage($post->getDb());
                    $image->post_id = $post->getId();
                    $filename = $formBlogPostImage->image->getFileName();
                    if (!is_array($filename)) {
                        $image->uploadFile($filename);
                        $image->filename = basename($filename);
                        $image->comment = $request->getPost('comment');
                        $image->save();

                        // Определим признак ajax_upload
                        $this->_flashMessenger->addMessage($this->Translate('Изображение загружено'));
                    } else {
                        $class_message = 'warning';
                        $message = $this->Translate('Файл не выбран');
                        $this->_flashMessenger->addMessage($this->Translate('Файл не выбран'));
                    }
                } else {
                    $class_message = 'warning';
                    $message = $this->Translate('Ошибка получения файла');
                    $this->_flashMessenger->addMessage($this->Translate('Ошибка загрузки файла'));
                }
            } else {// Ошибка загрузки изображения
                $class_message = 'warning';
                $message = $this->getFormMessagesToString($formBlogPostImage);
                $this->_flashMessenger->addMessage($this->Translate('Ошибка формы! Неверно введены данные в форму.'));
            }
            // Загрузить файл с помощью FileUploader
        } else if (Default_Plugin_FileUploader::isFileUploader()) {// Загрузка файла через FileUploader, если javascript включен...
            // Получим вид загрузчика - Iframe или Xhr
            $fileUploader = Default_Plugin_FileUploader::isFileUploader();
            // list of valid extensions, ex. array("jpeg", "xml", "bmp")
            $allowedExtensions = explode(';', $request->getParam('allowedExtensions'));
            // max file size in bytes
            $sizeLimit = (int) $request->getParam('sizeLimit');
            // Получим обьект загрузчика файлов
            try {
                $uploader = new Default_Plugin_FileUploader($allowedExtensions, $sizeLimit);
                // Определим путь загрузки файлов
                $path = Default_Model_DbTable_BlogPostImage::GetUploadPath();
                $path .= '/';
                //Загрузим файлы
                $result = $uploader->handleUpload($path);
            } catch (Exception $e) {
                $json = array(
                    'class_message' => 'warning',
                    'messages' => array(
                        '<em>' . $this->Translate('Ошибка загрузки файла') . '</em>',
                        Default_Plugin_SysBox::getMessageError($e)
                    )
                );
                if ($fileUploader == 'Iframe') {
                    $this->sendJson_Html($json);
                } else {
                    $this->sendJson($json);
                }
                return;
            }

            if (isset($result['success'])) { // OK
                // Создадим обьект изображения
                try {
                    $image = new Default_Model_DbTable_BlogPostImage($post->getDb());
                    $image->post_id = $post->getId();
                    $filename = $path . $uploader->file->getName();
                    $image->uploadFile($filename);
                    $image->filename = basename($filename);
                    $image->save();
                    $json['success'] = $result['success'];
                    $json['image_id'] = $image->getId();
                    $json['filename'] = $image->filename;
                    $json['post_id'] = $image->post_id;
                    $json['url_image'] = $image->createThumbnail(200, 65);
                    $json['form_action'] = $this->getUrl('images', 'blogmanager');
                } catch (Exception $e) {
                    $json = array(
                        'class_message' => 'warning',
                        'messages' => array(
                            '<em>' . $this->Translate('Ошибка загрузки файла') . '</em>',
                            Default_Plugin_SysBox::getMessageError($e)
                        )
                    );
                    if ($fileUploader == 'Iframe') {
                        $this->sendJson_Html($json);
                    } else {
                        $this->sendJson($json);
                    }
                    return;
                }
            } else {// Error
                $json = array(
                    'class_message' => 'warning',
                    'messages' => array(
                        '<em>' . $this->Translate('Ошибка загрузки файла') . '</em>',
                        $result['error']
                    )
                );
                if ($fileUploader == 'Iframe') {
                    $this->sendJson_Html($json);
                } else {
                    $this->sendJson($json);
                }
                return;
            }
        } else if ($request->getPost('reorder')) {
            $order = $request->getPost('preview-images');
            $post->setImageOrder($order);
        } else if ($request->getPost('delete')) {
            $image_id = (int) $request->getPost('image');
            $image = new Default_Model_DbTable_BlogPostImage($this->db);
            if ($image->loadForPost($post->getId(), $image_id)) {
                $image->delete();

                // Определим кол. оставшихся изображений
                $count_images = count($post->images) - 1;

                if ($this->_isAjaxRequest) {
                    $json = array(
                        'deleted' => true,
                        'image_id' => $image_id,
                        'count_images' => $count_images
                    );
                } else
                    $this->_flashMessenger->addMessage($this->Translate('Изображение удалено'));
            }
        } else if ($request->getPost('comment_update')) {
            $image_id = (int) $request->getPost('image');
            $image = new Default_Model_DbTable_BlogPostImage($this->db);
            if ($image->loadForPost($post->getId(), $image_id)) {
                $comment = $request->getPost('comment');
                $comment = $filterSanitize->filter($comment);
                $image->comment = $comment;
                $image->save();

                if ($this->_isAjaxRequest) {
                    $json = array(
                        'commented' => true,
                        'title' => '',
                        'comment' => $image->comment
                    );
                } else
                    $this->_flashMessenger->addMessage($this->Translate('Комментарий к изображению обновился'));
            }
        }else if ($request->getPost('download_images')) {// Загрузим изображения в виде HTML на страницу
            // Получим изображения для статьи
            $images = Default_Model_DbTable_BlogPostImage::GetImages($this->db, array('post_id' => $post_id));
            // Создадим обьект шаблона
            $templater = Default_Plugin_SysBox::createViewSmarty();

            //Установим параметры шаблона
            $templater->images = $images;
            $templater->post_id = $post_id;

            // Получим результат шаблона
            $html = $templater->render('blogmanager/lib/download-images.tpl');
            if ($this->_isAjaxRequest) {
                $json = array(
                    'downloaded' => true,
                    'html' => $html
                );
            }
        }

        if ($this->_isAjaxRequest) {
            $this->sendJson($json);
        } else {
            if (Default_Plugin_FileUploader::isFileUploader() == 'Iframe') {
                $this->sendJson_Html($json);
            } else {
                if ($message) {
                    $getParams = '?id=' . $post->getId() .
                            '&message=' . $message .
                            '&class_message=' . $class_message;
                } else {
                    $getParams = '?id=' . $post->getId();
                }
                $this->_redirect('/blogmanager/preview' . $getParams);
            }
        }
    }

    /**
     * Action - audio
     * upload/download/ordering audio
     * 
     * Access to the action is possible in the following paths:
     * - /blogmanager/audio
     *
     * @return void
     */
    public function audioAction() {
        $json = array();
        $filterSanitize = new Default_Form_Filter_Sanitize();
        //-----------------------
        // Получим обьект записи
        $request = $this->getRequest();

        $post_id = (int) $request->getPost('id');
        if (!$post_id) {
            $post_id = (int) $request->getParam('id');
        }

        $post = new Default_Model_DbTable_BlogPost($this->db);

        // Если конкретной записи нет, то перейдем к странице по умолчанию
        if (!$post->loadForUser($this->_identity->user_id, $post_id)) {
            $this->_redirect('/blogmanager');
        }

        // Определим тип операции над аудио: 'upload', 'reorder', 'delete'
        // Загрузка файла с помощью - FileUploader
        if (Default_Plugin_FileUploader::isFileUploader()) {
            // Получим вид загрузчика - Iframe или Xhr
            $fileUploader = Default_Plugin_FileUploader::isFileUploader();
            // list of valid extensions, ex. array("jpeg", "xml", "bmp")
            $allowedExtensions = explode(';', $request->getParam('allowedExtensions'));
            // max file size in bytes
            $sizeLimit = (int) $request->getParam('sizeLimit');
            // Получим обьект загрузчика файлов
            try {
                $uploader = new Default_Plugin_FileUploader($allowedExtensions, $sizeLimit);
                // Определим путь загрузки файлов
                $path = Default_Model_DbTable_BlogPostAudio::GetUploadPath();
                $path .= '/';
                //Загрузим файлы
                $result = $uploader->handleUpload($path);
            } catch (Exception $e) {
                $json = array(
                    'class_message' => 'warning',
                    'messages' => array(
                        '<em>' . $this->Translate('Ошибка загрузки файла') . '</em>',
                        Default_Plugin_SysBox::getMessageError($e)
                    )
                );
                if ($fileUploader == 'Iframe') {
                    $this->sendJson_Html($json);
                } else {
                    $this->sendJson($json);
                }
                return;
            }

            if (isset($result['success'])) { // OK
                // Создадим обьект изображения
                try {
                    $audio = new Default_Model_DbTable_BlogPostAudio($post->getDb());
                    $audio->post_id = $post->getId();
                    $filename = $path . $uploader->file->getName();
                    $audio->uploadFile($filename);
                    $audio->filename = basename($filename);
                    if (!$audio->save()) {
                        $json = array(
                            'class_message' => 'warning',
                            'messages' => array(
                                '<em>' . $this->Translate('Ошибка при сохранении данных') . '</em>'
                            )
                        );
                        if ($fileUploader == 'Iframe') {
                            $this->sendJson_Html($json);
                        } else {
                            $this->sendJson($json);
                        }
                        return;
                    }

                    $json['success'] = $result['success'];
                    $json['audio_id'] = $audio->getId();
                    $json['filename'] = $audio->filename;
                    $json['post_id'] = $audio->post_id;
                    $json['url_image'] = $this->getUrlRes('images/media/thumbs/file-mp3.png') . '?id=' . $audio->getId();
                    $json['form_action'] = $this->getUrl('audio', 'blogmanager');
                } catch (Exception $e) {
                    $json = array(
                        'class_message' => 'warning',
                        'messages' => array(
                            '<em>' . $this->Translate('Ошибка загрузки файла') . '</em>',
                            Default_Plugin_SysBox::getMessageError($e)
                        )
                    );
                    if ($fileUploader == 'Iframe') {
                        $this->sendJson_Html($json);
                    } else {
                        $this->sendJson($json);
                    }
                    return;
                }
            } else {// Error
                $json = array(
                    'class_message' => 'warning',
                    'messages' => array(
                        '<em>' . $this->Translate('Ошибка загрузки файла') . '</em>',
                        $result['error']
                    )
                );
                if ($fileUploader == 'Iframe') {
                    $this->sendJson_Html($json);
                } else {
                    $this->sendJson($json);
                }
                return;
            }
        } else if ($request->getPost('reorder')) {
            $order = $request->getPost('preview-audio');
            $post->setAudioOrder($order);
        } else if ($request->getPost('delete')) {
            $audio_id = (int) $request->getPost('image');
            $audio = new Default_Model_DbTable_BlogPostAudio($this->db);
            if ($audio->loadForPost($post->getId(), $audio_id)) {
                $audio->delete();

                // Определим кол. оставшихся изображений
                $count_audios = count($post->audio) - 1;

                $json = array(
                    'deleted' => true,
                    'image_id' => $audio_id,
                    'count_images' => $count_audios
                );
            }
        } else if ($request->getPost('comment_update')) {
            $audio_id = (int) $request->getPost('image');
            $audio = new Default_Model_DbTable_BlogPostAudio($this->db);
            if ($audio->loadForPost($post->getId(), $audio_id)) {
                $comment = $request->getPost('comment');
                $comment = $filterSanitize->filter($comment);
                $arrComment = explode('#', $comment);
                if (count($arrComment) > 1) {
                    $audio->name = $arrComment[0];
                    $audio->comment = $arrComment[1];
                } else {
                    $audio->name = $arrComment[0];
                }
                if (!$audio->save()) {
                    $json = array(
                        'class_message' => 'warning',
                        'messages' => array(
                            '<em>' . $this->Translate('Ошибка при сохранении данных') . '</em>'
                        )
                    );
                    $this->sendJson($json);
                    return;
                }

                $json = array(
                    'commented' => true,
                    'title' => $audio->name,
                    'comment' => $audio->comment
                );
            }
        } else if ($request->getPost('download_images')) {// Загрузим изображения в виде HTML на страницу
            // Получим файлы музыки для статьи
            $audios = Default_Model_DbTable_BlogPostAudio::GetAudio($this->db, array('post_id' => $post_id));
            // Создадим обьект шаблона
            $templater = Default_Plugin_SysBox::createViewSmarty();

            //Установим параметры шаблона
            $templater->audios = $audios;
            $templater->post_id = $post_id;

            // Получим результат шаблона
            $html = $templater->render('blogmanager/lib/download-audio.tpl');
            $json = array(
                'downloaded' => true,
                'html' => $html
            );
        }

        if ($this->_isAjaxRequest) {
            $this->sendJson($json);
        } else {
            $this->sendJson_Html($json);
        }
    }

    /**
     * Action - video
     * upload/download/ordering video
     * 
     * Access to the action is possible in the following paths:
     * - /blogmanager/video
     *
     * @return void
     */
    public function videoAction() {
        $json = array();
        $filterSanitize = new Default_Form_Filter_Sanitize();
        //-----------------------
        // Получим обьект записи
        $request = $this->getRequest();
        $params = $request->getParams();
        $post_id = (int) $request->getPost('id');
        if (!$post_id) {
            $post_id = (int) $request->getParam('id');
        }

        $post = new Default_Model_DbTable_BlogPost($this->db);

        // Если конкретной записи нет, то перейдем к странице по умолчанию
        if (!$post->loadForUser($this->_identity->user_id, $post_id)) {
            $this->_redirect('/blogmanager');
        }

        // Определим тип операции над видео: 'upload', 'reorder', 'delete'
        // Загрузка файла с помощью - FileUploader
        if (Default_Plugin_FileUploader::isFileUploader()) {
            // Получим вид загрузчика - Iframe или Xhr
            $fileUploader = Default_Plugin_FileUploader::isFileUploader();
            // list of valid extensions, ex. array("jpeg", "xml", "bmp")
            $allowedExtensions = $params['allowedExtensions'];
            $allowedExtensions = str_replace(' ', '', $allowedExtensions);
            $arrAllowedExtensions = explode(';', $allowedExtensions);
            // max file size in bytes
            $sizeLimit = (int) $request->getParam('sizeLimit');
            // Получим обьект загрузчика файлов
            try {
                $uploader = new Default_Plugin_FileUploader($arrAllowedExtensions, $sizeLimit);
                // Определим путь загрузки файлов
                $path = Default_Model_DbTable_BlogPostVideo::GetUploadPath();
                $path .= '/';
                //Загрузим файлы
                $result = $uploader->handleUpload($path);
            } catch (Exception $e) {
                $json = array(
                    'class_message' => 'warning',
                    'messages' => array(
                        '<em>' . $this->Translate('Ошибка загрузки файла') . '</em>',
                        Default_Plugin_SysBox::getMessageError($e)
                    )
                );
                if ($fileUploader == 'Iframe') {
                    $this->sendJson_Html($json);
                } else {
                    $this->sendJson($json);
                }
                return;
            }

            if (isset($result['success'])) {
                // Создадим обьект изображения
                try {

                    $filename = $path . $uploader->file->getName();
                    $pathinfo = pathinfo($filename);
                    $ext = $pathinfo['extension'];

                    $video = new Default_Model_DbTable_BlogPostVideo($post->getDb());
                    $video->post_id = $post->getId();
                    $video->uploadFile($filename);
                    $video->identifier = basename($filename);
                    $video->type = 'file-' . $ext;
                    if (!$video->save()) {
                        $json = array(
                            'class_message' => 'warning',
                            'messages' => array(
                                '<em>' . $this->Translate('Ошибка при сохранении данных') . '</em>'
                            )
                        );
                        if ($fileUploader == 'Iframe') {
                            $this->sendJson_Html($json);
                        } else {
                            $this->sendJson($json);
                        }
                        return;
                    }

                    $json['success'] = $result['success'];
                    $json['video_id'] = $video->getId();
                    $json['filename'] = $video->identifier;
                    $json['post_id'] = $video->post_id;
                    $json['form_action'] = $this->getUrl('video', 'blogmanager');

                    // Определим путь к изображению для видео 
                    $srcImage = 'images/media/thumbs/' . $video->type . '.png';

                    $json['url_image'] = $this->getUrlRes($srcImage) . '?id=' . $video->getId();
                } catch (Exception $e) {
                    $json = array(
                        'class_message' => 'warning',
                        'messages' => array(
                            '<em>' . $this->Translate('Ошибка загрузки файла') . '</em>',
                            Default_Plugin_SysBox::getMessageError($e)
                        )
                    );
                    if ($fileUploader == 'Iframe') {
                        $this->sendJson_Html($json);
                    } else {
                        $this->sendJson($json);
                    }
                    return;
                }
            } else {// Error
                $json = array(
                    'class_message' => 'warning',
                    'messages' => array(
                        '<em>' . $this->Translate('Ошибка загрузки файла') . '</em>',
                        $result['error']
                    )
                );
                if ($fileUploader == 'Iframe') {
                    $this->sendJson_Html($json);
                } else {
                    $this->sendJson($json);
                }
                return;
            }
        } else if ($request->getPost('add_video_url')) {
            // Создадим обьект изображения
            try {
                $strInfoVideo = $request->getPost('info_video');
                $strInfoVideo = stripslashes($strInfoVideo);
                $arrInfoVideo = Zend_Json::decode($strInfoVideo);

                $video = new Default_Model_DbTable_BlogPostVideo($post->getDb());
                $video->post_id = $post->getId();
                $video->identifier = $arrInfoVideo['url'];
                $video->type = $arrInfoVideo['type'];
                if (!$video->save()) {
                    $json = array(
                        'class_message' => 'warning',
                        'messages' => array(
                            '<em>' . $this->Translate('Ошибка при сохранении данных') . '</em>'
                        )
                    );
                    $this->sendJson($json);
                    return;
                }

                // Определим путь к изображению для видео 
                $srcImage = 'images/media/thumbs/' . $video->type . '.png';

                $json['video_id'] = $video->getId();
                $json['filename'] = $video->identifier;
                $json['post_id'] = $video->post_id;
                $json['url_image'] = $this->getUrlRes($srcImage) . '?id=' . $video->getId();
                $json['form_action'] = $this->getUrl('video', 'blogmanager');
                $json['result'] = $this->Translate('URL на ресурс добавлен');
            } catch (Exception $e) {
                $json = array(
                    'class_message' => 'warning',
                    'messages' => array(
                        '<em>' . $this->Translate('Ошибка загрузки файла') . '</em>',
                        Default_Plugin_SysBox::getMessageError($e)
                    )
                );
                $this->sendJson($json);
                return;
            }
        } else if ($request->getPost('reorder')) {
            $order = $request->getPost('preview-video');
            $post->setVideoOrder($order);
        } else if ($request->getPost('delete')) {
            $video_id = (int) $request->getPost('image');
            $video = new Default_Model_DbTable_BlogPostVideo($this->db);
            if ($video->loadForPost($post->getId(), $video_id)) {
                $video->delete();

                // Определим кол. оставшихся изображений
                $count_videos = count($post->video) - 1;

                $json = array(
                    'deleted' => true,
                    'image_id' => $video_id,
                    'count_images' => $count_videos
                );
            }
        } else if ($request->getPost('comment_update')) {
            $video_id = (int) $request->getPost('image');
            $video = new Default_Model_DbTable_BlogPostVideo($this->db);
            if ($video->loadForPost($post->getId(), $video_id)) {

                $comment = $request->getPost('comment');

                // Отфильтруем ненужные теги в комментарии
                $comment = $filterSanitize->filter($comment);

                // Выделим название и комментарий
                $arrComment = explode('#', $comment);

                // Если тип видео -> 'url-godtv', то важно получить точное название видео
                // это название должно точно соответствовать пути к загрузочной странице этого видео
                // пр. http://god-tv.ru/%D0%A0%D0%B5%D1%88%D0%B0%D1%8E%D1%89%D0%B8%D0%B9-%D1%80%D1%8B%D0%B2%D0%BE%D0%BA-%D1%81%D0%BC%D0%BE%D1%82%D1%80%D0%B5%D1%82%D1%8C-%D0%BE%D0%BD%D0%BB%D0%B0%D0%B9%D0%BD
                // или так %D0%A0%D0%B5%D1%88%D0%B0%D1%8E%D1%89%D0%B8%D0%B9-%D1%80%D1%8B%D0%B2%D0%BE%D0%BA-%D1%81%D0%BC%D0%BE%D1%82%D1%80%D0%B5%D1%82%D1%8C-%D0%BE%D0%BD%D0%BB%D0%B0%D0%B9%D0%BD
                // или так "Решающий-рывок-смотреть-онлайн"
                if ($video->type == 'url-godtv') {
                    $tmpName = urldecode($arrComment[0]);
                    $tmpNames = explode('/', $tmpName);
                    $tmpName = $tmpNames[count($tmpNames) - 1];
                    $arrComment[0] = $tmpName;
                }

                if (count($arrComment) > 1) {
                    $video->name = $arrComment[0];
                    $video->comment = $arrComment[1];
                } else {
                    $video->name = $arrComment[0];
                }
                // Сохраним в базе данных
                if (!$video->save()) {
                    $json = array(
                        'class_message' => 'warning',
                        'messages' => array(
                            '<em>' . $this->Translate('Ошибка при сохранении данных') . '</em>'
                        )
                    );
                    $this->sendJson($json);
                    return;
                }

                $json = array(
                    'commented' => true,
                    'title' => $video->name,
                    'comment' => $video->comment
                );
            }
        } else if ($request->getPost('download_images')) {// Загрузим изображения в виде HTML на страницу
            // Получим файлы видео для статьи
            $videos = Default_Model_DbTable_BlogPostVideo::GetVideo($this->db, array('post_id' => $post_id));
            // Создадим обьект шаблона
            $templater = Default_Plugin_SysBox::createViewSmarty();

            //Установим параметры шаблона
            $templater->videos = $videos;
            $templater->post_id = $post_id;

            // Получим результат шаблона
            $html = $templater->render('blogmanager/lib/download-video.tpl');
            $json = array(
                'downloaded' => true,
                'html' => $html
            );
        }

        if ($this->_isAjaxRequest) {
            $this->sendJson($json);
        } else {
            $this->sendJson_Html($json);
        }
    }

    /**
     * Action - locations
     * loading geographic locations for posts
     *
     * Access to the action is possible in the following paths:
     * - /blogmanager/locations
     *
     * @return void
     */
    public function locationsAction() {
        $request = $this->getRequest();

        $post_id = (int) $request->getQuery('id');

        $post = new Default_Model_DbTable_BlogPost($this->db);
        if (!$post->loadForUser($this->_identity->user_id, $post_id))
            $this->_redirect('/blogmanager');

        $this->_breadcrumbs->addStep(
                $this->Translate('Просмотр сообщения') . ': ' . $post->profile->title, $this->getUrl('preview') . '?id=' . $post->getId()
        );
        $this->_breadcrumbs->addStep($this->Translate('Координаты'));

        $this->view->post = $post;
    }

    /**
     * Action - locationsmanage
     * management geographic coordinates
     *
     * Access to the action is possible in the following paths:
     * - /blogmanager/locationsmanage
     *
     * @return void
     */
    public function locationsmanageAction() {
        $request = $this->getRequest();

        $action = $request->getPost('action');
        $post_id = $request->getPost('post_id');
        if ($request->getPost('user_id')) {
            $user_id = $request->getPost('user_id');
        } else {
            $user_id = $this->_identity->user_id;
        }

        $ret = array('post_id' => 0);

        $post = new Default_Model_DbTable_BlogPost($this->db);

        if ($post->loadForUser($user_id, $post_id)) {
            $ret['post_id'] = $post->getId();

            switch ($action) {
                case 'get':
                    $ret['locations'] = array();
                    foreach ($post->locations as $location) {
                        $location_id = $location->getId();
                        $details_url = $this->getUrl('details', 'blogmanager') . "?post_id=$post_id&location_id=$location_id";
                        $ret['locations'][] = array(
                            'location_id' => $location_id,
                            'latitude' => $location->latitude,
                            'longitude' => $location->longitude,
                            'description' => $location->description,
                            'content' => $location->content,
                            'correction' => $location->correction,
                            'details' => $location->details ? $details_url : ''
                        );
                    }

                    break;
                case 'description'://correction

                    $formBlogPostLocation = new Default_Form_BlogPostLocation();
                    // Проверяем на валидность поля формы
                    $allParams = $this->_getAllParams();
                    $result = $formBlogPostLocation->isValid($allParams);
                    if ($result) {
                        $location_id = $request->getPost('location_id');
                        $location = new Default_Model_DbTable_BlogPostLocation($this->db);
                        if ($location->loadForPost($post->getId(), $location_id)) {
                            $location->description = $formBlogPostLocation->getValue('description');
                            $location->save();

                            $location_id = $location->getId();
                            $ret['location_id'] = $location_id;
                            $ret['latitude'] = $location->latitude;
                            $ret['longitude'] = $location->longitude;
                            $ret['description'] = $location->description;
                            $ret['content'] = $location->content;
                            $ret['correction'] = $location->correction;
                            if ($location->details)
                                $ret['details'] = $this->getUrl('details', 'blogmanager') . "?post_id=$post_id&location_id=$location_id";
                        }
                    } else {
                        $ret['location_id'] = 0;
                    }
                    break;
                case 'content':

                    $formBlogPostLocation = new Default_Form_BlogPostLocation();
                    // Проверяем на валидность поля формы
                    $allParams = $this->_getAllParams();
                    $result = $formBlogPostLocation->isValid($allParams);
                    if ($result) {
                        $location_id = $request->getPost('location_id');
                        $location = new Default_Model_DbTable_BlogPostLocation($this->db);
                        if ($location->loadForPost($post->getId(), $location_id)) {
                            $content = $formBlogPostLocation->getValue('content');
                            $location->content = $content;
                            $location->save();

                            $location_id = $location->getId();
                            $ret['location_id'] = $location_id;
                            $ret['latitude'] = $location->latitude;
                            $ret['longitude'] = $location->longitude;
                            $ret['description'] = $location->description;
                            $ret['content'] = $location->content;
                            $ret['correction'] = $location->correction;
                            if ($location->details)
                                $ret['details'] = $this->getUrl('details', 'blogmanager') . "?post_id=$post_id&location_id=$location_id";
                        }
                    } else {
                        $ret['location_id'] = 0;
                    }
                    break;
                case 'get_details':
                    $location_id = $request->getPost('location_id');
                    $location = new Default_Model_DbTable_BlogPostLocation($this->db);
                    if ($location->loadForPost($post->getId(), $location_id)) {
                        $location_id = $location->getId();
                        $ret['location_id'] = $location_id;
                        $ret['details'] = $location->details;
                    } else {
                        $ret['location_id'] = 0;
                        $ret['error'] = $this->Translate('Ошибка получения подробной информации по географической координате');
                    }
                    break;
                case 'set_details':

                    $formBlogPostLocation = new Default_Form_BlogPostLocation();
                    // Проверяем на валидность поля формы
                    $allParams = $this->_getAllParams();
                    $result = $formBlogPostLocation->isValid($allParams);
                    if ($result) {
                        $location_id = $request->getPost('location_id');
                        $location = new Default_Model_DbTable_BlogPostLocation($this->db);
                        if ($location->loadForPost($post->getId(), $location_id)) {
                            $details = $formBlogPostLocation->getValue('details');
                            $location->details = $details;
                            $location->save();

                            $location_id = $location->getId();
                            $ret['location_id'] = $location_id;
                            $ret['latitude'] = $location->latitude;
                            $ret['longitude'] = $location->longitude;
                            $ret['description'] = $location->description;
                            $ret['content'] = $location->content;
                            $ret['correction'] = $location->correction;
                            if ($location->details)
                                $ret['details'] = $this->getUrl('details', 'blogmanager') . "?post_id=$post_id&location_id=$location_id";
                        }
                    } else {
                        $ret['location_id'] = 0;
                        $ret['error'] = $this->Translate('Ошибка записи подробной информации по географической координате');
                    }
                    break;
                case 'correction':

                    $formBlogPostLocation = new Default_Form_BlogPostLocation();
                    // Проверяем на валидность поля формы
                    $allParams = $this->_getAllParams();
                    $result = $formBlogPostLocation->isValid($allParams);
                    if ($result) {
                        $location_id = $request->getPost('location_id');
                        $location = new Default_Model_DbTable_BlogPostLocation($this->db);
                        if ($location->loadForPost($post->getId(), $location_id)) {
                            $location->correction = $formBlogPostLocation->getValue('correction');
                            $location->save();

                            $location_id = $location->getId();
                            $ret['location_id'] = $location_id;
                            $ret['latitude'] = $location->latitude;
                            $ret['longitude'] = $location->longitude;
                            $ret['description'] = $location->description;
                            $ret['content'] = $location->content;
                            $ret['correction'] = $location->correction;
                            if ($location->details)
                                $ret['details'] = $this->getUrl('details', 'blogmanager') . "?post_id=$post_id&location_id=$location_id";
                        }
                    } else {
                        $ret['location_id'] = 0;
                    }
                    break;
                case 'add':
                    $formBlogPostLocation = new Default_Form_BlogPostLocation();
                    // Проверяем на валидность поля формы
                    $allParams = $this->_getAllParams();
                    $result = $formBlogPostLocation->isValid($allParams);
                    if ($result) {
                        $location = new Default_Model_DbTable_BlogPostLocation($this->db);
                        $location->post_id = $post->getId();
                        $location->description = $formBlogPostLocation->getValue('description');
                        $location->longitude = $formBlogPostLocation->getValue('longitude');
                        $location->latitude = $formBlogPostLocation->getValue('latitude');
                        if ($request->getPost('content'))
                            $location->content = $formBlogPostLocation->getValue('content');
                        $location->save();

                        $ret['location_id'] = $location->getId();
                        $ret['latitude'] = $location->latitude;
                        $ret['longitude'] = $location->longitude;
                        $ret['description'] = $location->description;
                        $ret['content'] = $location->content;
                        $ret['correction'] = $location->correction;
                    } else {
                        $ret['location_id'] = 0;
                    }

                    break;

                case 'delete':
                    $location_id = $request->getPost('location_id');
                    $location = new Default_Model_DbTable_BlogPostLocation($this->db);
                    if ($location->loadForPost($post->getId(), $location_id)) {
                        $ret['location_id'] = $location->getId();
                        $location->delete();
                    }

                    break;

                case 'move':
                    $formBlogPostLocation = new Default_Form_BlogPostLocation();
                    // Проверяем на валидность поля формы
                    $allParams = $this->_getAllParams();
                    $result = $formBlogPostLocation->isValid($allParams);

                    if ($result) {
                        $location_id = $request->getPost('location_id');
                        $location = new Default_Model_DbTable_BlogPostLocation($this->db);
                        if ($location->loadForPost($post->getId(), $location_id)) {
                            if ($request->getPost('description')) {
                                $location->description = $formBlogPostLocation->getValue('description');
                            }
                            $location->longitude = $formBlogPostLocation->getValue('longitude');
                            $location->latitude = $formBlogPostLocation->getValue('latitude');
                            $location->save();

                            $location_id = $location->getId();
                            $ret['location_id'] = $location_id;
                            $ret['latitude'] = $location->latitude;
                            $ret['longitude'] = $location->longitude;
                            $ret['description'] = $location->description;
                            $ret['content'] = $location->content;
                            $ret['correction'] = $location->correction;
                            if ($location->details)
                                $ret['details'] = $this->getUrl('details', 'blogmanager') . "?post_id=$post_id&location_id=$location_id";
                        } else {
                            $ret['location_id'] = 0;
                        }
                    } else {
                        $ret['location_id'] = 0;
                    }
                    break;
            }
        }

        $this->sendJson($ret);
    }

    /**
     * Action - getlocations
     * get geographic coordinates
     *
     * Access to the action is possible in the following paths:
     * - /blogmanager/getlocations?user_id=1234&post_id=2345
     *
     * @return void
     */
    public function getlocationsAction() {
        $request = $this->getRequest();
        $post_id = $request->getPost('post_id');
        $user_id = $request->getPost('user_id');
        $ret = array('post_id' => 0);

        $post = new Default_Model_DbTable_BlogPost($this->db);

        if ($post->loadForUser($user_id, $post_id)) {
            $ret['post_id'] = $post->getId();
            $ret['locations'] = array();
            foreach ($post->locations as $location) {
                $location_id = $location->getId();
                $details_url = $this->getUrl('details', 'blogmanager') . "?post_id=$post_id&location_id=$location_id";
                $ret['locations'][] = array(
                    'location_id' => $location_id,
                    'latitude' => $location->latitude,
                    'longitude' => $location->longitude,
                    'description' => $location->description,
                    'content' => $location->content,
                    'correction' => $location->correction,
                    'details' => $location->details ? $details_url : ''
                );
            }
        }

        $this->sendJson($ret);
    }

    /**
     * Action - details
     * get detailed information for geographical coordinate
     *
     * Access to the action is possible in the following paths:
     * - /blogmanager/details?post_id=2345&location_id=1234
     *
     * @return void
     */
    public function detailsAction() {
        $request = $this->getRequest();
        $post_id = $request->getParam('post_id');
        $location_id = $request->getParam('location_id');

        $location = new Default_Model_DbTable_BlogPostLocation($this->db);
        if ($location->loadForPost($post_id, $location_id)) {
            $details = $location->details;
        } else {
            $details = $this->Translate('Ошибка получения подробной информации по географической координате');
            $details = '<div class="error">' . $details . '</div>';
        }

        $this->view->details = $details;
    }

}

?>