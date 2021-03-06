<?php

/**
 * Default_Plugin_TableGridController
 *
 * Plugin - It is used when working with tables MY.TableGrid
 *
 * @uses       Default_Plugin_BaseController
 * @package    Module-Default
 * @subpackage Plugins
 * @author   Sergii Beskorovainyi <bsa2657@yandex.ru>
 * @license  MIT <http://www.opensource.org/licenses/mit-license.php>
 * @link     https://github.com/bsa-git/zf-myblog/
 */
class Default_Plugin_TableGridController extends Default_Plugin_BaseController {

    /**
     * table - table name
     *
     * @var string
     */
    protected $_table = '';

    /**
     * Initialization controller
     *
     */
    public function init() {
        parent::init();

        // Получим название таблицы
        $params = $this->_request->getParams();
        if (isset($params['table'])) {
            $table = $params['table'];
            $arrTable = explode('.', $table);
            $this->_table = $arrTable[1];
        }
    }

    /**
     * Action rows (retrieve data) 
     * for this action happens withdrawal of all data 
     * according to the request parameters
     *
     *
     * @return void
     */
    public function rowsAction() {
        $options = array();
        //---------------------
        if ($this->_isAjaxRequest) {
            $jsons = array();
            try {
                //Получим кол. записей
                $request = $this->getRequest();
                $params = $request->getParams();
                if (isset($params['filter'])) {
                    $strFilter = stripslashes($params['filter']);
                    $arrFilter = Zend_Json::decode($strFilter);
                    $options['filter'] = $arrFilter;
                } else {
                    $options = array();
                }


                // Получим опции для запроса
                $count = $this->getCountRowsTable($options);

                if ($count) {

                    // Получим опции для запроса
                    $options = $this->getParamsPaginator($count);

                    // Получим строки
                    $rows = $this->getRowsArraysTable($options);

                    // Отфильтруем строки
                    $rows = $this->getFilteredRows($rows);

                    // Отформатируем строки
                    $rows = $this->getFormattedRows($rows);

                    // Получим массив для передачи клиенту
                    $jsons['rows'] = array_values($rows);

                    // Установим опции для полученной страницы
                    $currentPage = (int) $options['currentPage'];
                    $total = (int) $options['total'];
                    $from = (int) $options['fromPage'];
                    $to = (int) $options['toPage'];
                    $pages = (int) $options['pages'];

                    $jsons['options'] = array(
                        'pager' => array(
                            'currentPage' => $currentPage,
                            'total' => $total,
                            'from' => $from,
                            'to' => $to,
                            'pages' => $pages
                        )
                    );
                } else {

                    $jsons['rows'] = array();
                }

                $this->sendJson($jsons);
            } catch (Exception $exc) {
                $jsons = array(
                    'class_message' => 'warning',
                    'messages' => array(
                        '<em>' . $this->Translate('Ошибка получения строк таблицы') . '</em>',
                        Default_Plugin_SysBox::getMessageError($exc)
                    )
                );
                $this->sendJson($jsons);
                return;
            }
        }
    }

    /**
     * Action data (data) 
     * for this action is requested for specific fields of the table data. 
     * These are then placed in the ComboBox 
     * for easy editing of these fields of the table
     *
     *
     * @return void
     */
    public function dataAction() {
        if ($this->_isAjaxRequest) {
            try {

                // Получим данные
                $params = $this->_request->getParams();
                if (isset($params['fields'])) {
                    $strFields = stripslashes($params['fields']);
                    $arrFields = Zend_Json::decode($strFields);
                    $count = count($arrFields);
                } else {
                    $count = 0;
                }

                if ($count == 0) {
                    $jsons = array(
                        'class_message' => 'warning',
                        'messages' => array(
                            '<em>' . $this->Translate('Ошибка получения данных по URL') . '</em>',
                            $this->Translate('Ошибка обращения к сайту! Неверно заданы параметры.')
                        )
                    );
                    $this->sendJson($jsons);
                    return;
                }

                // Получим значения полей в Json
                $jsons = $this->getValuesForData($arrFields);

                // Определим способ получения и передачи данных
                // в зависимости от типа запроса на получение данных
                if ($params['type'] == 'table') {
                    $this->sendJson($jsons);
                } elseif ($params['type'] == 'filter') {
                    $fields = array_keys($arrFields);
                    $field = $fields[0];
                    $jsons = $jsons[$field];
                    $this->sendJson($jsons);
                } elseif ($params['type'] == 'search') {
                    $fields = array_keys($arrFields);
                    if (count($fields) == 2) {
                        $field1 = $fields[0];
                        $field2 = $fields[1];
                        // Отсортируем массивы по id
                        $arrField1 = $jsons[$field1];
                        $arrField2 = $jsons[$field2];
                        usort($arrField1, array("Default_Model_DatabaseObject", "_SortArrays2"));
                        usort($arrField2, array("Default_Model_DatabaseObject", "_SortArrays2"));

                        // Обьединим массивы
                        $newJsons = array_map("self::joinArraysForSearch", $arrField1, $arrField2);

                        // Отсортируем массив по text
                        usort($newJsons, array("Default_Model_DatabaseObject", "_SortArrays"));
                    } else {
                        $field = $fields[0];
                        if (count($jsons) > 0) {
                            $newJsons = $jsons[$field];
                        } else {
                            $newJsons = '';
                        }
                    }
                    $this->sendJson($newJsons);
                }
            } catch (Exception $exc) {
                $jsons = array(
                    'class_message' => 'warning',
                    'messages' => array(
                        '<em>' . $this->Translate('Ошибка получения данных по URL') . '</em>',
                        Default_Plugin_SysBox::getMessageError($exc)
                    )
                );
                $this->sendJson($jsons);
                return;
            }
        }
    }

    /**
     * Get the value of a field in Json format
     *
     * @param atring $field
     * @param array $jsons
     *
     * @return array
     */
    public function getValueForData($params, $jsons) {
        $field = $params['field'];
        //--------------------------
        // Получить отсортированные и сгруппированные значения из колонки таблицы
        $rows = $this->getValuesForCol($params);
        foreach ($rows as $key => $value) {
            $type = $params['type'];
            switch ($type) {
                case "table":
                case "search":
                    $jsons[$field][] = array('value' => $key, 'text' => $value);
                    break;
                default :
                    $jsons[$field][] = array('value' => $value, 'text' => $value);
                    break;
            }
        }
        return $jsons;
    }

    /**
     * Action validate (check values) 
     * for this action is checked the corresponding value 
     * parameters values are passed to the query parameters
     *
     *
     * @return void
     */
    public function validateAction() {
        if ($this->_isAjaxRequest) {
            try {
                // Получим данные
                $params = $this->_request->getParams();
                $table = isset($params['table']) ? $params['table'] : '';
                $field = isset($params['field']) ? $params['field'] : '';
                $value = isset($params['value']) ? $params['value'] : '';
                $jsons = $this->_isValid($params);

                // Передадим данные клиенту
                $this->sendJson($jsons);
            } catch (Exception $exc) {
                $jsons = array(
                    'class_message' => 'warning',
                    'messages' => array(
                        '<em>' . $this->Translate('Ошибка валидации значения') . '</em>',
                        Default_Plugin_SysBox::getMessageError($exc)
                    )
                );
                $this->sendJson($jsons);
                return;
            }
        }
    }

    /**
     * Check value
     *
     *
     * @param array $params     // Parameters checked value
     * @return array            
     */
    public function _isValid($params) {
        $jsons = array();
        $table = isset($params['table'])?$params['table']:'';
        $field = isset($params['field'])?$params['field']:'';
        $value = isset($params['value'])?$params['value']:'';

        // Создадим форму валидации значений
        $formValid = $this->createFormForValidation();
        $result = $formValid->isValid(array($field => $value));
        if (!$result) { // Ошибка значения
            $message = $this->getFormMessages($formValid);
            $jsons = array(
                'class_message' => 'warning',
                'messages' => $message
            );
        } else { // ОК
            if ($formValid->getValue($field)) {
                $jsons = array('value' => $formValid->getValue($field));
            } else {
                $jsons = array('value' => $value);
            }
        }
        return $jsons;
    }

    /**
     * Проверка значений строки в таблице
     * Checking the row values in the table
     *
     *
     * @param array $row        // Row with values
     * @return array            
     */
    public function _isValidRow($row) {
        $validRow = array();
        //--------------------
        // Создадим форму валидации значений
        $formValid = $this->createFormForValidation();

        $result = $formValid->isValid($row);
        if (!$result) { // Ошибка значения
            $message = $this->getFormMessages($formValid);

            $jsons = array(
                'class_message' => 'warning',
                'messages' => $message
            );
        } else { // ОК
            foreach ($row as $key => $value) {
                if ($formValid->getValue($key)) {
                    $value = $formValid->getValue($key);
                }
                $validRow[$key] = $value;
            }
        }
        $jsons = array(
            'row' => $validRow
        );
        return $jsons;
    }

    /**
     *
     * Action save (data storage) by this action, the changes of data or add data
     *
     *
     * @return void
     */
    public function saveAction() {
        if ($this->_isAjaxRequest) {
            $jsons = array();
            try {
                // Получим данные
                $params = $this->_request->getParams();
                $strRows = stripslashes($params['rows']);
                $arrRows = Zend_Json::decode($strRows);

                //--- Проверим полученные строки ---
                foreach ($arrRows as $row) {

                    // Проверим значения при сохранении данных
                    $jsons = $this->validRowForSave($row);

                    // Выведем ошибку значения или запомним
                    // проверенное и отфильтрованное значение
                    if (isset($jsons['class_message'])) { // Ошибка значения
                        $jsons['messages'][0] = str_replace('.', '', $jsons['messages'][0]);
                        $jsons['messages'][0] .= $this->Translate(' ( для строки id=%s )', $row['id']);
                        // Передадим данные клиенту
                        $this->sendJson($jsons);
                        return;
                    }

                    // Запомним значения
                    $rowTable = $this->createRowTable();
                    $table_fields = $rowTable->getSelectFields();
                    if (isset($row['id'])) {
                        $rowTable->load($row['id']);
                    }
                    $saveRow = array();
                    foreach ($row as $key => $value) {

                        // Получим отформатируемое значение
                        $value = $this->getFormattedValue($key, $value);
                        if (in_array($key, $table_fields)) {

                            // Сохраним в поле строки значение
                            // если они не равны друг другу
                            $rowValue = $rowTable->$key;
                            if ($rowValue != $value) {
                                $rowTable->$key = $value;
                                $saveRow[$key] = $value;
                            }
                        } else {
                            if ($key !== '_nn_' && $rowTable->profile) {
                                $rowValue = $rowTable->profile->$key;
                                if ($rowValue != $value) {
                                    $rowTable->profile->$key = $value;
                                    $saveRow[$key] = $value;
                                }
                            }
                        }
                    }
                    //Запомним данные пользователя
                    $result = $this->saveRowTable($rowTable);
                    if (!$result) {
                        $strRow = '';
                        foreach ($saveRow as $key => $value) {
                            $strRow .= $key . '=' . $value . '; ';
                        }
                        throw new Exception('SaveRow -> ' . $strRow);
                    }
                }

                // Передадим данные клиенту
                $this->sendJson(array('result' => $this->Translate('Данные успешно сохранены')));
            } catch (Exception $exc) {
                $jsons = array(
                    'class_message' => 'warning',
                    'messages' => array(
                        '<em>' . $this->Translate('Ошибка при сохранении данных') . '</em>',
                        Default_Plugin_SysBox::getMessageError($exc)
                    )
                );
                $this->sendJson($jsons);
                return;
            }
        }
    }

    /**
     * Action delete (delete data) 
     * on the action of the data is removed from the database
     *
     * Access to the action is possible in the following paths:
     * - /admin/user/delete
     *
     * @return void
     */
    public function deleteAction() {
        if ($this->_isAjaxRequest) {
            $jsons = array();
            try {
                // Получим данные
                $params = $this->_request->getParams();
                $table = $params['table'];
                $strRows = stripslashes($params['rows']);
                $arrIds = Zend_Json::decode($strRows);

                //--- Проверим каждое значение ---
                foreach ($arrIds as $id) {
                    //Создадим обьект строки таблицы
                    $rowTable = $this->createRowTable();
                    $result = $this->loadRowTable($rowTable, $id);
                    if ($result) {// OK
                        $result = $this->deleteRowTable($rowTable);
                        if ($result) {// OK
                            $jsons = array(
                                'result' => $this->Translate('Данные успешно удалены'),
                            );
                        } else {// ERR
                            $jsons = array(
                                'class_message' => 'warning',
                                'messages' => array(
                                    '<em>' . $this->Translate('Ошибка при удалении данных') . '</em>',
                            ));
                        }
                    } else {// ERR
                        $jsons = array(
                            'class_message' => 'warning',
                            'messages' => array(
                                '<em>' . $this->Translate('Ошибка при удалении данных') . '</em>',
                        ));
                    }
                    if (!$result) {
                        break;
                    }
                }
                $this->sendJson($jsons);
            } catch (Exception $exc) {// ERROR
                $jsons = array(
                    'class_message' => 'warning',
                    'messages' => array(
                        '<em>' . $this->Translate('Ошибка при удалении данных') . '</em>',
                        Default_Plugin_SysBox::getMessageError($exc)
                    )
                );
                $this->sendJson($jsons);
                return;
            }
        }
    }

    /**
     * Load the data table record to object $row
     *
     * @param array $row
     * @param int $id
     *
     * @return bool
     */
    public function loadRowTable($row, $id) {
        $result = $row->load($id);
        return $result;
    }

    /**
     * Update/Insert row table
     *
     * @param array $row
     *
     * @return bool
     */
    public function saveRowTable($row) {
        $result = $row->save();
        return $result;
    }

    /**
     * Delete row table object
     *
     * @param array $row
     *
     * @return bool
     */
    public function deleteRowTable($row) {
        $result = $row->delete();
        return $result;
    }

    /**
     * Action search (search for values in the table) 
     * by this action takes place in the search row table and 
     * returns the number of the search row, 
     * if the search occurred successfully
     *
     * Access to the action is possible in the following paths:
     * - /admin/user/search
     *
     * @return void
     */
    public function searchAction() {
        if ($this->_isAjaxRequest) {
            $jsons = array();
            try {
                //Получим кол. записей пользователей
                //Получим кол. записей
                $request = $this->getRequest();
                $params = $request->getParams();
                $value_id = $params['value_id'];

                // Выполним поиск записи
                $jsons = $this->_getSearchResult($value_id);
                if (count($jsons) == 0) {
                    $jsons = array(
                        'class_message' => 'information',
                        'messages' => array(
                            $this->Translate("Значение <em>'%s'</em> не найдено", $params['value']) . '!'
                        )
                    );
                }
                $this->sendJson($jsons);
            } catch (Exception $exc) {// ERROR
                $jsons = array(
                    'class_message' => 'warning',
                    'messages' => array(
                        '<em>' . $this->Translate('Ошибка поиска данных') . '</em>',
                        Default_Plugin_SysBox::getMessageError($exc)
                    )
                );
                $this->sendJson($jsons);
                return;
            }
        }
    }

    /**
     * Get search result
     *
     * @param int $id
     *
     * @return array
     */
    private function _getSearchResult($id) {
        $jsons = array();
        $page = 0;
        //----------------------
        // Получим параметры запроса
        $params = $this->_request->getParams();
        if (isset($params['filter'])) {
            $strFilter = stripslashes($params['filter']);
            $arrFilter = Zend_Json::decode($strFilter);
            $options['filter'] = $arrFilter;
        }
        $options['joinTableForSort'] = $params['joinTableForSort'];
        $sortColumn = ($params['sortColumn'] == '_nn_') ? '' : $params['sortColumn'];
        if ($sortColumn) {
            $options['order'] = $sortColumn . ' ' . $params['ascDescFlg'];
        }
        $options['group'] = false;

        $options['field'] = 'id';

        $options['joinTableForSort'] = $params['joinTableForSort'];

        // Получим отсортированные записи
        $rows = $this->getValuesForCol($options);
        if (count($rows) == 0) {
            return $jsons;
        }
        $ids = array_keys($rows);
        $count = array_search($id, $ids) + 1;


        // Найдем страницу, где находиться наша
        // найденная запись
        $rowsByPage = $params['rowsByPage'];
        $page = floor($count / $rowsByPage);
        if (($count % $rowsByPage) > 0) {
            $page++;
        }

        // Получим массив для ответа
        $jsons['id'] = $id;
        $jsons['page'] = $page;
        $jsons['result'] = $this->Translate('Значения найдены');

        return $jsons;
    }

    /**
     * Compare the two values
     *
     * @param string $compare
     * @param string $value
     * @param string $cValue
     *
     * @return bool
     */
    public function doCompareValue($compare, $value, $cValue) {
        $result = TRUE;
        //------------------
        switch ($compare) {
            case "=":
                $result = ($value == $cValue);
                break;
            case "<>":
                $result = ($value !== $cValue);
                break;
            case ">":
                $result = ($value > $cValue);
                break;
            case ">=":
                $result = ($value >= $cValue);
                break;
            case "<":
                $result = ($value < $cValue);
                break;
            case "<=":
                $result = ($value >= $cValue);
                break;
            default :
                $result = FALSE;
                break;
        }
        return $result;
    }

    //=============== REPORT =================//

    /**
     * Action - report
     * display table data in a report in HTML or PDF format
     * 
     * Access to the action is possible in the following paths:
     * - /admin/user/report
     *
     * @return void
     */
    public function reportAction() {
        try {
            $params = $this->getRequest()->getParams();

            // Тип отчета
            $report_type = $params['type'];
            // Таблица для отчета
            $table = $params['table'];

            // Получим данные по отчету
            $reportData = $this->_getReportData($table);

            // Получим данные для отчета
            switch ($report_type) {
                case 'html':
                    $templater = Default_Plugin_SysBox::createViewSmarty();
                    // Отображается в режиме отчета
                    $templater->report = TRUE;

                    //------ Установим параметры и переменные HTML ------
                    // ---- Титл -----
                    $templater->title_name = $reportData['html']['title_report'];
                    $templater->title_logo = $reportData['html']['logo_report'];

                    // ---- Заголовок -----
                    $templater->column_model = $reportData['html']['column_model'];
                    $templater->is_group_head = isset($reportData['html']['is_group_head']) ? $reportData['html']['is_group_head'] : 0;

                    // ---- Нижний колонтитул -----
                    $templater->rows_footer = $reportData['html']['rows_footer'];
                    $templater->footer_colspan = $reportData['html']['footer_colspan'];

                    // ---- Тело таблицы -----
                    $templater->rows_body = $reportData['html']['rows_body'];
                    $templater->is_row_header = isset($reportData['html']['is_row_header']) ? $reportData['html']['is_row_header'] : 0;

                    // Получим результат шаблона
                    $html = $templater->render('reports/report-table.tpl');

                    $this->sendJson(array(
                        'result' => $this->Translate('Создан отчет в формате HTML'),
                        'html' => $html));
                    break;
                case 'pdf':

                    // Проверим наличие файла mpdf.php
                    // Если нет, то выдадим ошибку!
                    $path = APPLICATION_BASE . '/library/mPDF/mpdf.php';
                    if (!is_file($path)) {
                        throw new Exception($this->Translate('Не установлена библиотека mPDF', '/library/mPDF', 'http://www.mpdf1.com/mpdf/index.php?page=Download'));
                    }


                    // Создадим обьект шаблона
                    if ($this->_isAjaxRequest) {
                        $templater = Default_Plugin_SysBox::createViewSmarty();
                    } else {
                        $templater = $this->view;
                    }

                    //------ Установим параметры и переменные HTML ------
                    // ---- Титл -----
                    $templater->title_name = $reportData['html']['title_report'];
                    $templater->title_logo = $reportData['html']['logo_report'];

                    // ---- Заголовок -----
                    $templater->column_model = $reportData['html']['column_model'];
                    $templater->is_group_head = isset($reportData['html']['is_group_head']) ? $reportData['html']['is_group_head'] : 0;

                    // ---- Нижний колонтитул -----
                    $templater->rows_footer = $reportData['html']['rows_footer'];
                    $templater->footer_colspan = $reportData['html']['footer_colspan'];

                    // ---- Тело таблицы -----
                    $templater->rows_body = $reportData['html']['rows_body'];
                    $templater->is_row_header = isset($reportData['pdf']['is_row_header']) ? $reportData['pdf']['is_row_header'] : 0;

                    // Получим результат шаблона
                    $html = $templater->render('reports/table.tpl');

                    // Установим имя отчета PDF
                    // в названии файла будет присутствовать хеш полученного HTML
                    // это нужно для того, чтобы не создавать существующих файлов
                    $md5Html = md5($html);
                    $report = $table . '_' . $md5Html;

                    // Установим параметры для отчета PDF
                    $pdfParams['pdfReport'] = $report;
                    $pdfParams['html'] = $html;
                    $pdfParams['isCommonFont'] = FALSE;
                    $pdfParams['pathStylesheet'] = 'css/report/blue-style.css'; //phpinfo blue-style
                    $pdfParams['headerLeftMargin'] = $reportData['pdf']['title_report'];
                    $pdfParams['headerCentreMargin'] = $reportData['pdf']['logo_report'];
                    $pdfParams['pageFormat'] = $reportData['pdf']['pageFormat'];

                    ob_start();

                    // Получим имя файла и проверим его наличие
                    $filename = Default_Plugin_SysBox::getPath_For_FilePDF($report);

                    if (file_exists($filename)) {// Файл уже существует
                        sleep(1);

                        // Получить URL PDF файла
                        $urlFilePDF = Default_Plugin_SysBox::getFullUrl_For_FilePDF($report);

                        $this->sendJson(array(
                            'result' => $this->Translate('Этот отчет уже существует'),
                            'url_file_pdf' => $urlFilePDF));
                    } else {// Создадим отчет...
                        // Удалим ранее созданные отчеты
                        // Получим директорию с файлами отчетов
                        $patch_dir = Default_Plugin_SysBox::getPath_For_FilePDF('');
                        // Получим обьект построения дерева файлов
                        $ft = new Default_Plugin_FileTree($patch_dir);
                        // создадим дерево файлов
                        $report_del = $table . '_*.pdf';
                        $ft->readTree(array('name' => $report_del));
                        // удалим файлы и директории
                        $result = $ft->delFiles();

                        // Создать PDF файл из HTML
                        $urlFilePDF = Default_Plugin_SysBox::mpdfGenerator_Html2PDF($pdfParams);

                        $this->sendJson(array(
                            'result' => $this->Translate('Создан отчет в формате PDF'),
                            'url_file_pdf' => $urlFilePDF));
                    }
                    break;
                case 'exel':
                    break;
                default:
                    break;
            }
        } catch (Exception $exc) {
            $jsons = array(
                'class_message' => 'warning',
                'messages' => array(
                    '<em>' . $this->Translate('Ошибка формирования отчета') . '</em>',
                    Default_Plugin_SysBox::getMessageError($exc)
                )
            );
            $this->sendJson($jsons);
        }
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
        $options = array();
        $arrData = array();
        $arrColumnModel = array();
        $arrItemsComboBox = array();
        //---------------------
        try {
            //Получим параметры запроса
            $request = $this->getRequest();
            $params = $request->getParams();
            // Подготовим данные по фильтру
            if (isset($params['filter'])) {
                $strFilter = stripslashes($params['filter']);
                $arrFilter = Zend_Json::decode($strFilter);
                $options['filter'] = $arrFilter;
            }
            // Подготовим данные по структуре полей таблицы
            if (isset($params['columnModel'])) {
                $arrData['is_group_head'] = 0;
                $columnModel = stripslashes($params['columnModel']);
                $arrColumnModel = Zend_Json::decode($columnModel);
                foreach ($arrColumnModel as $column) {
                    $id = $column['id'];
                    $arrData['column_model'][$id] = $column;
                    if ($column['count_childrens'] > 0) {
                        $arrData['is_group_head'] = 1;
                    }
                }
            }
            // Подготовим данные по значениям полей таблицы 
            if (isset($params['itemsComboBox'])) {
                $arrItemsComboBox = Zend_Json::decode($params['itemsComboBox']);
            }

            // Получим опции для запроса
            $count = $this->getCountRowsTable($options);

            if ($count) {

                // Получим опции для запроса
                $options = $this->getParamsPaginator($count);

                // Получим строки
                $rows = $this->getRowsArraysTable($options);

                // Отфильтруем строки
                $rows = $this->getFilteredRows($rows);

                // Отформатируем строки
                $rows = $this->getFormattedRows($rows);

                // Получим массив для передачи клиенту
                $rows = array_values($rows);

                // Получим текстовые значения вместо индексов
                // Преобразуем данные к нужному типу
                foreach ($rows as $row) {// строка
                    $newRow = array(); // новая строка
                    foreach ($row as $key => $value) {// ключ / значение в строке
                        if (isset($arrItemsComboBox[$key]) && is_array($arrItemsComboBox[$key])) { // если есть чем заменить индексное значение?
                            $arrItemsForKey = $arrItemsComboBox[$key]; // массив: value="индексное значение" text="текстовое значение"
                            foreach ($arrItemsForKey as $itemForKey) {
                                if ($itemForKey['value'] == $value) {
                                    $newRow[$key] = $itemForKey['text'];
                                    break;
                                }
                            }
                        } else { // Преобразуем данные к нужному типу
                            if (isset($arrData['column_model'][$key]) && is_array($arrData['column_model'][$key])) {
                                $infoField = $arrData['column_model'][$key];
                                if ($infoField['type'] == 'string') {
                                    $newRow[$key] = (string) $value;
                                } elseif ($infoField['type'] == 'boolean') {
                                    $newRow[$key] = (boolean) $value;
                                    if ($newRow[$key]) {
                                        $newRow[$key] = $this->Translate('Да');
                                    } else {
                                        $newRow[$key] = $this->Translate('Нет');
                                    }
                                } elseif ($infoField['type'] == 'number') {
                                    $numberValue = (float) $value;
                                    if ($numberValue % 1 == 0) {// целое число
                                        $numberValue = (int) $value;
                                    }
                                    $newRow[$key] = $numberValue;
                                }
                            } else {
                                $newRow[$key] = $value;
                            }
                        }
                    }
                    $arrData['rows'][] = $newRow;
                }
            }
            return $arrData;
        } catch (Exception $exc) {
            throw new Exception($exc->getMessage());
        }
    }

    /**
     * 
     * Exclude some of the fields from the report
     * 
     * @param array $fieldsExcluded  // an array of columns to exclude
     * @param array $modelColumns    // an array of all columns of the report
     *
     * @return array
     */
    protected function _excludeFieldsFromReport($fieldsExcluded, $modelColumns) {
        if (count($fieldsExcluded)) {
            foreach ($fieldsExcluded as $field) {
                $fieldExcluded = isset($modelColumns[$field]) ? $modelColumns[$field] : array();
                // Если в колонках есть взможность группировки, то
                // скорректируем кол. дочерних колонок в родительской
                if (isset($fieldExcluded['parent'])) {
                    $parent = $fieldExcluded['parent'];
                    $count_childrens = $fieldExcluded['count_childrens'];
                    if ($count_childrens) {
                        $count_childrens--;
                    }
                    foreach ($modelColumns as $key => $column) {
                        if ($column['parent'] == $parent) {
                            $modelColumns[$key]['count_childrens'] = $count_childrens;
                        }
                    }
                }
                // Удалим колонку из списка колонок
                if (isset($modelColumns[$field])) {
                    unset($modelColumns[$field]);
                }
            }
        }
        return $modelColumns;
    }

    /**
     * 
     * Creating a report footer
     * 
     * @param array $modelFooter  // array params 
     * keys:
     * - 'footer_colspan' (joint number of columns in the table)
     * - 'rows' (an array of values for the report records)
     * - 'column_model' (parameters of table columns)
     * - 'footers' (options footer, key - the column name value - an act or expression of a text)
     *
     * @return array
     */
    protected function _footerForReport($modelFooter) {
        $resultActions = array('count', 'sum', 'max', 'min', 'average');
        $rows_footer = array();
        $row_footer = array();
        $footer_colspan = $modelFooter['footer_colspan'];
        $rows = $modelFooter['rows'];
        $column_model = $modelFooter['column_model'];
        $footers = $modelFooter['footers'];
        $index = 0;
        //-----------------------------------------------------
        foreach ($footers as $footer) {
            foreach ($column_model as $key => $column) {
                if (isset($footer[$key])) {
                    $footer_value = $footer[$key];
                    if (in_array($footer_value, $resultActions)) {
                        $ArrayBox = new Default_Plugin_ArrayBox($rows);
                        $arrValues = $ArrayBox->slice($key);
                        switch ($footer_value) {
                            case 'count':
                                $row_footer[$index] = $arrValues->count();
                                break;
                            case 'sum':
                                $row_footer[$index] = $arrValues->sum();
                                break;
                            case 'max':
                                $arrResult = $arrValues->max();
                                $row_footer[$index] = $arrResult['value'];
                                break;
                            case 'min':
                                $arrResult = $arrValues->min();
                                $row_footer[$index] = $arrResult['value'];
                                break;
                            case 'average':
                                $row_footer[$index] = $arrValues->avg();
                                break;
                            default:
                                break;
                        }
                    } else {
                        $row_footer[$index] = $footer[$key];
                    }
                } else {
                    if ($footer_colspan > 1) {
                        $footer_colspan = $footer_colspan - 1;
                    } else {
                        $row_footer[$index] = '';
                    }
                }
                $index++;
            }

            $index = 0;
            $rows_footer[] = $row_footer;
        }
        return $rows_footer;
    }

    //=============== ADD FUNCTIONS =================//

    /**
     * Get count. rows in the table
     *
     * @param array $options
     *
     * @return int
     */
    public function getCountRowsTable($options = NULL) {
        
    }

    /**
     * Create a table row object
     *
     * @param array $options
     *
     * @return object
     */
    public function createRowTable($options = NULL) {
        
    }

    /**
     * Create a form to check the values of the table
     *
     * @param array $options
     *
     * @return Default_Form_MyForm
     */
    public function createFormForValidation($options = NULL) {
        
    }

    /**
     * Get a table records
     *
     * @param array $options
     *
     * @return array
     */
    public function getRowsArraysTable($options = NULL) {
        
    }

    /**
     * Get the values of the fields in Json format
     *
     * @param array $fields
     *
     * @return array
     */
    public function getValuesForData($fields) {
        
    }

    /**
     * Get the value of a field
     *
     * @param array $fields
     *
     * @return array
     */
    public function getValuesForCol($options = NULL) {
        
    }

    /**
     * Check the values of the row when saving data to the database
     *
     * @param array $row
     *
     * @return array
     */
    public function validRowForSave($row) {
        // Проверим строку на валидность
        $jsons = $this->_isValidRow($row);

        return $jsons;
    }

    /**
     * Get the formatted value
     *
     * @param string $key
     * @param string $value
     *
     * @return string
     */
    public function getFormattedValue($key, $value) {
        
    }

    /**
     * Get formatted row values
     *
     * @param array $rows
     *
     * @return array
     */
    public function getFormattedRows($rows) {
        
    }

    /**
     * Get filtered values row
     *
     * @param array $rows
     *
     * @return array
     */
    public function getFilteredRows($rows) {
        return $rows;
    }


    /**
     * Combining values from the two arrays separator - space
     *
     * @param array $aArrValue1
     * @param array $aArrValue2
     *
     * @return array
     */
    static function joinArraysForSearch($aArrValue1, $aArrValue2) {
        // Получим массив
        $newArray = array(
            'value' => trim($aArrValue1['value']),
            'text' => trim($aArrValue1['text'] . ' ' . $aArrValue2['text'])
        );
        return $newArray;
    }

}
