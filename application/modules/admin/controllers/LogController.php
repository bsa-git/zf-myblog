<?php

/**
 * Admin_LogController
 *
 * Controller - Log
 * logging: messages, statistics, error
 *
 * @uses       Default_Plugin_TableGrid
 * @package    Module-Admin (administration of site)
 * @subpackage Controllers
 * @author   Sergii Beskorovainyi <bsa2657@yandex.ru>
 * @license  MIT <http://www.opensource.org/licenses/mit-license.php>
 * @link     https://github.com/bsa-git/zf-myblog/
 */
class Admin_LogController extends Default_Plugin_TableGridController {

    /**
     * Initialization controller
     *
     */
    public function init() {
        parent::init();
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
        $arrResultData['pdf']['is_row_header'] = false;
        $arrResultData['pdf']['pageFormat'] = 'A4';

        // Установим параметры для HTML по умолчанию
        $arrResultData['html']['column_model'] = $arrData['column_model'];
        $arrResultData['html']['is_group_head'] = $arrData['is_group_head'];
        $arrResultData['html']['rows_body'] = isset($arrData['rows']) ? $arrData['rows'] : array();
        $arrResultData['html']['is_row_header'] = TRUE;
        $arrResultData['html']['footer_colspan'] = $footer_colspan;

        // Исключим в отчете некоторые поля
        $fieldsExcluded = array('pr');
        $newModelColumns = parent::_excludeFieldsFromReport($fieldsExcluded, $arrData['column_model']);
        $arrResultData['html']['column_model'] = $newModelColumns;

        // Получим массив записей для нижнего колонтитула
        $footers[] = array(
            'id' => $this->Translate('Всего записей') . ':',
            'ts' => 'count',
            'pr_name' => ' ',
        );

        // Сформируем набор записей для нижнего колонтитула
        $rows_footer = parent::_footerForReport(array(
                    'footer_colspan' => $footer_colspan,
                    'rows' => isset($arrData['rows']) ? $arrData['rows'] : array(),
                    'column_model' => $newModelColumns,
                    'footers' => $footers
        ));

        $arrResultData['html']['rows_footer'] = $rows_footer;

        // Получим данные для отчета
        switch ($table) {
            case 'admin.log_msg':

                // Установим параметры PDF
                $arrResultData['pdf']['logo_report'] = Default_Plugin_SysBox::getFullURL_Res('/images/system/update_log.gif');
                $arrResultData['pdf']['title_report'] = $this->Translate('Лог сообщений');

                // Установим параметры для HTML
                $arrResultData['html']['logo_report'] = Default_Plugin_SysBox::getFullURL_Res('/images/system/update_log.gif');
                $arrResultData['html']['title_report'] = $this->Translate('Лог сообщений');
                break;
            case 'admin.log_stat':

                // Установим параметры PDF
                $arrResultData['pdf']['logo_report'] = Default_Plugin_SysBox::getFullURL_Res('/images/system/logStat20x20.png');
                $arrResultData['pdf']['title_report'] = $this->Translate('Лог статистики');

                // Установим параметры для HTML
                $arrResultData['html']['logo_report'] = Default_Plugin_SysBox::getFullURL_Res('/images/system/logStat20x20.png');
                $arrResultData['html']['title_report'] = $this->Translate('Лог статистики');
                break;
            case 'admin.log_error':

                // Установим параметры PDF
                $arrResultData['pdf']['logo_report'] = Default_Plugin_SysBox::getFullURL_Res('/images/system/delete.gif');
                $arrResultData['pdf']['title_report'] = $this->Translate('Лог ошибок');

                // Установим параметры для HTML
                $arrResultData['html']['logo_report'] = Default_Plugin_SysBox::getFullURL_Res('/images/system/delete.gif');
                $arrResultData['html']['title_report'] = $this->Translate('Лог ошибок');
                break;
            default:
                break;
        }
        return $arrResultData;
    }

    //=============== Working with Tables =================//

    /**
     * Action - rows
     * get all data according to the request parameters
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
            $id = $row->getId();
            if ($isSaved) {
                $message = "User - \"$username\" updated a row in the table-\"$this->_table\" with id=\"$id\"";
                $this->_logMsg->admin_row_update($message);
            } else {
                $message = "User - \"$username\" inserted a row into a table-\"$this->_table\" with id=\"$id\"";
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
            $id = $row->getId();
            $message = "User - \"$username\" deleted a row from table-\"$this->_table\" with id=\"$id\"";
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
        return Default_Model_DbTable_BlogLog::GetLogCount($this->db, $this->_table, $options);
    }

    /**
     * Create a table record object
     *
     * @param array $options
     *
     * @return object
     */
    public function createRowTable($options = NULL) {
        return new Default_Model_DbTable_BlogLog($this->db, $this->_table);
    }

    /**
     * Get a table records
     *
     * @param array $options
     *
     * @return array
     */
    public function getRowsArraysTable($options = NULL) {
        return Default_Model_DbTable_BlogLog::GetLogs_Array($this->db, $this->_table, $options);
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
        $rows = Default_Model_DbTable_BlogLog::GetValuesForCol($this->db, $this->_table, $options);
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
        return new Admin_Form_Log();
    }

    /**
     * Valid the values of the row when save
     *
     * @param array $params
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
            foreach ($row as $key => $value) {
                $newRow[$key] = $value;
            }
            // Проверим строку на валидность
            $jsons = $this->_isValidRow($newRow);
        } else { // втавляем новую запись
            // Удалим из строки поле "info_key"
            foreach ($row as $key => $value) {
                $newRow[$key] = $value;
            }
            // Проверим строку на валидность
            $jsons = $this->_isValidRow($newRow);
        }
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

        if ($key == 'ts') {
            if ($value) {
                if (is_int($value)) {
                    $value = $this->dtFormat($value, 'yyyy-MM-dd HH:mm:ss', 'U');
                }
            } else {
                $value = $this->dtFormat(time(), 'yyyy-MM-dd HH:mm:ss', 'U');
            }
        }

        return $value;
    }

    /**
     * Get formatted rows values
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
                if ($key == 'ts') {
                    if ($value) {
                        if (is_int($value)) {
                            $value = $this->dtFormat($value, 'yyyy-MM-dd HH:mm:ss', 'U');
                        }
                    } else {
                        $value = $this->dtFormat(time(), 'yyyy-MM-dd HH:mm:ss', 'U');
                    }
                }
                if ($key == 'msg') {
                    $value = nl2br($value);
                }
                $formatRow[$key] = $value;
            }
            $formatRows[] = $formatRow;
            $formatRow = array();
        }

        return $formatRows;
    }

}
