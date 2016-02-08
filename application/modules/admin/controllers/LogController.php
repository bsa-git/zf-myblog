<?php

/**
 * Admin_LogController
 *
 * Контроллер - Log
 * Просмотр записей логирования: сообщений, статистики, ошибок
 *
 * @uses       Default_Plugin_TableGrid
 * @package    Module-Admin (Администрирование сайта)
 * @subpackage Controllers
 */
class Admin_LogController extends Default_Plugin_TableGridController {

    /**
     * Инициализация контроллера
     *
     */
    public function init() {
        parent::init();
    }

    //=============== РАБОТА С ОТЧЕТАМИ =================//

    /**
     * Действие - report
     * Отчеты по документам
     * 
     * Доступ к действию возможем по следующим путям urls:
     * - /admin/info/report
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

    //=============== РАБОТА С ТАБЛИЦЕЙ =================//

    /**
     * Действие rows (получить данные)
     * по этому действию происходит вывод всех данных
     * в соответствии с параметрами запроса
     *
     * Доступ к действию возможем по следующим путям urls:
     * - /admin/user/rows
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
     * - /admin/user/data
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
     * - /admin/user/validate
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
     * - /admin/user/save
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
     * - /admin/user/delete
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
     * Удалить обьект записи таблицы
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
     * Действие search (поиск значения в таблице)
     * по этому действию происходит поиск строки
     * в таблице и возвращается номер стр. поиска
     * если поиск произошел успешно
     *
     * Доступ к действию возможем по следующим путям urls:
     * - /admin/user/search
     *
     * @return void
     */
    public function searchAction() {
        parent::searchAction();
    }

    /**
     * Получить кол. строк в таблице
     *
     * @param array $options
     *
     * @return int
     */
    public function getCountRowsTable($options = NULL) {
        return Default_Model_DbTable_BlogLog::GetLogCount($this->db, $this->_table, $options);
    }

    /**
     * Создать обьект записи таблицы
     *
     * @param array $options
     *
     * @return object
     */
    public function createRowTable($options = NULL) {
        return new Default_Model_DbTable_BlogLog($this->db, $this->_table);
    }

    /**
     * Получить записи таблицы
     *
     * @param array $options
     *
     * @return array
     */
    public function getRowsArraysTable($options = NULL) {
        return Default_Model_DbTable_BlogLog::GetLogs_Array($this->db, $this->_table, $options);
    }

    /**
     * Получить значения полей в Json
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
     * Получить значения поля
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
     * Создать форму для проверки значений таблицы
     *
     * @param array $options
     *
     * @return Default_Form_MyForm
     */
    public function createFormForValidation($options = NULL) {
        return new Admin_Form_Log();
    }

    /**
     * Проверить значение при записи данных
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
     * Получить отформатированное значение
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
