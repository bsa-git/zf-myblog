<?php

/**
 * Admin_InfoController
 *
 * Controller - Info
 * view and edit information help records
 * (ex. prompts or information window)
 *
 * @uses       Default_Plugin_TableGrid
 * @package    Module-Admin (administration of site)
 * @subpackage Controllers
 * @author   Sergii Beskorovainyi <bsa2657@yandex.ru>
 * @license  MIT <http://www.opensource.org/licenses/mit-license.php>
 * @link     https://github.com/bsa-git/zf-myblog/
 */
class Admin_InfoController extends Default_Plugin_TableGridController {

    /**
     * Initialization controller
     *
     */
    public function init() {
        parent::init();
    }

    //=============== Loading the template =================//
    /**
     * Action - load
     * Download form of editing
     * 
     * Access to the action is possible in the following paths:
     * - /admin/info/load
     *
     * @return void
     */
    public function loadAction() {
        $json = array();
        //------------------
        try {
            // Создадим обьект шаблона
            $templater = Default_Plugin_SysBox::createViewSmarty();

            //Установим параметры шаблона
            $templater->list_locales = $this->_locales;

            // Получим результат шаблона
            $html = $templater->render('info/load.tpl');
            $json = array(
                'downloaded' => true,
                'html' => $html
            );
            $this->sendJson($json);
        } catch (Exception $exc) {
            $json = array(
                'class_message' => 'warning',
                'messages' => array(
                    '<em>' . $this->Translate('Ошибка при загрузке формы редактирования') . '</em>',
                    $exc->getTraceAsString()
                )
            );
            $this->sendJson($json);
        }
    }

    //=============== Display information help =================//
    /**
     * Action - view
     * display information help in a separate window
     * 
     * Access to the action is possible in the following paths:
     * - /admin/info/view
     *
     * @return void
     */
    public function viewAction() {
        $json = array();
        //------------------
        try {

            $request = $this->getRequest();
            $params = $request->getParams();

            $info_key = $params['info_key'];
            $locale = $params['local'];
            $info = new Default_Model_DbTable_BlogInfo($this->db);
            if ($info->loadForInfo($info_key)) {
                $title = 'title_' . $locale;
                if ($info->profile->$title) {
                    $json['title'] = $info->profile->$title;
                } else {
                    $json['title'] = $this->Translate('Инф. помощь отсутствует...');
                }
                $content = 'content_' . $locale;
                if ($info->profile->$content) {
                    $json['content'] = $info->profile->$content;
                } else {
                    $json['content'] = $this->Translate('Инф. помощь отсутствует...');
                }
            }
            $this->sendJson($json);
        } catch (Exception $exc) {
            $json = array(
                'class_message' => 'warning',
                'messages' => array(
                    '<em>' . $this->Translate('Ошибка при загрузке инф. помощи') . '</em>',
                    $exc->getTraceAsString()
                )
            );
            $this->sendJson($json);
        }
    }

    /**
     * Action - hint
     * display information help in a tooltip
     * 
     * Access to the action is possible in the following paths:
     * - /admin/info/hint
     *
     * @return void
     */
    public function hintAction() {
        $html = '';
        //------------------
        try {

            $request = $this->getRequest();
            $params = $request->getParams();

            $info_key = $params['id'];
            $locale = $params['local'];
            $info = new Default_Model_DbTable_BlogInfo($this->db);
            if ($info->loadForInfo($info_key)) {
                $title = 'title_' . $locale;
                if ($info->profile->$title) {
                    $title = $info->profile->$title;
                } else {
                    $title = $this->Translate('Инф. помощь отсутствует...');
                }
                $content = 'content_' . $locale;
                if ($info->profile->$content) {
                    $content = $info->profile->$content;
                } else {
                    $content = $this->Translate('Инф. помощь отсутствует...');
                }
            }
            $html = "<div class='tooltip_content'><h4>$title</h4>$content</div>";
            $this->sendHtml($html);
        } catch (Exception $exc) {
            $html = '<em>' . $this->Translate('Ошибка при загрузке инф. помощи') . '</em>';
            $this->sendHtml($html);
        }
    }

    //=============== Edit information help =================//
    /**
     * Action - edit
     * edit information help
     * 
     * Access to the action is possible in the following paths:
     * - /admin/info/edit
     *
     * @return void
     */
    public function editAction() {
        $json = array();
        //----------------
        $request = $this->getRequest();
        $params = $request->getParams();

        $action = $params['my_action'];
        $info_key = $params['info_key'];

        $info = new Default_Model_DbTable_BlogInfo($this->db);
        if ($info->loadForInfo($info_key)) {
            $json['info_key'] = $info_key;
            switch ($action) {
                case 'load': // Загрузить значения по инф. помощи
                    $json['title'] = $info->title_info;
                    foreach ($this->_locales as $locale) {
                        $title = 'title_' . $locale;
                        if ($info->profile->$title) {
                            $json['info_values'][$title] = $info->profile->$title;
                        } else {
                            $json['info_values'][$title] = $this->Translate('Введите название инф. помощи...');
                        }
                        $content = 'content_' . $locale;
                        if ($info->profile->$content) {
                            $json['info_values'][$content] = $info->profile->$content;
                        } else {
                            $json['info_values'][$content] = $this->Translate('Содержание инф. помощи...');
                        }
                    }
                    $json['result'] = $this->Translate('Инф. помощь успешно загружена');
                    break;
                case 'title': // Сохранить название инф. помощи для конкретного языка

                    $title_value = $params['title'];
                    $locale = $params['local'];
                    $title = 'title_' . $locale;
                    $info->profile->$title = $title_value;

                    if ($info->save()) {
                        $json['result'] = $this->Translate('Данные успешно сохранены');
                        $json['title'] = $title_value;
                    } else {
                        $json = array(
                            'class_message' => 'warning',
                            'messages' => array(
                                '<em>' . $this->Translate('Ошибка при сохранении данных') . '</em>'
                            )
                        );
                    }
                    break;
                case 'content': // Сохранить содержание инф. помощи для конкретного языка

                    $content_value = $params['content'];
                    // Удалим из текста код '&nbsp;' (код непереносимого пробела)
                    // для праивльного отображения в подсказках
                    $content_value = str_replace('&nbsp;', ' ', $content_value);
                    // Удалим из текста код 'class="section"' 
//                    $content_value = str_replace('&nbsp;', ' ', $content_value);

                    $locale = $params['local'];
                    $content = 'content_' . $locale;
                    $info->profile->$content = $content_value;

                    if ($info->save()) {
                        $json['result'] = $this->Translate('Данные успешно сохранены');
                        $json['content'] = $content_value;
                    } else {
                        $json = array(
                            'class_message' => 'warning',
                            'messages' => array(
                                '<em>' . $this->Translate('Ошибка при сохранении данных') . '</em>'
                            )
                        );
                    }
                    break;
            }
            $this->sendJson($json);
            return;
        } else {
            $json = array(
                'class_message' => 'warning',
                'messages' => array(
                    '<em>' . $this->Translate('Ошибка при редактировании инф. помощи') . '</em>'
                )
            );
            $this->sendJson($json);
            return;
        }
    }

    //=============== REPORT =================//

    /**
     * Action - report
     * create report
     * 
     * Access to the action is possible in the following paths:
     * - /admin/info/report
     *
     * @return void
     */
    public function reportAction() {
        parent::reportAction();
    }

    /**
     * 
     * Get report data
     * 
     * @param string $table
     *
     * @return array
     */
    protected function _getReportData($table) {
        $arrResultData = array();
        $rows_footer = array();
        $footer_colspan = 2;
        //-------------------
        // Получим данные для отчета
        $arrData = parent::_getReportData($table);

        // Установим параметры PDF по умолчанию
        $arrResultData['pdf']['logo_report'] = Default_Plugin_SysBox::getFullURL_Res('/images/system/notice-info24x24.png');
        $arrResultData['pdf']['title_report'] = $this->Translate('Список инф. помощи');
        $arrResultData['pdf']['is_row_header'] = TRUE;
        $arrResultData['pdf']['pageFormat'] = 'A4';

        // Установим параметры для HTML по умолчанию
        $arrResultData['html']['column_model'] = $arrData['column_model'];
        $arrResultData['html']['is_group_head'] = $arrData['is_group_head'];
        $arrResultData['html']['rows_body'] = isset($arrData['rows']) ? $arrData['rows'] : array();
        $arrResultData['html']['is_row_header'] = true;
        $arrResultData['html']['footer_colspan'] = $footer_colspan;

        // Получим данные для отчета
        switch ($table) {
            case 'admin.blog_info':

                // Исключим в отчете некоторые поля
                $fieldsExcluded = array('actual');
                $newModelColumns = parent::_excludeFieldsFromReport($fieldsExcluded, $arrData['column_model']);
                $arrResultData['html']['column_model'] = $newModelColumns;

                // Получим массив записей для нижнего колонтитула
                $footers[] = array(
                    'id' => $this->Translate('Всего записей') . ':',
                    'info_key' => 'count',
                    'title_info' => ' ',
                );


                $rows_footer = parent::_footerForReport(array(
                            'footer_colspan' => $footer_colspan,
                            'rows' => $arrData['rows'],
                            'column_model' => $newModelColumns,
                            'footers' => $footers
                ));

                // Установим параметры для HTML
                $arrResultData['html']['logo_report'] = Default_Plugin_SysBox::getFullURL_Res('/images/system/notice-info24x24.png');
                $arrResultData['html']['title_report'] = $this->Translate('Список инф. помощи');
                $arrResultData['html']['rows_footer'] = $rows_footer;
                $arrResultData['html']['is_row_header'] = TRUE;
                $arrResultData['html']['footer_colspan'] = 1;
                // Установим параметры PDF
                $arrResultData['pdf']['pageFormat'] = 'A4';
                $arrResultData['pdf']['is_row_header'] = false;
                break;
            default:
                break;
        }
        return $arrResultData;
    }

    //=============== Working with Table =================//

    /**
     * Action - rows
     * get all data in accordance with the request parameters
     *
     * Access to the action is possible in the following paths:
     * - /admin/user/rows
     *
     * @return void
     */
    public function rowsAction() {
        parent::rowsAction();
    }

    /**
     * Action - data
     * get data on certain fields of the table. 
     * This data placed in the ComboBox, to edit these table fields
     *
     * Access to the action is possible in the following paths:
     * - /admin/user/data
     *
     * @return void
     */
    public function dataAction() {
        parent::dataAction();
    }

    /**
     * Action - validate
     * check the appropriate value. Parameters values are passed in the query
     *
     * Access to the action is possible in the following paths:
     * - /admin/user/validate
     *
     * @return void
     */
    public function validateAction() {
        parent::validateAction();
    }

    /**
     * Action - save
     * save the modified data or add new data
     * 
     * Access to the action is possible in the following paths:
     * - /admin/user/save
     *
     * @return void
     */
    public function saveAction() {
        parent::saveAction();
    }

    /**
     * Action - delete
     * delete data from the database
     *
     * Access to the action is possible in the following paths:
     * - /admin/user/delete
     *
     * @return void
     */
    public function deleteAction() {
        parent::deleteAction();
    }

    /**
     * Update/Insert table row
     *
     * @param array $row
     *
     * @return bool
     */
    public function saveRowTable($row) {

        $isSaved = $row->isSaved();
        $result = parent::saveRowTable($row);

        // Сохраним событие в лог
        if ($result) {
            $username = $this->_identity->username;
            if ($isSaved) {
                $message = "User - \"$username\" updated a row in the table-\"blog_info\" with title_info=\"$row->title_info\"";
                $this->_logMsg->admin_row_update($message);
            } else {
                $message = "User - \"$username\" inserted a row into a table-\"blog_info\" with title_info=\"$row->title_info\"";
                $this->_logMsg->admin_row_insert($message);
            }
        }
        return $result;
    }

    /**
     * Delete table row
     *
     * @param array $row
     *
     * @return bool
     */
    public function deleteRowTable($row) {

        $result = parent::deleteRowTable($row);
        // Сохраним событие в лог
        if ($result) {
            $username = $this->_identity->username;
            $message = "User - \"$username\" deleted a row from table-\"blog_info\" with title_info=\"$row->title_info\"";
            $this->_logMsg->admin_row_delete($message);
        }
        return $result;
    }

    /**
     * Action - search
     * search the row in the table and return the number of row
     *
     * Access to the action is possible in the following paths:
     * - /admin/user/search
     *
     * @return void
     */
    public function searchAction() {
        parent::searchAction();
    }

    /**
     * Get the number of rows in the table
     *
     * @param array $options
     *
     * @return int
     */
    public function getCountRowsTable($options = NULL) {
        return Default_Model_DbTable_BlogInfo::GetInfoCount($this->db, $options);
    }

    /**
     * Create a table record object
     *
     * @param array $options
     *
     * @return object
     */
    public function createRowTable($options = NULL) {
        return new Default_Model_DbTable_BlogInfo($this->db);
    }

    /**
     * Get a table records
     *
     * @param array $options
     *
     * @return array
     */
    public function getRowsArraysTable($options = NULL) {
        return Default_Model_DbTable_BlogInfo::GetInfos_Array($this->db, $options);
    }

    /**
     * Get the values of the fields in Json format
     *
     * @param array $fields
     *
     * @return array
     */
    public function getValuesForData($fields) {
        $params = array();
        $jsons = array();
        //----------------------------
        $_params = $this->_request->getParams();
        foreach ($fields as $field => $joinTable) {
            switch ($field) {
                // Поле, для получения данных
                case "":
                    break;
                default :
                    $params['field'] = $field;
                    $params['type'] = $_params['type'];
                    $params['joinTableForSort'] = $joinTable;
                    $params['order'] = $field . ' ASC';
                    if ($params['type'] == 'search') {
                        $params['group'] = FALSE;
                        if ($_params['filter']) {
                            $strFilter = stripslashes($_params['filter']);
                            $arrFilter = Zend_Json::decode($strFilter);
                            $params['filter'] = $arrFilter;
                        }
                    }
                    $jsons = $this->getValueForData($params, $jsons);
                    break;
            }
        }
        return $jsons;
    }

    /**
     * Get the values of a field in a table
     *
     * @param array $fields
     *
     * @return array
     */
    public function getValuesForCol($options = NULL) {
        $rows = Default_Model_DbTable_BlogInfo::GetValuesForCol($this->db, $options);
        return $rows;
    }

    /**
     * Create a form to validation the values
     *
     * @param array $options
     *
     * @return Default_Form_MyForm
     */
    public function createFormForValidation($options = NULL) {
        return new Admin_Form_Info();
    }

    /**
     * Valid the values of the row when save
     *
     * @param array $row
     *
     * @return array
     */
    public function validRowForSave($row) {
        $jsons = array();
        $newRow = array();
        //------------------------------
        $rowTable = $this->createRowTable();
        if ($row['id']) {
            $rowTable->load($row['id']);
        }

        // Проверим редактируем существующую или втавляем новую запись
        if ($row['id']) { // редактируем существующую запись
            // Удалим из строки поле "info_key"
            foreach ($row as $key => $value) {
                $newRow[$key] = $value;
            }
            // Проверим строку на валидность
            $jsons = $this->_isValidRow($newRow);
        } else { // втавляем новую запись
            // Удалим из строки поле "info_key"
            foreach ($row as $key => $value) {
                if ($key !== 'info_key') {
                    $newRow[$key] = $value;
                }
            }
            // Проверим строку на валидность
            $jsons = $this->_isValidRow($newRow);
        }
        return $jsons;
    }

    /**
     * Get formatted values
     *
     * @param string $key
     * @param string $value
     *
     * @return string
     */
    public function getFormattedValue($key, $value) {

        return $value;
    }

    /**
     * Get formatted row values
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
                $formatRow[$key] = $value;
            }
            $formatRows[] = $formatRow;
            $formatRow = array();
        }

        return $formatRows;
    }

}
