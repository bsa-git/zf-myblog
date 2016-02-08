<?php

/**
 * Admin_BlogController
 *
 * Контроллер - Blog
 * Управление блогом
 *
 * @uses       Default_Plugin_TableGrid
 * @package    Module-Admin (Администрирование сайта)
 * @subpackage Controllers
 */
class Admin_BlogController extends Default_Plugin_TableGridController {

    /**
     * Инициализация контроллера
     *
     */
    public function init() {
        parent::init();
        $this->_breadcrumbs->addStep($this->Translate('Блоги'), $this->getUrl(null, 'blog', 'admin'));
    }

    /**
     * Действие по умолчанию
     *
     * Доступ к действию возможем по следующим путям urls:
     * - /admin/blog/index
     * - /admin/blog
     *
     * @return void
     */
    public function indexAction() {
        
    }

    /**
     * Действие - posts
     * Получить список всех записей пользователей
     *
     * Доступ к действию возможем по следующим путям urls:
     * - /admin/blog/posts
     *
     * @return void
     */
    public function postsAction() {

        $params = $this->_request->getParams();
        $this->view->ajax_post = isset($params['ajax_post']) ? TRUE : FALSE;

        //Добавим путь к действию
        $this->_breadcrumbs->addStep($this->Translate('Список сообщений в блогах'));
    }

    /**
     * Действие rows (получить данные)
     * по этому действию происходит вывод всех данных
     * в соответствии с параметрами запроса
     *
     * Доступ к действию возможем по следующим путям urls:
     * - /admin/blog/rows
     *
     * @return void
     */
    public function rowsAction() {
        parent::rowsAction();
    }

    /**
     * Действие data (данные)
     * по этому действию происходит запрос к данным по определенным
     * полям таблицы. Затем эти данные помещаются в ComboBox
     * для удобного редактирования этих полей таблицы
     *
     * Доступ к действию возможем по следующим путям urls:
     * - /admin/blog/data
     *
     * @return void
     */
    public function dataAction() {
        parent::dataAction();
    }

    /**
     * Действие validate (проверка значения)
     * по этому действию происходит проверка соответсвующего значения
     * параметры значения передаются в параметрах запроса
     *
     * Доступ к действию возможем по следующим путям urls:
     * - /admin/blog/validate
     *
     * @return void
     */
    public function validateAction() {
        parent::validateAction();
    }

    /**
     * Действие save (сохранение данных)
     * по этому действию происходит сохранение измененных данных
     * или добавленых данных
     *
     * Доступ к действию возможем по следующим путям urls:
     * - /admin/blog/save
     *
     * @return void
     */
    public function saveAction() {
        parent::saveAction();
    }

    /**
     * Действие delete (удаление данных)
     * по этому действию происходит удаление данных из базы данных
     *
     * Доступ к действию возможем по следующим путям urls:
     * - /admin/blog/delete
     *
     * @return void
     */
    public function deleteAction() {
        parent::deleteAction();
    }

    /**
     * Изменить/Вставить запись таблицы
     *
     * @param array $row
     *
     * @return bool
     */
    public function saveRowTable($row) {
        $prefix = "";
        //-----------

        $result = parent::saveRowTable($row);
        // Сохраним событие в лог
        if ($result) {
            $isSaved = $row->isSaved();
            $username = $this->_identity->username;
            $params = $this->_request->getParams();
            $table = $params['table'];
            switch ($table) {
                case 'admin.blog_posts':
                    $title = $row->profile->title;
                    $prefix = "title=\"$title\"";
                    break;
                case 'admin.blog_posts_audio':
                    $prefix = "name=\"$row->name\"";
                    break;
                case 'admin.blog_posts_images':
                    $id = $row->getId();
                    $prefix = "id=\"$id\"";
                    break;
                case 'admin.blog_posts_locations':
                    $prefix = "description=\"$row->description\"";
                    break;
                case 'admin.blog_posts_tags':
                    $prefix = "label=\"$row->label\"";
                    break;
                case 'admin.blog_posts_video':
                    $prefix = "name=\"$row->name\"";
                    break;
                default:
                    break;
            }
            if ($isSaved) {
                $message = "User - \"$username\" updated a row in the table-\"$table\" with $prefix";
                $this->_logMsg->admin_row_update($message);
            } else {
                $message = "User - \"$username\" inserted a row into table-\"$table\" with $prefix";
                $this->_logMsg->admin_row_insert($message);
            }
        }
        return $result;
    }

    /**
     * Удалить обьект записи таблицы
     *
     * @param array $row
     *
     * @return bool
     */
    public function deleteRowTable($row) {
        $prefix = "";
        //-----------

        $result = parent::deleteRowTable($row);
        // Сохраним событие в лог
        if ($result) {
            $username = $this->_identity->username;
            $params = $this->_request->getParams();
            $table = $params['table'];
            switch ($table) {
                case 'admin.blog_posts':
                    $title = $row->profile->title;
                    $prefix = "title=\"$title\"";
                    break;
                case 'admin.blog_posts_audio':
                    $prefix = "name=\"$row->name\"";
                    break;
                case 'admin.blog_posts_images':
                    $id = $row->getId();
                    $prefix = "id=\"$id\"";
                    break;
                case 'admin.blog_posts_locations':
                    $prefix = "description=\"$row->description\"";
                    break;
                case 'admin.blog_posts_tags':
                    $prefix = "label=\"$row->label\"";
                    break;
                case 'admin.blog_posts_video':
                    $prefix = "name=\"$row->name\"";
                    break;
                default:
                    break;
            }
            $message = "User - \"$username\" deleted a row from table-\"$table\" with $prefix";
            $this->_logMsg->admin_row_delete($message);
        }
        return $result;
    }

    /**
     * Действие search (поиск значения в таблице)
     * по этому действию происходит поиск строки
     * в таблице и возвращается номер стр. поиска
     * если поиск произошел успешно
     *
     * Доступ к действию возможем по следующим путям urls:
     * - /admin/blog/search
     *
     * @return void
     */
    public function searchAction() {
        parent::searchAction();
    }

    //=============== РАБОТА С ОТЧЕТАМИ =================//

    /**
     * Действие - report
     * Отчеты по документам
     * 
     * Доступ к действию возможем по следующим путям urls:
     * - /admin/blog/report
     *
     * @return void
     */
    public function reportAction() {
        parent::reportAction();
    }

    /**
     * 
     * Получить данные для отчета
     * 
     * @param string $table
     *
     * @return array
     */
    protected function _getReportData($table) {
        $arrResultData = array();
        $footers = array();
        $rows_footer = array();
        $footer_colspan = 2;
        //-------------------
        // Получим данные для отчета
        $arrData = parent::_getReportData($table);

        // Установим параметры PDF
        $arrResultData['pdf']['logo_report'] = Default_Plugin_SysBox::getFullURL_Res('/images/system/index_doc48x48.png');
        $arrResultData['pdf']['title_report'] = $this->Translate('Список сообщений');
        $arrResultData['pdf']['is_row_header'] = TRUE;
        $arrResultData['pdf']['pageFormat'] = 'A4';

        // Установим параметры для HTML
        $arrResultData['html']['column_model'] = $arrData['column_model'];
        $arrResultData['html']['rows_body'] = isset($arrData['rows']) ? $arrData['rows'] : array();
        $arrResultData['html']['is_group_head'] = $arrData['is_group_head'];
        $arrResultData['html']['is_row_header'] = TRUE;
        $arrResultData['html']['footer_colspan'] = $footer_colspan;

        // Получим данные для отчета
        switch ($table) {
            case 'admin.blog_posts':

                $footer_colspan = 1;

                // Исключим в отчете некоторые поля
                $fieldsExcluded = array('id', 'actual');
                $newModelColumns = parent::_excludeFieldsFromReport($fieldsExcluded, $arrData['column_model']);
                $arrResultData['html']['column_model'] = $newModelColumns;

                // Получим массив записей для нижнего колонтитула
                $footers[] = array(
                    'user_id' => $this->Translate('Всего записей') . ':',
                    'url' => 'count',
                );
                $footers[] = array(
                    'user_id' => $this->Translate('Минимальная дата') . ':',
                    'ts_created' => 'min',
                );
                $footers[] = array(
                    'user_id' => $this->Translate('Максимальная дата') . ':',
                    'ts_published' => 'max'
                );

                $rows_footer = parent::_footerForReport(array(
                            'footer_colspan' => $footer_colspan,
                            'rows' => $arrData['rows'],
                            'column_model' => $newModelColumns,
                            'footers' => $footers
                ));

                // Установим параметры для HTML
                $arrResultData['html']['logo_report'] = Default_Plugin_SysBox::getFullURL_Res('/images/system/documents32x32.png');
                $arrResultData['html']['title_report'] = $this->Translate('Список сообщений в блогах');
                $arrResultData['html']['rows_footer'] = $rows_footer;
                $arrResultData['html']['footer_colspan'] = $footer_colspan;

                // Установим параметры для PDF
                $arrResultData['pdf']['is_row_header'] = false;
                break;
            default:
                break;
        }
        return $arrResultData;
    }

    /**
     * Получить кол. строк в таблице
     *
     * @param array $options
     *
     * @return int
     */
    public function getCountRowsTable($options = NULL) {
        $request = $this->getRequest();
        $params = $request->getParams();
        $table = $params['table'];
        //---------------------------


        if ($table == 'admin.blog_posts') {
            return Default_Model_DbTable_BlogPost::GetPostsCount($this->db, $options);
        }

        if ($table == 'admin.blog_posts_tags') {
            $post_ids = Default_Model_DbTable_BlogPost::GetPostsIds_Array($this->db, $options);
            return Default_Model_DbTable_BlogPostTag::GetPostsTags_Count($this->db, array('post_id' => $post_ids));
        }

        if ($table == 'admin.blog_posts_images') {
            $post_ids = Default_Model_DbTable_BlogPost::GetPostsIds_Array($this->db, $options);
            return Default_Model_DbTable_BlogPostImage::GetPostsImages_Count($this->db, array('post_id' => $post_ids));
        }

        if ($table == 'admin.blog_posts_audio') {
            $post_ids = Default_Model_DbTable_BlogPost::GetPostsIds_Array($this->db, $options);
            return Default_Model_DbTable_BlogPostAudio::GetPostsAudio_Count($this->db, array('post_id' => $post_ids));
        }

        if ($table == 'admin.blog_posts_video') {
            $post_ids = Default_Model_DbTable_BlogPost::GetPostsIds_Array($this->db, $options);
            return Default_Model_DbTable_BlogPostVideo::GetPostsVideo_Count($this->db, array('post_id' => $post_ids));
        }

        if ($table == 'admin.blog_posts_locations') {
            $post_ids = Default_Model_DbTable_BlogPost::GetPostsIds_Array($this->db, $options);
            return Default_Model_DbTable_BlogPostLocation::GetPostsLocations_Count($this->db, array('post_id' => $post_ids));
        }
    }

    /**
     * Создать обьект записи таблицы
     *
     * @param array $options
     *
     * @return object
     */
    public function createRowTable($options = NULL) {
        $request = $this->getRequest();
        $params = $request->getParams();
        $table = $params['table'];
        //---------------------------

        if ($table == 'admin.blog_posts') {
            return new Default_Model_DbTable_BlogPost($this->db);
        }

        if ($table == 'admin.blog_posts_tags') {
            return new Default_Model_DbTable_BlogPostTag($this->db);
        }

        if ($table == 'admin.blog_posts_images') {
            return new Default_Model_DbTable_BlogPostImage($this->db);
        }

        if ($table == 'admin.blog_posts_audio') {
            return new Default_Model_DbTable_BlogPostAudio($this->db);
        }

        if ($table == 'admin.blog_posts_video') {
            return new Default_Model_DbTable_BlogPostVideo($this->db);
        }

        if ($table == 'admin.blog_posts_locations') {
            return new Default_Model_DbTable_BlogPostLocation($this->db);
        }
    }

    /**
     * Получить записи таблицы
     *
     * @param array $options
     *
     * @return array
     */
    public function getRowsArraysTable($options = NULL) {
        $newRows = array();
        $sortRows = array();
        $newRow = array();
        //-------------------
        // Получим параметры запроса
        $request = $this->getRequest();
        $params = $request->getParams();
        $table = $params['table'];
        $fromPage = $options['fromPage'];
        $toPage = $options['toPage'];
        if ($options['sortColumn']) {
            $sortColumn = $options['sortColumn'];
        } else {
            $sortColumn = 'id';
        }
        $ascDescFlg = $options['ascDescFlg'];

        $count = 1;

        if ($table == 'admin.blog_posts') {
            // Получим все строки сообщений с учетом параметров
            $rows = Default_Model_DbTable_BlogPost::GetPosts_Array($this->db, $options);
            foreach ($rows as $row) {
                foreach ($row as $key => $value) {
                    if (is_array($value)) {
                        
                    } else {
                        $newRow[$key] = $value;
                    }
                }
                $newRows[] = $newRow;
                $newRow = array();
            }
            return $newRows;
        }

        if ($table == 'admin.blog_posts_tags') {
            // Получим все строки сообщений с учетом параметров
            $options['offset'] = NULL;
            $options['limit'] = NULL;
            $options['sort'] = FALSE;
            $rows = Default_Model_DbTable_BlogPost::GetPosts_Array($this->db, $options);
            foreach ($rows as $row) {
                foreach ($row as $key => $value) {
                    if (is_array($value)) {
                        if ($key == '_tags_') {
                            $arrValues = $value;
                            $arrPost = $newRow;
                            foreach ($arrValues as $arrValue) {
                                $newRow = $arrValue + $arrPost + array(
                                    'sortColumn' => $sortColumn,
                                    'ascDescFlg' => $ascDescFlg
                                );
                                $newRows[] = $newRow;
                            }
                        }
                    } else {
                        if (($sortColumn !== 'id' && $key == $sortColumn) || $key == 'title') {
                            $newRow[$key] = $value;
                        }
                    }
                }
            }
            usort($newRows, array("Default_Model_DatabaseObject", "_SortProfiles_Arrays"));
            foreach ($newRows as $newRow) {
                if ($count >= $fromPage && $count <= $toPage) {
                    $sortRows[] = $newRow;
                }
                $count++;
            }
            return $sortRows;
        }

        if ($table == 'admin.blog_posts_images') {
            // Получим все строки сообщений с учетом параметров
            $options['offset'] = NULL;
            $options['limit'] = NULL;
            $options['sort'] = FALSE;
            $rows = Default_Model_DbTable_BlogPost::GetPosts_Array($this->db, $options);
            foreach ($rows as $row) {
                foreach ($row as $key => $value) {
                    if (is_array($value)) {
                        if ($key == '_images_') {
                            $arrValues = $value;
                            $arrPost = $newRow;
                            foreach ($arrValues as $arrValue) {
                                $newRow = $arrValue + $arrPost + array(
                                    'sortColumn' => $sortColumn,
                                    'ascDescFlg' => $ascDescFlg
                                );
                                $newRows[] = $newRow;
                            }
                        }
                    } else {
                        if (($sortColumn !== 'id' && $key == $sortColumn) || $key == 'title') {
                            $newRow[$key] = $value;
                        }
                    }
                }
            }
            usort($newRows, array("Default_Model_DatabaseObject", "_SortProfiles_Arrays"));
            foreach ($newRows as $newRow) {
                if ($count >= $fromPage && $count <= $toPage) {
                    $sortRows[] = $newRow;
                }
                $count++;
            }
            return $sortRows;
        }

        if ($table == 'admin.blog_posts_audio') {
            // Получим все строки сообщений с учетом параметров
            $options['offset'] = NULL;
            $options['limit'] = NULL;
            $options['sort'] = FALSE;
            $rows = Default_Model_DbTable_BlogPost::GetPosts_Array($this->db, $options);
            foreach ($rows as $row) {
                foreach ($row as $key => $value) {
                    if (is_array($value)) {
                        if ($key == '_audio_') {
                            $arrValues = $value;
                            $arrPost = $newRow;
                            foreach ($arrValues as $arrValue) {
                                $newRow = $arrValue + $arrPost + array(
                                    'sortColumn' => $sortColumn,
                                    'ascDescFlg' => $ascDescFlg
                                );
                                $newRows[] = $newRow;
                            }
                        }
                    } else {
                        if (($sortColumn !== 'id' && $key == $sortColumn) || $key == 'title') {
                            $newRow[$key] = $value;
                        }
                    }
                }
            }
            usort($newRows, array("Default_Model_DatabaseObject", "_SortProfiles_Arrays"));
            foreach ($newRows as $newRow) {
                if ($count >= $fromPage && $count <= $toPage) {
                    $sortRows[] = $newRow;
                }
                $count++;
            }
            return $sortRows;
        }

        if ($table == 'admin.blog_posts_video') {
            // Получим все строки сообщений с учетом параметров
            $options['offset'] = NULL;
            $options['limit'] = NULL;
            $options['sort'] = FALSE;
            $rows = Default_Model_DbTable_BlogPost::GetPosts_Array($this->db, $options);
            foreach ($rows as $row) {
                foreach ($row as $key => $value) {
                    if (is_array($value)) {
                        if ($key == '_video_') {
                            $arrValues = $value;
                            $arrPost = $newRow;
                            foreach ($arrValues as $arrValue) {
                                $newRow = $arrValue + $arrPost + array(
                                    'sortColumn' => $sortColumn,
                                    'ascDescFlg' => $ascDescFlg
                                );
                                $newRows[] = $newRow;
                            }
                        }
                    } else {
                        if (($sortColumn !== 'id' && $key == $sortColumn) || $key == 'title') {
                            $newRow[$key] = $value;
                        }
                    }
                }
            }
            usort($newRows, array("Default_Model_DatabaseObject", "_SortProfiles_Arrays"));
            foreach ($newRows as $newRow) {
                if ($count >= $fromPage && $count <= $toPage) {
                    $sortRows[] = $newRow;
                }
                $count++;
            }
            return $sortRows;
        }

        if ($table == 'admin.blog_posts_locations') {
            // Получим все строки сообщений с учетом параметров
            $options['offset'] = NULL;
            $options['limit'] = NULL;
            $options['sort'] = FALSE;
            $rows = Default_Model_DbTable_BlogPost::GetPosts_Array($this->db, $options);
            foreach ($rows as $row) {
                foreach ($row as $key => $value) {
                    if (is_array($value)) {
                        if ($key == '_locations_') {
                            $arrValues = $value;
                            $arrPost = $newRow;
                            foreach ($arrValues as $arrValue) {
                                $newRow = $arrValue + $arrPost + array(
                                    'sortColumn' => $sortColumn,
                                    'ascDescFlg' => $ascDescFlg
                                );
                                $newRows[] = $newRow;
                            }
                        }
                    } else {
                        if (($sortColumn !== 'id' && $key == $sortColumn) || $key == 'title') {
                            $newRow[$key] = $value;
                        }
                    }
                }
            }
            usort($newRows, array("Default_Model_DatabaseObject", "_SortProfiles_Arrays"));
            foreach ($newRows as $newRow) {
                if ($count >= $fromPage && $count <= $toPage) {
                    $sortRows[] = $newRow;
                }
                $count++;
            }
            return $sortRows;
        }
    }

    /**
     * Получить значения полей в Jason
     *
     * @param array $fields
     *  обьект набора полей таблицы -> fields : {}
     *  который содержит:
     *    - название поля : присоединенную таблицу, к которой относится это поле
     *    -> fieldName : joinTable
     *
     * @return array
     */
    public function getValuesForData($fields) {
        $params = array();
        $jsons = array();
        $newJsons = array();
        //-------------------------
        $_params = $this->_request->getParams();
        foreach ($fields as $field => $joinTable) {

            switch ($field) {
                // Статус сообщения
                case "status":
                    $jsons[$field][] = array('value' => 'D', 'text' => $this->Translate('Черновой'));
                    $jsons[$field][] = array('value' => 'L', 'text' => $this->Translate('Опубликованный'));
                    break;
                default :
                    $params['field'] = $field;
                    $params['type'] = $_params['type'];
                    $params['joinTableForSort'] = $joinTable;
                    $params['order'] = $field . ' ASC';
                    if ($params['type'] == 'search') {
                        $params['group'] = FALSE;
                        if (isset($_params['filter'])) {
                            $strFilter = stripslashes($_params['filter']);
                            $arrFilter = Zend_Json::decode($strFilter);
                            $params['filter'] = $arrFilter;
                        }
                    }
                    $jsons = $this->getValueForData($params, $jsons);
                    // Отформатируем поля, если нужно
                    if ($field == 'ts_created') {
                        foreach ($jsons['ts_created'] as $json) {
                            $json['text'] = $this->dtFormat($json['text'], 'yyyy-MM-dd', 'yyyy-MM-dd HH:mm:ss');
                            $newJsons[] = $json;
                        }
                        $jsons['ts_created'] = $newJsons;
                    }
                    break;
            }
        }
        return $jsons;
    }

    /**
     * Получить значения поля
     *
     * @param array $fields
     *
     * @return array
     */
    public function getValuesForCol($options = NULL) {

        // Обращение идет к таблице "users"  
        if ($options['joinTableForSort'] == 'admin.users') {
            $options['joinTableForSort'] = '';
            $rows = Default_Model_DbTable_User::GetValuesForCol($this->db, $options);
            return $rows;
        }

        // Обращение идет к таблице "users_profile"  
        if ($options['joinTableForSort'] == 'admin.users_profile') {
            $options['joinTableForSort'] = 'users_profile';
            $rows = Default_Model_DbTable_User::GetValuesForCol($this->db, $options);
            return $rows;
        }

        // Обращение идет к таблице "blog_post" или присоединенным таблицам:
        // blog_post_profile; blog_post_tags; blog_post_images; blog_post_locations
        $rows = Default_Model_DbTable_BlogPost::GetValuesForCol($this->db, $options);
        return $rows;
    }

    /**
     * Создать форму для проверки значений таблицы
     *
     * @param array $options
     *
     * @return Default_Form_MyForm
     */
    public function createFormForValidation($options = NULL) {
//        if(!class_exists('Admin_Form_Blogs')){
//            require_once APPLICATION_PATH . '/modules/admin/forms/Blogs.php';
//        }
        return new Admin_Form_Blogs();
    }

    /**
     * Получить отформатированное значение
     *
     * @param string $key
     * @param string $value
     *
     * @return string
     */
    public function getFormattedValue($key, $value) {

        if ($key == 'ts_created') {
            if ($value) {
                $value = $this->dtFormat($value, 'U', 'yyyy-MM-dd');
            } else {
                $value = time();
            }
        }
        if ($key == 'actual') {
            if (!$value) {
                $value = 0;
            }
        }
        return $value;
    }

    /**
     * Получить отформатированные строки значений
     *
     * @param array $rows
     *
     * @return array
     */
    public function getFormattedRows($rows) {
        $formatRows = array();
        $formatRow = array();
        foreach ($rows as $row) {
            foreach ($row as $key => $value) {
                if ($key == 'ts_created') {
                    if ($value) {
                        if (is_int($value)) {
                            $value = $this->dtFormat($value, 'yyyy-MM-dd', 'U');
                        }
                    } else {
                        $value = $this->dtFormat(time(), 'yyyy-MM-dd', 'U');
                    }
                }
                if ($key == 'ts_published') {
                    if ($value) {
                        if (is_int($value)) {
                            $value = $this->dtFormat($value, 'yyyy-MM-dd HH:mm:ss', 'U');
                        }
                    } else {
                        $value = $this->dtFormat(time(), 'yyyy-MM-dd HH:mm:ss', 'U');
                    }
                }
                $formatRow[$key] = $value;
            }
            $formatRows[] = $formatRow;
            $formatRow = array();
        }

        return $formatRows;
    }

    /**
     * Получить отфильтрованные значения
     *
     * @param array $rows
     *
     * @return array
     */
    public function getFilteredRows($rows) {
        $filterRows = array();
        $filterRow = array();
        //-------------------
        // Получим параметры запроса
        $request = $this->getRequest();
        $params = $request->getParams();
        $table = $params['table'];

        if ($table == 'admin.blog_posts') {
            foreach ($rows as $row) {
                // Отфильтруем ненужные поля в записи (масиве)
                foreach ($row as $key => $value) {
                    // Исключим поля
                    switch ($key) {
                        case "content":
                            break;
                        default :
                            $filterRow[$key] = $value;
                            break;
                    }
                }
                if (count($filterRow) > 0) {
                    $filterRows[] = $filterRow;
                }
                $filterRow = array();
            }
        }
        if ($table == 'admin.blog_posts_tags') {
            foreach ($rows as $row) {
                // Отфильтруем ненужные поля в записи (масиве)
                foreach ($row as $key => $value) {
                    // Исключим поля
                    switch ($key) {
                        case "sortColumn":
                        case "ascDescFlg":
                            break;
                        default :
                            $filterRow[$key] = $value;
                            break;
                    }
                }

                if (count($filterRow) > 0) {
                    $filterRows[] = $filterRow;
                }
                $filterRow = array();
            }
        }
        if ($table == 'admin.blog_posts_images') {
            foreach ($rows as $row) {
                // Отфильтруем ненужные поля в записи (масиве)
                foreach ($row as $key => $value) {
                    // Исключим поля
                    switch ($key) {
                        case "sortColumn":
                        case "ascDescFlg":
                            break;
                        default :
                            $filterRow[$key] = $value;
                            break;
                    }
                }
                if (count($filterRow) > 0) {
                    $filterRows[] = $filterRow;
                }
                $filterRow = array();
            }
        }
        if ($table == 'admin.blog_posts_audio') {
            foreach ($rows as $row) {
                // Отфильтруем ненужные поля в записи (масиве)
                foreach ($row as $key => $value) {
                    // Исключим поля
                    switch ($key) {
                        case "sortColumn":
                        case "ascDescFlg":
                            break;
                        default :
                            $filterRow[$key] = $value;
                            break;
                    }
                }
                if (count($filterRow) > 0) {
                    $filterRows[] = $filterRow;
                }
                $filterRow = array();
            }
        }
        if ($table == 'admin.blog_posts_video') {
            foreach ($rows as $row) {
                // Отфильтруем ненужные поля в записи (масиве)
                foreach ($row as $key => $value) {
                    // Исключим поля
                    switch ($key) {
                        case "sortColumn":
                        case "ascDescFlg":
                            break;
                        default :
                            $filterRow[$key] = $value;
                            break;
                    }
                }
                if (count($filterRow) > 0) {
                    $filterRows[] = $filterRow;
                }
                $filterRow = array();
            }
        }
        if ($table == 'admin.blog_posts_locations') {
            foreach ($rows as $row) {
                // Отфильтруем ненужные поля в записи (масиве)
                foreach ($row as $key => $value) {
                    // Исключим поля
                    switch ($key) {
                        case "content":
                        case "correction":
                        case "details":
                        case "sortColumn":
                        case "ascDescFlg":
                            break;
                        default :
                            $filterRow[$key] = $value;
                            break;
                    }
                }
                if (count($filterRow) > 0) {
                    $filterRows[] = $filterRow;
                }
                $filterRow = array();
            }
        }
        return $filterRows;
    }

    /**
     * Проверить строку значений при записи данных
     *
     * @param array $row
     *
     * @return array
     */
    public function validRowForSave($row) {

        $params = $this->_request->getParams();
        $table = $params['table'];
        //-----------------------
        // Проверим строку на валидность
        $jsons = $this->_isValidRow($row);
        if ($jsons['class_message']) { // Ошибка валидации
            return $jsons;
        }

        // Проверка валидации особых случаев
        if ($table == 'admin.blog_posts_tags') {
            $params = array();
            $params['table'] = 'blog_posts_tags';
            $params['fieldKey1'] = 'post_id';
            $params['fieldKey2'] = 'tag';
            $params['adapter'] = $this->db;
            $params['id'] = $row['id'];

            $validator = new Default_Form_Validate_DbMultipleKey($params);
            $value = $row['post_id'] . ';' . $row['tag'];
            if (!$validator->isValid($value)) {
                $messages = $validator->getMessages();
                $newMess = array();
                $newMess[] = '<em>' . Zend_Registry::get('Zend_Translate')->_('Ошибка формы! Неверно введены данные в форму.') . '</em>';
                $messages = array_values($messages);
                $newMess[] = $messages[0];
                $jsons = array(
                    'class_message' => 'warning',
                    'messages' => $newMess
                );
            }
        }

        return $jsons;
    }

}
