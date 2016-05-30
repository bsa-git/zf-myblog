/**
 * Class - MyTableGrid
 *
 * With these class you can:
 *  - Initialize and create a grid table
 *  - Sort columns
 *  - Edit cells using various kinds of editors
 *  - Maintain Ajax mode when loading, sorting and editing data
 *  - Load the data into lists of type ComboBox
 *  - Validated editable data in table cells
 *
 * JavaScript
 *
 * @author   Sergii Beskorovainyi <bsa2657@yandex.ru>
 * @license  MIT <http://www.opensource.org/licenses/mit-license.php>
 * @link     https://github.com/bsa-git/zf-myblog/
 */
BSA.MyTableGrid = Class.create({
    tableGrid: null, // Table object
    idGrid: 0, // Number of grid on the page
    bodyDiv: null, // Element which contains a Grid
    boxSearch: null, // ComboBox for find
    idSearch: 0, // The unique ID value, record found
    boxFilter_fields: null, // ComboBox - table fields for the filter
    boxFilter_values: null, // ComboBox - table values for the filter
    boxFilter_compare: null, // ComboBox - compare for the filter
    itemsFilter_compare: ['=', '>', '>=', '<', '<=', '<>'],
    filterContainer: null,
    boxFilterContainer: new Hash(), // Array lists of ComboBox for filter
    paramsFilter: null,
    idTimeout: null,
    itemsComboBox: new Hash(), // Array lists for ComboBox fields
    listError: new Hash(), // Error array user input
    master: null,
    slave: null,
    accordion: null,
    actual: false,
    tooltipContainer: null, // Container windows tooltip
    tooltips: [], // An array of field numbers for which you have a sign - "tooltip"
    tooltip: null, // Tooltip object
    ajaxData: {// The types of get data for AJAX lists Типы получения данных для списков по AJAX
        dataFilter: 'dataFilter', // data for filter lists
        dataSearch: 'dataSearch', // data for search lists
        refKeyTable: 'refKeyTable', // data for foreign keys lists
        selfKeyTable: 'selfKeyTable', // data for internal keys list
        selfFieldTable: 'selfFieldTable', // data for internal table field list
        customFieldTable: 'customFieldTable' // custom data for internal fields of the table
    },
    lastScrollRow: -1, // Last line number when scrolling

    // Object initialization
    initialize: function (params)
    {
        // Set container
        if (params && $(params.table.container)) {
            this.container = params.table.container;
        } else {
            return;
        }

        // Get a table initialization function
        if (params.table.ini_table) {
            this.ini_table = params.table.ini_table;
        }

        // Initialization search
        if (params.search) {
            this.paramsSearch = params.search;
        }


        // Initialization filter
        if (params.filter) {
            this.paramsFilter = params.filter;
        }


        // Set controller URL
        this.url = lb.getMsg('urlBase') + params.table.url;

        // Set table name
        this.name = params.table.name;

        // Set the number of rows in the table on a single page
        this.rowsByPage = params.table.rowsByPage;

        // Define relations between tables
        if (params.table.master) {
            this.master = params.table.master;
        }

        if (params.table.slave) {
            this.slave = params.table.slave;
        }

        if (params.table.accordion) {
            this.accordion = params.table.accordion;
        }

        // Establish a calendar for the localization of the respective language
        this._localDateCalendar();

        // Define whether to use Tooltip?
        if ($('floatTip')) {
            this.tooltipContainer = $('floatTip');
        }

        if (this.accordion) {
            // Subscribe to the events in the accordion
            this._subscribeAccordionEvents();
        } else {
            // We obtain data on the URL or create a table
            if (this.getParamsForAjaxData()) {
                this.retrieveAjaxData();
            } else {
                this._createTable();
            }

            // Initialization tooltip
            if (this.tooltipContainer) {
                this._iniTooltip();
            }

        }
    },
    // Create table
    _createTable: function ()
    {
        var self = this;

        // Инициализация таблицы
        this.tableGrid = new MY.TableGrid(this._getTableModel());

        // Если таблица находиться в секции аккордиона
        if (this.accordion) {
            this._updateFilterForSlave(this.slave);
        }
        this.tableGrid.show(this.container);

        // Инициализация поиска
        if (this.paramsSearch) {
            this._iniSearch(this.paramsSearch);
            this._iniSearchBox();
        }

        // Инициализация фильтра
        if (this.paramsFilter) {
            var timeout = window.setTimeout(function () {
                self._iniFilter(self.paramsFilter);
                window.clearTimeout(timeout);
            }.bind(self), 2000);
        }
    },
    // Initialization table
    _getTableModel: function ()
    {
        return this.ini_table();
    },
    // Processing rows in the grid
    RowsProcessing: function () {

        // Получим bodyDiv для события скроллинга
        if (!this.idGrid) {
            this.idGrid = $$('.my-tablegrid').length;
            this.bodyDiv = $('bodyDiv' + this.idGrid);
        }

        // Установим в исходное состояние
        if (this.lastScrollRow !== -1) {
            this.lastScrollRow = -1;
        }

        // Установим подсказки для грида
        if (this.tooltips.size()) {
            Event.observe(this.bodyDiv, 'scroll', this._syncScroll.bind(this));
            this._syncTooltip();
        }
    },
    // Synchronization rows in the grid when scrolling
    _syncScroll: function () {
        // Установим подсказки для грида
        if (this.tooltips.size()) {
            // Выполним синхронизацию подсказок с задержкой 
            // ф-ия выполниться после всех событий в очереди
            this._syncTooltip.bind(this).defer();
        }
    },
    //--------- Tooltip ---------------//

    // Initialization tooltips for grid
    _iniTooltip: function () {
        var tooltips = [];
        //---------------------
        // Определим поля, для которых установлен признак подсказки
        var columnModel = this._getTableModel().columnModel;

        columnModel.each(function (column, index) {
            if (column.tooltip) {
                tooltips.push(
                        {
                            index: index,
                            id: column.id,
                            title: column.title,
                            width: column.width,
                            id_grid: this.idGrid
                        }
                );
            }
        }.bind(this))

        // Установим событие по определению положения окна подсказки
        if (tooltips.size()) {
            this.tooltip = new BSA.Tooltip();
            document.observe('mousemove', this.tooltip.Move.bind(this.tooltip));
            this.tooltips = tooltips;
        }
    },
    // Synchronization tooltips
    _syncTooltip: function () {

        var rowsCount = this.bodyDiv.select("tr.mtgRow" + this.idGrid).size();

        // Установим события появления/закрытия окна подсказки
        this.tooltips.each(function (tooltip) {
            if (this.tableGrid.rows.length > this.lastScrollRow + 1) {
                for (var i = this.lastScrollRow; i < rowsCount; i++) {
                    var rowIdx = i;
                    var idGridCell = 'mtgC' + this.idGrid + '_' + tooltip.index + ',' + rowIdx;
                    var gridCell = $(idGridCell);
                    if (gridCell) {
                        var str = gridCell.down('div').innerHTML;
                        gridCell.observe('mouseover', this.tooltip.View.bindAsEventListener(this.tooltip, str, gridCell));
                        gridCell.observe('mouseout', this.tooltip.Hide.bindAsEventListener(this.tooltip, gridCell));
                    }
                }
            }
        }.bind(this))

        this.lastScrollRow = rowsCount - 1;
    },
    //--------- Saving changes/addition values ---------------//

    // Save values
    onSaveValues: function (type) {
        var self = this;
        var rows;
        var result;
        //-----------------------
        if (type == 'modified')
            rows = self.tableGrid.getModifiedRows();
        if (type == 'add')
            rows = self.tableGrid.getNewRowsAdded();

        // Подготовим значения записей для сохранения
        // с учетом значений ComboBox
        var resultRows = this._getRowsForSave(rows);

        // Преобразуем записи в JSON формат
        var jsonRows = Object.toJSON(resultRows);
        var params = {
            rows: jsonRows,
            table: self.name
        };
        var checkResult = true;

        //Очистим сообщение об ошибке
        BSA.Sys.messagebox_clear();

        if (type == 'modified')
            BSA.Sys.message_write(lb.getMsg('msgDataAreUpdating'));
        if (type == 'add')
            BSA.Sys.message_write(lb.getMsg('msgDataAreAdding'));
        //-------------------------

        // Сделаем проверку на необходимость ввода значения
        rows.each(function (row) {
            var keys = Object.keys(row);
            var _row = row;
            keys.each(function (key) {
                if (!checkResult) {
                    return;
                }
                var value = _row[key];
                // Сделаем проверку на необходимость ввода значения
                result = self._checkRequiredValue(key, value);

                // Сделаем проверку на валидацию
                if (!result.messages) {
                    result = self._isValid({
                        field: key,
                        value: value
                    });
                }

                if (result.messages) {
                    // Выведем сообщение об ошибке
                    if (type == 'modified') {
                        var message = result.messages[0].replace('.', '') + ' ' + lb.getMsg('msgForRowId') + _row['id'];
                        result.messages[0] = message;
                    }

                    BSA.Sys.message_write(lb.getMsg('errSaveData'));


                    BSA.Sys.messagebox_write(result.class_message, result.messages);
                    checkResult = false;
                    return;
                }
            })
            if (!checkResult) {
                return;
            }
        });

        if (!checkResult) {
            return;
        }

        // Инициализация Ajax запроса
        new Ajax.Request(self.url + '/save', {
            parameters: params,
            // Успешный ответ
            onSuccess: function (response) {
                // Получим данные ответа
                var json = BSA.Sys.getJsonResponse(response, true);
                try {
                    // Проверим есть ли ошибки
                    if (json.class_message) {// ERRORS
                        // Выведем сообщение об ошибке
                        BSA.Sys.message_write(lb.getMsg('errSaveData'));
                    } else { // OK

                        self.tableGrid.clear();

                        BSA.Sys.message_write(json.result);

                        // Получим новые данные в списки полей и перезагрузим текущую страницу
                        if (self.getParamsForAjaxData('selfFieldTable')) {
                            self.retrieveAjaxData('selfFieldTable');
                        } else {
                            // Отобразим измененную таблицу на текущей странице
                            //                            var pager = self.tableGrid.pager;
                            //                            self.tableGrid._retrieveDataFromUrl(pager.currentPage, false);
                            self.reViewTable();
                        }
                        // Обновим подчиненные таблицы
                        if (type == 'add') {
                            self._updateDataForSlaves(self.master);
                        }


                        // Обновим список для поиска значений в таблице
                        if (self.boxSearch) {
                            self.boxSearch.options.items = null;
                            self.boxSearch.getAllChoices();
                        }

                    }
                } catch (ex) {
                    if (ex instanceof Error) { // Это экземпляр Error или подкласса?
                        self.onFailure(ex.name + ": " + ex.message);
                    }

                } finally {
                    BSA.Sys.message_clear();
                }
            },
            // Ошибочный ответ
            onFailure: function (transport) {
                var errText = transport.responseText;
                var msgs = [lb.getMsg('errSaveData'), errText];
                BSA.Sys.messagebox_write('warning', msgs);
                self.onFailure(lb.getMsg('errSaveData'));
            }
        });
    },
    // Get (prepare) the rows to save. Send the correct values for the ComboBox
    _getRowsForSave: function (rows) {
        var self = this;
        var keys;
        var params;
        var resultRows = [];
        var resultRow = {};
        var objColumn;
        var arrTable, arrMyTable;
        var table = '';
        var myTable = '';
        var value;
        //---------------------
        // Получим имя таблицы нашего обьекта
        arrMyTable = this.name.split('.');
        myTable = (arrMyTable.length > 1) ? arrMyTable[1] : arrMyTable[0];

        // Отфильтруем ненужные поля из строки записи
        // Попадают только те поля, которые относяться к основной таблице
        // или ее расширению -> profile (пр. blog_posts или blog_posts_profile)
        rows.each(function (row) {
            keys = Object.keys(row);
            keys.each(function (key) {
                objColumn = self._getColumnObj(key);
                if (objColumn) {
                    if (objColumn.joinTable) {
                        arrTable = objColumn.joinTable.split('.');
                        table = (arrTable.length > 1) ? arrTable[1] : arrTable[0];
                    }
                    if (table === '' || table == myTable || (table == (myTable + '_profile'))) {
                        value = row[key];
                        value = BSA.Sys.replaceValuesForJson(value);
                        params = {
                            field: key,
                            value: value
                        };
                        resultRow[key] = self._getValueForComboBox(params);
                    }
                }
            });
            resultRows.push(resultRow);
            resultRow = {};
        });
        return resultRows;
    },
    //--------- Delete values ---------------//

    onDeleteValues: function () {

        var self = this;
        var rowsDelete = [];
        var rows = self.tableGrid.getSelectedRowsByColumn('_nn_');

        //Очистим сообщение об ошибке
        BSA.Sys.messagebox_clear();
        //        BSA.Sys.message_clear();

        // Получи массив для удаления записей
        rows.each(function (row) {
            if (row['id']) {
                rowsDelete.push(row['id']);
            }
        });

        // Если массив для удаления пустой, то удалим записи из таблицы
        // без удаления записей с сервера
        if (rowsDelete.length == 0) {
            // Перезагрузим текущую страницу
            //            var pager = self.tableGrid.pager;
            //            self.tableGrid._retrieveDataFromUrl(pager.currentPage, false);
            self.reViewTable();

            BSA.Sys.message_write(lb.getMsg('msgDataSuccessfullyRemoved'));
            BSA.Sys.message_clear();
            return;
        }

        var jsonRows = Object.toJSON(rowsDelete);
        var params = {
            rows: jsonRows,
            table: self.name
        };


        BSA.Sys.message_write(lb.getMsg('msgDataAreRemoving'));
        //-------------------------

        // Инициализация Ajax запроса
        new Ajax.Request(self.url + '/delete', {
            parameters: params,
            // Успешный ответ
            onSuccess: function (response) {
                // Получим данные ответа
                var json = BSA.Sys.getJsonResponse(response, true);
                try {

                    // Проверим есть ли ошибки
                    if (!json.class_message) {// OK

                        BSA.Sys.message_write(json.result);

                        // Получим новые данные в списки полей и перезагрузим текущую страницу
                        if (self.getParamsForAjaxData('selfFieldTable')) {
                            self.retrieveAjaxData('selfFieldTable');
                        } else {
                            self.reViewTable();
                        }
                        // Обновим подчиненные таблицы
                        self._updateDataForSlaves(self.master);

                        // Очистить список ошибок
                        //self._clearListErrors();

                        BSA.Sys.message_write(lb.getMsg('msgDataSuccessfullyRemoved'));
                        BSA.Sys.message_clear();

                        // Обновим список для поиска значений в таблице
                        if (self.boxSearch) {
                            self.boxSearch.options.items = null;
                            self.boxSearch.getAllChoices();
                        }


                    }
                } catch (ex) {
                    if (ex instanceof Error) { // Это экземпляр Error или подкласса?
                        self.onFailure(ex.name + ": " + ex.message);
                    }

                } finally {
                    BSA.Sys.message_clear();
                }
            },
            // Ошибочный ответ
            onFailure: function (transport) {
                var errText = transport.responseText;
                var msgs = [lb.getMsg('errDeleteData'), errText];
                BSA.Sys.messagebox_write('warning', msgs);
                self.onFailure(lb.getMsg('errDeleteData'));
            }
        });
    },
    //--------- Report ---------------//

    // Create PDF report
    onReportPDF: function () {

        //        Event.stop(event);
        var self = this;

        //Очистим сообщение об ошибке
        BSA.Sys.messagebox_clear();

        // Откроем диалог
        BSA.Dialogs.openDialogInfo({
            type: 'WaiteServerAction',
            msg: lb.getMsg('msgIsPreparingReport')
        });
        // Получим параметры для Ajax
        var params = this._getParamsForReport('pdf');

        // Инициализация Ajax запроса
        new Ajax.Request(self.url + '/report', {
            parameters: params,
            // Успешный ответ
            onSuccess: function (response) {
                try {
                    // Получим данные ответа
                    var json = BSA.Sys.getJsonResponse(response, true);

                    if (!json.class_message) {// OK
                        BSA.Sys.message_write(json.result);

                        var winDimensions = document.viewport.getDimensions();
                        var w = window.open(json.url_file_pdf, '_blank', "width=" + (winDimensions.width - 20) + ",height=" + winDimensions.height + ",resizable=yes");
                        if (w) {
                            w.focus();
                        } else {
                            // Выведем предупреждение о невозможности открыть окно..
                            BSA.Sys.messagebox_write('caution', [lb.getMsg('msgNotOpenReportWindow')]);
                        }
                    }
                } catch (ex) {
                    if (ex instanceof Error) { // Это экземпляр Error или подкласса?
                        self.onFailure(ex.name + ": " + ex.message);
                    }

                } finally {
                    // Очистим сообщения
                    BSA.Sys.message_clear();
                    // Закроем диалог
                    BSA.Dialogs.closeDialogInfo();
                }
            },
            // Ошибочный ответ
            onFailure: function (transport) {
                var errText = transport.responseText;
                var msgs = [lb.getMsg('errDeleteData'), errText];
                BSA.Sys.messagebox_write('warning', msgs);
                self.onFailure(lb.getMsg('errDeleteData'));

            }
        });
    },
    // Create HTML report
    onReportHTML: function () {

        var self = this;

        //Очистим сообщение об ошибке
        BSA.Sys.messagebox_clear();

        var params = this._getParamsForReport('html');

        // Инициализация Ajax запроса
        new Ajax.Request(self.url + '/report', {
            parameters: params,
            // Успешный ответ
            onSuccess: function (response) {

                try {

                    // Получим данные ответа
                    var json = BSA.Sys.getJsonResponse(response, true);

                    // Проверим есть ли ошибки
                    if (!json.class_message) {// OK
                        BSA.Sys.message_write(json.result);
                        var winDimensions = document.viewport.getDimensions();
                        var params = "width=" + (winDimensions.width - 20) + ",height=" + winDimensions.height + ",resizable=yes";
                        var w = window.open('', '_blank', params);

                        if (w) {
                            w.focus();
                            var d = w.document; // Получить ссылку на объект Document
                            d.open(); // Начать новый документ (необязательно)
                            d.write(json.html); // Вывести содержимое документа
                            d.close();
                        } else {
                            // Выведем предупреждение о невозможности открыть окно..
                            BSA.Sys.messagebox_write('caution', [lb.getMsg('msgNotOpenReportWindow')]);
                        }
                    }
                } catch (ex) {
                    if (ex instanceof Error) { // Это экземпляр Error или подкласса?
                        self.onFailure(ex.name + ": " + ex.message);
                    }

                } finally {
                    BSA.Sys.message_clear();
                    BSA.Dialogs.closeDialogInfo();
                }
            },
            // Ошибочный ответ
            onFailure: function (transport) {
                var errText = transport.responseText;
                var msgs = [lb.getMsg('errDeleteData'), errText];
                BSA.Sys.messagebox_write('warning', msgs);
                self.onFailure(lb.getMsg('errDeleteData'));

            }
        });
    },
    // Get the object parameters for the report
    _getParamsForReport: function (type) {
        var tg = this.tableGrid;
        var rp = {};
        var filter = null;
        var childrens = null;
        var refColum = '';
        //-----------------------
        rp.table = this.name; // название таблицы
        rp.type = type; // тип отчета
        // Установим параметр для фильтра
        filter = this._getParamsForFilter('filter');
        if (filter) {
            rp.filter = Object.toJSON(filter);
        }

        // Получим клон для списков ItemsComboBox
        var cloneItemsComboBox = this.itemsComboBox.clone();

        // кол. записей на странице отчета
        rp.rowsByPage = -1; // вывести все строки на странице
        // номер страницы
        rp.page = 1;
        // колонка для сортировки, таблица чья эта колонка и направление сортировки
        rp[tg.sortColumnParameter] = tg.request[tg.sortColumnParameter];
        rp[tg.ascDescFlagParameter] = tg.request[tg.ascDescFlagParameter];
        rp.joinTableForSort = tg.request['joinTableForSort'];
        // Получим упрощенную модель описания таблицы
        // для передачи в параметрах запроса
        var arrColumnModel = [];
        var columnModel = this._getTableModel().columnModel;
        columnModel.each(function (column) {
            if (column.children) {
                childrens = column.children;
                var countChildrens = childrens.size();
                childrens.each(function (children) {
                    if (children.referenceMap) {
                        refColum = children.referenceMap.refColum;
                        // Установим списки для ключа "refColum"
                        // под новым ключем -> "column.id"
                        cloneItemsComboBox.set(children.id, cloneItemsComboBox.get(refColum));
                        // Удалим списки под старым ключем "refColum"
                        cloneItemsComboBox.unset(refColum);
                    }
                    arrColumnModel.push({
                        id: children.id,
                        title: children.title,
                        type: (children.type) ? children.type : 'string',
                        width: children.width,
                        parent: column.title,
                        count_childrens: countChildrens
                    });
                })
            } else {
                if (column.referenceMap) {
                    refColum = column.referenceMap.refColum;
                    // Установим списки для ключа "refColum"
                    // под новым ключем -> "column.id"
                    cloneItemsComboBox.set(column.id, cloneItemsComboBox.get(refColum));
                    // Удалим списки под старым ключем "refColum"
                    cloneItemsComboBox.unset(refColum);
                }
                if (column.id != '_nn_') {
                    arrColumnModel.push({
                        id: column.id,
                        title: column.title,
                        type: (column.type) ? column.type : 'string',
                        width: column.width,
                        parent: '',
                        count_childrens: 0
                    });
                }
            }
        })
        rp.columnModel = Object.toJSON(arrColumnModel);

        // Получим параметры значений для ComboBox полей
        //column.referenceMap)?column.referenceMap.refColum:
        rp.itemsComboBox = Object.toJSON(cloneItemsComboBox);

        return rp;
    },
    //--------- Validate value ---------------//
    _validateValue: function (params) {
        var self = this;
        var isModifiedCell = false;
        var innerElement;
        //-------------------------
        //Очистим сообщение об ошибке
        BSA.Sys.messagebox_clear();

        // Получим текущую позицию ячейки
        var currentPosition = self.tableGrid.getCurrentPosition();
        var x = currentPosition[0];
        var y = currentPosition[1];
        var key = x + ':' + y;

        // Получим обьект текущей ячейки
        var currentCell = self.tableGrid.getCellElementAt(x, y);
        innerElement = currentCell.down();

        // Установим ссылку на ячейку и событие ячейки
        // при нажатии кнопки клавиатуры - Enter
        if (self.listError.get(key)) {
            var value = self.listError.get(key).value;
            if (value == params.value) {// Если значение не изменилось, то выйдем из ф-ии
                return;
            } else {
                // Сохраним изменное проверяемое значение
                self.listError.get(key).value = params.value;

                // Если значение изменилось, но редактор не
                // установил соответствующий класс - 'modified-cell'
                // и не добавил в список измененных
                // мы сами установим этот класс и добавим в список измененных строк
                // Это работает, если только мы изменяем строку, а не добавляем -> (y >= 0)
                isModifiedCell = innerElement.hasClassName('modified-cell');
                if (!isModifiedCell && self.tableGrid.modifiedRows.indexOf(y) == -1 && y >= 0) {
                    innerElement.addClassName('modified-cell');
                    self.tableGrid.modifiedRows.push(y);
                }

            }

            // Сделаем проверку на необходимость ввода значения
            var result = self._checkRequiredValue(params.field, params.value);
            if (result.messages) {

                var objColumn = this._getColumnObj(params.field);
                // Добавим класс ошибки в текущую ячейку
                currentCell.addClassName('my-textfield-input-error');

                // Создадим подсказку для этой ошибки
                var tooltip = new MY.ToolTip({
                    parent: currentCell,
                    message: '<em>' + objColumn.title + ': </em>' + lb.getMsg('errValueCanNotBeEmpty') + ';',
                    type: 'error'
                });

                // Сохраним инф. об ошибке в списке ошибок
                self.listError.get(key).tooltip = tooltip;

                // Выведем сообщение об ошибке
                BSA.Sys.messagebox_write(result.class_message, result.messages);

                return;
            }

        } else {// Выбрали ячейку первый раз. Мы запомним значение без проверки

            // Установим событие ячейки
            var _onKeyPressHandler = self._onKeyPressEnter.bindAsEventListener(this);
            self.tableGrid.keys.addEvent('action', currentCell, _onKeyPressHandler);

            // Сохраним текущее проверяемое значение
            self.listError.set(key, {
                value: params.value,
                currentCell: currentCell // Сохраняем для сохранения события нажатия Enter
            });
            return;
        }

        // Инициализация Ajax запроса
        new Ajax.Request(self.url + '/validate', {
            parameters: params,
            // Успешный ответ
            onSuccess: function (response) {
                // Получим данные ответа
                var json = BSA.Sys.getJsonResponse(response, true);
                try {

                    // Проверим есть ли ошибки при валидации
                    if (json.class_message) {// есть ошибки
                        // Добавим класс ошибки в текущую ячейку
                        currentCell.addClassName('my-textfield-input-error');

                        // Создадим подсказку для этой ошибки
                        var tooltip = new MY.ToolTip({
                            parent: currentCell,
                            message: json.messages.pop(),
                            type: 'error'
                        });

                        // Сохраним инф. об ошибке в списке ошибок
                        self.listError.get(key).tooltip = tooltip;
                    } else { // OK
                        if (self.listError.get(key).tooltip) { // Были обнаружены ошибки
                            var thisError = self.listError.get(key);

                            // Удалим подсказку об ошибке
                            thisError.tooltip.remove();

                            // Удалим класс ошибки
                            //thisError.cell.removeClassName('my-textfield-input-error');
                            currentCell.removeClassName('my-textfield-input-error');
                        }

                        // Запомним отфильтрованное и проверенное значение
                        // в ячейке
                        if (json.value) {
                            self.tableGrid.setValueAt(json.value, x, y, false)
                        }

                    }
                } catch (ex) {
                    if (ex instanceof Error) { // Это экземпляр Error или подкласса?
                        self.onFailure(ex.name + ": " + ex.message);
                    }

                } finally {
                }
            },
            // Ошибочный ответ
            onFailure: function (transport) {
                var errText = transport.responseText;
                var msgs = [lb.getMsg('errValidateValue'), errText];
                BSA.Sys.messagebox_write('warning', msgs);
                self.onFailure(lb.getMsg('errValidateValue'));
            }
        });
    },
    // Validate value without Ajax
    _validateValueNoAjax: function (params) {
        var self = this;
        var result;
        //---------------------
        //Очистим сообщение об ошибке
        BSA.Sys.messagebox_clear();

        // Получим текущую позицию ячейки
        var currentPosition = self.tableGrid.getCurrentPosition();
        var x = currentPosition[0];
        var y = currentPosition[1];
        var key = x + ':' + y;

        // Получим обьект текущей ячейки
        var currentCell = self.tableGrid.getCellElementAt(x, y);
        //        innerElement = currentCell.down();

        // Установим ссылку на ячейку и событие ячейки
        // при нажатии кнопки клавиатуры - Enter
        if (self.listError.get(key)) {
            var value = self.listError.get(key).value;

            // Если значение не изменилось, то выйдем из ф-ии
            if (value == params.value) {
                return;
            } else {
                // Сохраним изменное проверяемое значение
                self.listError.get(key).value = params.value;

            }

            // Сделаем проверки значения
            result = self._isValid(params);

            if (result.messages) {// Были обнаружены ошибки

                //                var objColumn = this._getColumnObj(params.field);
                // Добавим класс ошибки в текущую ячейку
                currentCell.addClassName('my-textfield-input-error');

                // Создадим подсказку для этой ошибки
                var tooltip = new MY.ToolTip({
                    parent: currentCell,
                    //                    message : '<em>'+ objColumn.title +': </em>' + lb.getMsg('errValueCanNotBeEmpty') + ';',
                    message: result.messages[1],
                    type: 'error'
                });

                // Сохраним инф. об ошибке в списке ошибок
                self.listError.get(key).tooltip = tooltip;

                // Выведем сообщение об ошибке
                BSA.Sys.messagebox_write(result.class_message, result.messages);
            } else {
                if (self.listError.get(key).tooltip) {
                    var thisError = self.listError.get(key);

                    // Удалим подсказку об ошибке
                    thisError.tooltip.remove();

                    // Удалим класс ошибки
                    //thisError.cell.removeClassName('my-textfield-input-error');
                    currentCell.removeClassName('my-textfield-input-error');
                }
            }

        } else {// Выбрали ячейку первый раз. Мы запомним значение без проверки

            // Установим событие ячейки
            var _onKeyPressHandler = self._onKeyPressEnterNoAjax.bindAsEventListener(this);
            self.tableGrid.keys.addEvent('action', currentCell, _onKeyPressHandler);

            // Сохраним текущее проверяемое значение
            self.listError.set(key, {
                value: params.value,
                currentCell: currentCell // Сохраняем для сохранения события нажатия Enter
            });
            return;
            // Если значение первый раз пустое = "", то проверку не будем делать
            //            if(! params.value){
            //                return;
            //            }
        }
    },
    // Verify whether a value is required?
    _checkRequiredValue: function (column_id, value) {
        var result = {};
        var objColumn = this._getColumnObj(column_id);
        //------------------------
        if (objColumn === null) {
            return result;
        }
        var _value;
        if (value === undefined) {
            _value = '';
        } else if (value === null) {
            _value = '';
        } else if (value === '') {
            _value = '';
        } else {
            _value = value;
        }
        if (objColumn.required && _value == '') {
            // Выведем сообщение об ошибке
            result.class_message = 'warning';
            result.messages = ['<em>' + lb.getMsg('errForm') + '</em>',
                '<em>' + objColumn.title + ': </em>' + lb.getMsg('errValueCanNotBeEmpty') + ';'];
        }
        return result;
    },
    // Is valid value?
    _isValid: function (params) {
        var self = this;
        var keys;
        var validator;
        var result = {};
        var pattern;
        var whiteSpace = '';
        var matchResult;
        var msgError = '';
        var objColumn = this._getColumnObj(params.field);
        //--------------------

        // Проверим обьект
        if (!objColumn) {
            return result;
        }

        // Если валидаторы отсутствуют, то выйдем из ф-ии
        if (!objColumn.validators) {
            return result;
        }
        keys = Object.keys(objColumn.validators);
        keys.each(function (key) {
            switch (key) {
                case "alnum":
                    validator = objColumn.validators[key];

                    // Проверим на пустую строку
                    if (validator.required) {
                        if (params.value === '') {
                            msgError = lb.getMsg('msgValueCanNotBeEmptyString');
                        }
                    }

                    // Проверим на правильность введенных символов
                    if (!msgError) {
                        // Определим шаблон
                        whiteSpace = validator.allowWhiteSpace ? '\\s' : '';
                        if (validator.unicodeEnabled) { // Поддержка Unicode
                            // \u0400-\u04FF -> любой символ кириллицы
                            pattern = '[\\w\\u0400-\\u04FF' + whiteSpace + ']';
                        } else { // Только английские буквы, знак подчеркивания
                            pattern = '[\\w' + whiteSpace + ']';
                        }
                        // Установим может ли быть пустое значение
                        if (validator.required) {
                            pattern += '+';
                        } else {
                            pattern += '*';
                        }
                        pattern = '^' + pattern + '$';
                        pattern = new RegExp(pattern);
                        matchResult = pattern.test(params.value);

                        if (!matchResult) {
                            //Выведем сообщение об ошибке
                            msgError = lb.getMsg('msgValueContainsInvalidCharacters', {
                                value: params.value
                            });
                        }
                    }
                    break;
                default :
                    break;
            }
            if (msgError) {
                return;
            }
        })
        if (msgError) {
            //Выведем сообщение об ошибке
            result.class_message = 'warning';
            result.messages = ['<em>' + lb.getMsg('errForm') + '</em>',
                '<em>' + objColumn.title + ': </em>' + msgError];
        }
        return result;
    },
    /**
     * Event by pressing the buttons on the keypad - Enter. 
     * This function validates the value entered into a table cell
     *
     * @param Event event
     */
    _onKeyPressEnter: function (event) {

        var cm = this.tableGrid.columnModel;
        var currentPosition = this.tableGrid.getCurrentPosition();
        var x = currentPosition[0];
        var y = currentPosition[1];
        var field = cm[x].id;
        var params = {
            table: this.tableGrid.name,
            field: field,
            value: this.tableGrid.getValueAt(x, y)
        }
        var validateValue = this._getValueForComboBox(params);
        params.value = validateValue;
        this._validateValue(params);
    },
    /**
     * Event by pressing the buttons on the keypad - Enter. 
     * This function validates the value entered into a table cell without Ajax
     *
     * @param Event event 
     */
    _onKeyPressEnterNoAjax: function (event) {

        var cm = this.tableGrid.columnModel;
        var currentPosition = this.tableGrid.getCurrentPosition();
        var x = currentPosition[0];
        var y = currentPosition[1];
        var field = cm[x].id;
        var params = {
            table: this.tableGrid.name,
            field: field,
            value: this.tableGrid.getValueAt(x, y)
        }
        this._validateValueNoAjax(params);
    },
    //---------- AJAX DATA ------- //

    // Get data at URL
    retrieveAjaxData: function (type) {
        var self = this;
        var params = this.getParamsForAjaxData(type);
        var listAjaxData;
        //--------------------------------

        if (!params.fields) {
            return;
        }
        // Преобразуем параметры в JSON формат
        var jsonParams = params;
        jsonParams.fields = Object.toJSON(jsonParams.fields);


        //Очистим сообщение об ошибке
        BSA.Sys.messagebox_clear();

        new Ajax.Request(this.url + '/data', {
            parameters: jsonParams,
            onSuccess: function (response) {
                var json = BSA.Sys.getJsonResponse(response, true);
                try {

                    // Проверим есть ли ошибки
                    if (!json.class_message) {// OK
                        // Сохраним полученные данные в свойствах обьекта
                        var params = self.getParamsForAjaxData(type);
                        var fields = Object.keys(params.fields);
                        fields.each(function (field) {
                            if (field) {
                                if (json[field]) {

                                    // Преобразуем полученные данные, в зависимости от их типа
                                    listAjaxData = self._prepAjaxData(field, json[field]);
                                    self.itemsComboBox.set(field, listAjaxData);
                                } else {// Ошибка, если отсутвуют данные в ответе
                                    throw new Error(lb.getMsg('errRetrieveDataFromUrl'));
                                }
                            }
                        });

                        // Создадим таблицу
                        if (self.tableGrid) {
                            // Обновим списки в ComboBox
                            self._updateItemsForComboBox();

                            // Загрузим таблицу с новыми данными в списках
                            //                            var pager = self.tableGrid.pager;
                            //                            self.tableGrid._retrieveDataFromUrl(pager.currentPage, false); 
                            self.reViewTable();
                        } else {
                            self._createTable();
                        }

                    }

                } catch (ex) {
                    if (ex instanceof Error) { // Это экземпляр Error или подкласса?
                        self.onFailure(ex.name + ": " + ex.message);
                    }

                } finally {

                }
            },
            onFailure: function (transport) {
                var errText = transport.responseText;
                var msgs = [lb.getMsg('errRetrieveDataFromUrl'), errText];
                BSA.Sys.messagebox_write('warning', msgs);
                self.onFailure(lb.getMsg('errRetrieveDataFromUrl'));
            }
        });
    },
    // Get the parameter object for Ajax Data
    // This object contains:
    // - table name -> table : "admin.posts",
    // - type of query data -> type : "table",
    // - set the table fields object -> fields : {}
    // which contains:
    // - field name : attached table, which refers to this field
    //  -> fieldName : joinTable
    getParamsForAjaxData: function (type) {
        //var self = this;
        var refMap;
        var fields = {};
        var params = null;
        var cm = this._getTableModel().columnModel;
        var id = '';
        var childrens;
        var length = cm.length;
        //-----------------
        for (var i = 0; i < length; i++) {
            id = cm[i].id;
            if (cm[i].children) {
                childrens = cm[i].children;
                for (var j = 0; j < childrens.length; j++) {
                    id = childrens[j].id;
                    if (childrens[j].ajaxdata) {

                        // Определим тип загрузки данных
                        if (type === undefined || childrens[j].ajaxdata == type) {
                            if (childrens[j].joinTable) {
                                fields[id] = childrens[j].joinTable;
                            } else {
                                if (childrens[j].referenceMap) {
                                    refMap = childrens[j].referenceMap;
                                    fields[refMap.refColum] = refMap.refTable;
                                } else {
                                    fields[id] = '';
                                }
                            }
                        }
                    }
                }
            } else {
                if (cm[i].ajaxdata) {

                    // Определим тип загрузки данных
                    if (type === undefined || cm[i].ajaxdata == type) {
                        if (cm[i].joinTable) {
                            fields[id] = cm[i].joinTable;
                        } else {
                            if (cm[i].referenceMap) {
                                refMap = cm[i].referenceMap;
                                fields[refMap.refColum] = refMap.refTable;
                            } else {
                                fields[id] = '';
                            }
                        }
                    }
                }
            }
        }
        length = Object.keys(fields).length;
        if (length) {
            params = {};
            params.table = this.name;
            params.type = 'table';
            params.fields = fields;
        }
        return params;
    },
    //---------- Find ------- //

    // Initialization search
    _iniSearch: function (params)
    {
        var self = this;
        var box = '';
        var input = '';
        var ok;
        var keys = Object.keys(params);
        keys.each(function (key) {
            box = key;
            input = params[key];
            // Отобразим контейнер для поиска, если он есть
            if ($(box)) {
                $(box).show();
                self.input_search = input;
                ok = $(box).select('input[type="submit"]')[0];
                if (ok) {
                    ok.observe('click', self.onSearchClick.bindAsEventListener(self));
                }
            }

        })
    },
    // Initialization search box
    _iniSearchBox: function () {
        var self = this;
        var boxSearch = null;
        if (self.input_search) {
            boxSearch = new MY.ComboBox({
                input: self.input_search,
                url: self.url + '/data',
                initialText: lb.getMsg('msgFindValues'),
                parameters: {},
                getParameters: function () {
                    var params = self._getParamsForSearch();
                    params.fields = Object.toJSON(params.fields);
                    return params;
                }
            });
            self.boxSearch = boxSearch;
        }
    },
    // Get the parameter the object to search
    // This object contains:
    // - table name -> table : "admin.posts",
    // - type of query data -> type : "search",
    // - set the table fields object -> fields : {}
    // which contains:
    // - field name : attached table, which refers to this field  -> fieldName : joinTable
    // filter for table (filter structure is described in the function for receiving filter parameter) -> filter : {} 
    _getParamsForSearch: function () {
        var self = this;
        var fields = {};
        var params = {};
        var cm = self.tableGrid.columnModel;

        for (var i = 0; i < cm.length; i++) {
            if (cm[i].search) {
                if (cm[i].joinTable) {
                    fields[cm[i].id] = cm[i].joinTable;
                } else {
                    fields[cm[i].id] = '';
                }
            }
        }
        if (fields) {
            params.table = self.name;
            params.type = 'search';
            //params.fields = Object.toJSON(fields);
            params.fields = fields;
            if (self.tableGrid.request['filter']) {
                params.filter = self.tableGrid.request['filter'];
            }
        }
        return params;
    },
    // Get the request parameters for the event - onSearchClick
    _getParamsForSearchClick: function () {
        var searchParameters = {};
        var value_id = 0;
        //---------------------------
        // Получим параметры для поиска
        var params = this._getParamsForSearch();


        searchParameters.table = params.table;
        // Найдем колонку сортировки и направление сортировки
        var cm = this.tableGrid.columnModel;
        var sortedColumnIndex = this.tableGrid.sortedColumnIndex;
        var ascDescFlg = cm[sortedColumnIndex].sortedAscDescFlg;
        var sortColumn = cm[sortedColumnIndex].id;
        searchParameters.sortColumn = sortColumn;
        searchParameters.ascDescFlg = ascDescFlg;
        searchParameters.joinTableForSort = this._getJoinTableForSort();

        // Получим число строк на странице
        searchParameters.rowsByPage = this.rowsByPage;

        // Получим значение для поиска
        var value = this.boxSearch.getValue();
        if (value == lb.getMsg('msgFindValues')) {
            alert(lb.getMsg('errSearchNotPossible') + '\n' + lb.getMsg('errDoNotSelectValueToSearch'))
            return null;
        } else {

            var items = this.boxSearch.getItems()
            for (var i = 0; i < items.length; i++) {
                if (items[i]['text'] == value) {
                    value_id = items[i]['value'];
                    break;
                }
            }
            if (value_id) {
                searchParameters.value_id = value_id;
            } else {
                alert(lb.getMsg('errSearchNotPossible') + '\n' + lb.getMsg('errValueForSearchIsNotTrue'))
                return null;
            }

        }
        if (params.filter) {
            searchParameters.filter = params.filter;
        }


        return searchParameters;
    },
    onSearchClick: function () {
        var self = this;
        //-----------------

        //Очистим сообщение об ошибке
        BSA.Sys.messagebox_clear();

        // Получим параметры для поиска
        var searchParameters = self._getParamsForSearchClick();

        if (searchParameters === null) {
            return;
        }
        new Ajax.Request(this.url + '/search', {
            parameters: searchParameters,
            onSuccess: function (response) {

                try {

                    var json = BSA.Sys.getJsonResponse(response, true);

                    // Проверим есть ли ошибки
                    if (!json.class_message) {// OK

                        BSA.Sys.message_write(json.result);

                        // Установим ID найденой записи
                        // этот признак используется в обработчике afterRender
                        self.idSearch = json.id;

                        // Значение найдено, перейдем на соответсвующую страницу
                        // и выделим соответсвующую запись в таблице
                        // Перезагрузим текущую страницу
                        //                        self.tableGrid._retrieveDataFromUrl(json.page, false);
                        self.reViewTable(json.page);
                        BSA.Sys.message_clear();
                    }

                } catch (ex) {
                    if (ex instanceof Error) { // Это экземпляр Error или подкласса?
                        self.onFailure(ex.name + ": " + ex.message);
                    }

                } finally {

                }
            },
            onFailure: function (transport) {
                var errText = transport.responseText;
                var msgs = [lb.getMsg('errRetrieveDataFromUrl'), errText];
                BSA.Sys.messagebox_write('warning', msgs);
                self.onFailure(lb.getMsg('errRetrieveDataFromUrl'));
            }
        });

    },
    //---------- Filter ------- //

    // The initial filter initialization
    _iniFilter: function (params)
    {
        var self = this;
        var container = params.form;
        var id = '';
        var divBoxes;
        var ok;
        var reset;
        //------------------------------
        // Отобразим контейнер для фильтрации, если он есть
        if ($(container))
        {
            this.filterContainer = $(container);
            this.filterContainer.show();
        } else {
            return;
        }

        // Создадим список полей филтра
        divBoxes = $(container).select('div.filter-box');
        divBoxes.each(function (box) {
            // Получим ID фильтра
            id = box.readAttribute('id');
            if (id) {
                // Инициализация фильтра в DOM для ID
                self._iniFilterForID(id);
                // Инициализация FilterBox для ID
                self._iniFilterBoxForID(id);
            }
        })

        // Назначим события для фильтра
        ok = $(container).select('input[type="submit"]')[0];
        if (ok) {
            ok.observe('click', self.onFilterOkClick.bindAsEventListener(self));
        }

        reset = $(container).select('input[type="reset"]')[0];
        if (reset) {
            reset.observe('click', self.onFilterResetClick.bindAsEventListener(self));
        }
    },
    // Filter initialization for ID
    _iniFilterForID: function (id)
    {
        var self = this;
        var boxes;
        var add_filter;
        var delete_filter;
        var and_filter;
        var or_filter;
        //-------------------------
        boxes = {};
        boxes.hiden = false;
        boxes.andLogic = true;
        boxes.valueFilter_fields = lb.getMsg('msgSelectField');
        boxes['fields-input-' + id] = null;
        boxes['values-input-' + id] = null;
        boxes['compare-input-' + id] = null;
        self.boxFilterContainer.set(id, boxes);
        // Добавим события на кнопки добавить фильтр
        add_filter = $('add-' + id);
        if (add_filter.readAttribute('title')) {
            add_filter.observe('click', function (event) {

                // Отменим стандартное событие формы
                Event.stop(event);

                var element = Event.element(event);
                var myId = element.readAttribute('id');
                var ids = myId.split("_");
                var newId = 'filter_' + (ids[1] * 1 + 1);

                // если такой фильтр существует, то сделаем его видимым
                // если нет, то создадим его
                if (self.boxFilterContainer.get(newId)) {
                    Effect.Appear(newId, {
                        duration: 1.0
                    });
                    // Установим признак невидимости
                    self.boxFilterContainer.get(newId).hiden = false;
                } else {
                    // Создадим новый фильтр
                    self._createFilterBox(newId, id);
                    // Проинициализируем вновь созданный фильтр
                    self._iniFilterForID(newId);
                    // Проинициализируем FilterBox вновь созданного фильтра
                    self._iniFilterBoxForID(newId);
                }


            });
        }

        // Добавим события на кнопки удалить фильтр
        delete_filter = $('delete-' + id);
        if (delete_filter.readAttribute('title')) {
            delete_filter.observe('click', function (event) {
                var element = Event.element(event);
                var myId = element.readAttribute('id');
                var ids = myId.split("_");
                var newId = 'filter_' + ids[1];

                // Отменим стандартное событие формы
                Event.stop(event);

                Effect.Fade(newId, {
                    duration: 1.0
                });
                self.boxFilterContainer.get(newId).hiden = true;
            });
        }

        // Добавим события на кнопки логическое И
        and_filter = $('and-' + id);
        if (and_filter) {
            and_filter.observe('click', function (event) {
                var and_filter = Event.element(event);
                var myId = and_filter.readAttribute('id');
                var ids = myId.split("-");
                var or_filter = 'or-' + ids[1];
                // Отменим стандартное событие формы
                Event.stop(event);
                and_filter.toggle();
                $(or_filter).toggle();
                self.boxFilterContainer.get(ids[1]).andLogic = false;
            });
        }

        // Добавим события на кнопки логическое ИЛИ
        or_filter = $('or-' + id);
        if (or_filter) {
            or_filter.observe('click', function (event) {
                var or_filter = Event.element(event);
                var myId = or_filter.readAttribute('id');
                var ids = myId.split("-");
                var and_filter = 'and-' + ids[1];
                // Отменим стандартное событие формы
                Event.stop(event);
                or_filter.toggle();
                $(and_filter).toggle();
                self.boxFilterContainer.get(ids[1]).andLogic = true;
            });
        }
    },
    _createFilterBox: function (id, idElement) {
        var filterBox = '<div class="filter-box" id="#{id}" >' +
                '<input type="image" id="and-#{id}" src="' + lb.getMsg('urlRes') + '/images/system/AND.png" alt title="' + lb.getMsg('msgLogicAND') + '"> ' +
                '<input type="image" id="or-#{id}" src="' + lb.getMsg('urlRes') + '/images/system/OR.png" alt title="' + lb.getMsg('msgLogicOR') + '" style="display: none">' +
                '<input class="filter-input" type="text" id="fields-input-#{id}" /> ' +
                '<input class="filter-compare" type="text" id="compare-input-#{id}" /> ' +
                '<input class="filter-input" type="text" id="values-input-#{id}" /> ' +
                '<img src="' + lb.getMsg('urlRes') + '/images/system/search-indicator.gif" id="indicator-#{id}" style="display: none" alt="" /> ' +
                '<input type="image" id="add-#{id}" src="' + lb.getMsg('urlRes') + '/images/system/more_20x20.gif" alt title="' + lb.getMsg('msgAddFilter') + '"> ' +
                '<input type="image" id="delete-#{id}" src="' + lb.getMsg('urlRes') + '/images/system/delete_button.gif" alt title="' + lb.getMsg('msgDeleteFilter') + '"> ' +
                '</div>';
        var template = new Template(filterBox);
        var show = {
            id: id
        };
        // Получим шаблон с данными
        filterBox = template.evaluate(show);

        // Вставим новый фильтр в DOM
        $(idElement).insert({
            after: filterBox
        });
    },
    _iniFilterBoxForID: function (id) {
        var self = this;
        var boxFilter_fields;
        var boxFilter_values;
        var boxFilter_compare;
        var idFilter_fields = '';
        var idFilter_values = '';
        var idFilter_compare = '';
        var idFilter_indicator = '';
        //-------------------------

        idFilter_fields = 'fields-input-' + id;
        idFilter_values = 'values-input-' + id;
        idFilter_compare = 'compare-input-' + id;

        // Создадим ComboBox для полей таблицы
        boxFilter_fields = new MY.ComboBox({
            input: idFilter_fields,
            items: self._getParamsForFilter('fields'),
            initialText: lb.getMsg('msgSelectField'),
            onClickItem: function () {
                var idFilter_fields = 'fields-input-' + id;
                var idFilter_values = 'values-input-' + id;
                var idFilter_indicator = 'indicator-' + id;
                var idFilter_add = 'add-' + id;
                var myBoxFields = self.boxFilterContainer.get(id)[idFilter_fields];
                var myBoxValues = self.boxFilterContainer.get(id)[idFilter_values];
                var currentValue = myBoxFields.getValue();
                var oldValue = self.boxFilterContainer.get(id)['valueFilter_fields'];
                //--------------------------------------------
                // Обновим список для поиска значений в таблице
                if (oldValue !== currentValue) {
                    // Покажем индикатор ожидания
                    $(idFilter_add).hide()
                    $(idFilter_indicator).show();
                    // Обновим данные в значениях
                    myBoxValues.options.items = null;
                    myBoxValues.getAllChoices();
                    // Запомним новое текущее значение
                    self.boxFilterContainer.get(id)['valueFilter_fields'] = currentValue;

                    // Скроем индикатор ожидания через 1 сек.
                    var idTimeout = window.setTimeout(function () {
                        $(idFilter_add).show()
                        $(idFilter_indicator).hide();
                        window.clearTimeout(idTimeout);
                    }, 1000);
                }
            }
        });
        self.boxFilterContainer.get(id)[idFilter_fields] = boxFilter_fields;

        // Создадим ComboBox для значений таблицы
        boxFilter_values = new MY.ComboBox({
            input: idFilter_values,
            url: self.url + '/data',
            initialText: lb.getMsg('msgSelectValue'),
            parameters: {},
            getParameters: function () {
                var idFilter_fields = 'fields-input-' + id;
                var myBox = self.boxFilterContainer.get(id)[idFilter_fields];
                return self._getParamsForFilter('values', myBox);
            }
        });
        self.boxFilterContainer.get(id)[idFilter_values] = boxFilter_values;

        // Создадим ComboBox для сравнения поля и значения
        boxFilter_compare = new MY.ComboBox({
            input: idFilter_compare,
            items: self.itemsFilter_compare,
            initialText: ''
        });
        //            pair.value[idFilter_compare] = boxFilter_compare;
        self.boxFilterContainer.get(id)[idFilter_compare] = boxFilter_compare;
    },
    // Get params for filter
    _getParamsForFilter: function (type, boxFilter) {
        var self = this;
        var refMap;
        var fields = [];
        var field = '';
        var value = '';
        var params = {};
        var items;
        var objColumn;
        var cm = this.tableGrid.columnModel;
        //-----------------------------------

        // Получить список значений полей таблицы
        // для ComboBox - boxFilter_fields
        if (type == 'fields') {
            for (var i = 0; i < cm.length; i++) {
                if (cm[i].filter) {
                    fields.push({
                        value: cm[i].id,
                        text: cm[i].title
                    });
                }
            }
            return fields;
        }

        // Получить параметры для ajax
        // для ComboBox - boxFilter_values
        if (type == 'values') {
            field = boxFilter.getValue();
            if (field == lb.getMsg('msgSelectField')) {
                return {};
            } else {
                items = boxFilter.getItems()
                params.table = this.name;
                params.fields = {};
                params.type = 'filter';
                for (i = 0; i < items.length; i++) {
                    if (items[i]['text'] == field) {
                        field = items[i]['value'];
                        objColumn = this._getColumnObj(field);
                        if (objColumn.joinTable) {
                            params.fields[field] = objColumn.joinTable;
                        } else {
                            if (objColumn.referenceMap) {
                                refMap = objColumn.referenceMap;
                                params.fields[refMap.refColum] = refMap.refTable;
                            } else {
                                params.fields[field] = '';
                            }
                        }
                    }
                }
                params.fields = Object.toJSON(params.fields);
            }
            return params;
        }

        // Получить значение (value) поля таблицы
        // из значения (text)
        if (type == 'field') {
            field = boxFilter.getValue();
            if (field == lb.getMsg('msgSelectField')) {
                return '';
            } else {
                items = boxFilter.getItems()
                for (i = 0; i < items.length; i++) {
                    if (items[i]['text'] == field) {
                        return items[i]['value'];
                    }
                }
            }
            return '';
        }

        // Получить значение (value) значения поля таблицы
        // из значения (text)
        if (type == 'value') {
            value = boxFilter.getValue();
            if (value == lb.getMsg('msgSelectValue')) {
                return '';
            } else {
                items = boxFilter.getItems()
                for (i = 0; i < items.length; i++) {
                    if (items[i]['text'] == value) {
                        return items[i]['value'];
                    }
                }
            }
            return '';
        }

        // Получить параметры для ajax запроса
        // это обьект, в котором содержаться набор обьектов с ключами
        // определяющими название поля таблицы
        // каждый такой обьект содержит имя присоединенной таблицы
        // если оно есть и массив параметров фильтра:
        // куда входит
        // - признак логики -> andLogic : true,
        // - действие сравнения -> compare : "=",
        // - значение сравнения -> value : "user1"
        if (type == 'filter') {
            var ajaxParams = null;
            var bxFieldsValue = '';
            var joinTable = '';
            var joinTables;
            var bxValuesValue = '';
            var bxCompareValue = '';
            var noResult = false;
            var boxFilterContainer;
            //--------------------------

            boxFilterContainer = this.boxFilterContainer;
            boxFilterContainer.each(function (pair) {
                // Будем рассматривать только видимые поля ввода
                if (pair.value.hiden) {
                    return;
                }
                bxFieldsValue = pair.value['fields-input-' + pair.key].getValue();
                bxValuesValue = pair.value['values-input-' + pair.key].getValue();
                bxCompareValue = pair.value['compare-input-' + pair.key].getValue();

                // Определим что бы во все три поля ввода было введено значение
                noResult = (bxFieldsValue == lb.getMsg('msgSelectField')) ||
                        (bxValuesValue == lb.getMsg('msgSelectValue')) ||
                        (bxCompareValue == '') ||
                        (bxFieldsValue == '') ||
                        (bxValuesValue == '');
                if (noResult) {
                    return;
                }

                // Найдем значения фильтра и разместим их в параметрах ajax
                bxFieldsValue = self._getParamsForFilter('field', pair.value['fields-input-' + pair.key]);
                bxValuesValue = self._getParamsForFilter('value', pair.value['values-input-' + pair.key]);

                // Найдем обьект колонки таблицы
                objColumn = self._getColumnObj(bxFieldsValue);

                // Найдем имя таблицы, из которой получаем поле в записи
                // и переопределим название поля, если есть ссылка на другое поле
                if (objColumn.joinTable) {
                    joinTable = objColumn.joinTable;
                } else {
                    if (objColumn.referenceMap) {
                        refMap = objColumn.referenceMap;
                        joinTable = refMap.refTable;
                        joinTables = joinTable.split('.');
                        if (joinTables.length > 1) {
                            joinTable = joinTables[1];
                        }

                        // переопределим название поля
                        bxFieldsValue = refMap.refColum;
                    }
                }

                if (ajaxParams === null) {
                    ajaxParams = {};
                }
                if (!ajaxParams[bxFieldsValue]) {
                    ajaxParams[bxFieldsValue] = {
                        joinTable: joinTable,
                        filterParams: []
                    };

                }
                ajaxParams[bxFieldsValue]['filterParams'].push({
                    compare: bxCompareValue,
                    value: bxValuesValue,
                    andLogic: pair.value['andLogic']
                });
                joinTable = '';
            })

            return ajaxParams;
        }
        return null;
    },
    // Perform data filtering table
    onFilterOkClick: function (event) {
        Event.stop(event);
        var ajax_params = this._getParamsForFilter('filter');
        if (ajax_params === null) {
            ajax_params = {};
        }
        ajax_params = Object.toJSON(ajax_params);
        this.tableGrid.request.filter = ajax_params;
        // Перезагрузим текущую страницу
        this.reViewTable();

        // Обновим фильтры для подчиненных таблиц 
        // и перезагрузим их
        // Получим фильтр для глвной таблице, если он есть
        if (this.master && this.tableGrid.request['filter']) {
            var filter = this.tableGrid.request['filter'];
            this._updateFilterForSlaves(this.master, filter);
        }


        // Обновим список для поиска значений в таблице
        if (this.boxSearch) {
            this.boxSearch.options.items = null;
            this.boxSearch.getAllChoices();
        }

    },
    // Clean the filter for table
    onFilterResetClick: function (event) {

        var boxFilterContainer;
        //--------------------------
        // Отменим стандартное событие формы
        Event.stop(event);

        // Очистим элементы формы
        var form = $(this.paramsFilter.form)
        var inputs = form.getInputs('text') // -> only text inputs
        inputs.invoke('clear');

        // Фактически очистим ComboBox
        boxFilterContainer = this.boxFilterContainer;
        boxFilterContainer.each(function (pair) {
            //pair.value.clear = true;
            pair.value['fields-input-' + pair.key].oldElementValue = '';
            pair.value['values-input-' + pair.key].oldElementValue = '';
            pair.value['compare-input-' + pair.key].oldElementValue = '';
        })
    },
    //=========== Sort ===========//

    // Update request for sorting
    updateRequestForSort: function () {
        var objColumn;
        var refMap;
        var joinTable = '';
        var joinTables;
        var sortCol = '';
        var tg = this.tableGrid;

        //------------------------
        if (tg.request[tg.sortColumnParameter]) {
            sortCol = tg.request[tg.sortColumnParameter];
            objColumn = this._getColumnObj(sortCol);
            if (!objColumn) {
                return;
            }
            if (objColumn.joinTable) {
                joinTable = objColumn.joinTable;
            } else {
                if (objColumn.referenceMap) {
                    refMap = objColumn.referenceMap;
                    joinTable = refMap.refTable;
                    joinTables = joinTable.split('.');
                    if (joinTables.length > 1) {
                        joinTable = joinTables[1];
                    }

                    // переопределим колонку сортировки 
                    tg.request[tg.sortColumnParameter] = refMap.refColum;
                }
            }
            tg.request['joinTableForSort'] = joinTable;
        }
    },
    // Get the name of the table
    _getJoinTableForSort: function () {
        var objColumn;
        var refMap;
        var joinTable = '';
        var joinTables;
        var sortCol = '';
        var tg = this.tableGrid;
        //------------------------
        if (tg.request[tg.sortColumnParameter]) {
            sortCol = tg.request[tg.sortColumnParameter];
            objColumn = this._getColumnObj(sortCol);
            if (objColumn.joinTable) {
                joinTable = objColumn.joinTable;
            } else {
                if (objColumn.referenceMap) {
                    refMap = objColumn.referenceMap;
                    joinTable = refMap.refTable;
                    joinTables = joinTable.split('.');
                    if (joinTables.length > 1) {
                        joinTable = joinTables[1];
                    }
                }
            }
        }
        return joinTable;
    },
    //=========== Date ===========//

    // Get the date in the format ISO_8601 (yyyy-MM-dd)
    _getDate: function (date, format) {
        var value;
        if (date) {
            value = date.format(format);
        } else {
            value = '';
        }
        return value;
    },
    
    // Get date from the UNIX format
    _getDateFromUnix: function (timestamp, format) {
        var d = new Date(timestamp * 1000);
        return d.format(format);
    },
    // Get localized date format
    _getDateLocalFormat: function (onlyDate) {
        var format = '';
        switch (lb.getMsg('languageSite')) {
            case 'uk':
            case 'ru':
                if (onlyDate)
                    format = 'dd.MM.yyyy';
                else
                    format = 'dd.MM.yyyy HH:mm:ss';
                break;
            case 'en':
                if (onlyDate)
                    format = 'MM.dd.yyyy';
                else
                    format = 'MM.dd.yyyy HH:mm:ss';

                break;
            default:
                break;
        }

        return format;
    },
    // Calendar localization
    _localDateCalendar: function () {
        Date.MONTH_NAMES = $w(i18n.getMessage('date.month.names'));
        Date.MONTH_ABBREVIATIONS = $w(i18n.getMessage('date.month.abbreviations'));
        Date.DAY_NAMES = $w(i18n.getMessage('date.day.names'));
        Date.DAY_ABBREVIATIONS = $w(i18n.getMessage('date.day.abbreviations'));
        Date.WEEK_DAYS = $w(i18n.getMessage('date.week.days'));
        Date.FIRST_DAY_OF_WEEK = 1;
    },
    //============== Synchronization slave tables ===========//

    // Update data lists for slave tables
    _updateDataForSlaves: function (master) {
        if (master === null) {
            return
        }
        var self = this;
        var tables = scriptInstances.get('MyTableGrid');
        tables.each(function (table) {
            if (table.slave && table.slave === master && table.actual) {
                // Обновим списки если нужно и повторно отобразим таблицу
                if (table.getParamsForAjaxData()) {
                    table.retrieveAjaxData();
                } else {
                    table.reViewTable();
                }

                // Если подчиненная таблица является еще и главной для других таблиц
                // повторим цикл обновления списков данных для ее подчиненных таблиц
                if (table.master) {
                    self._updateDataForSlaves(table.master);
                }
            }
        })
    },
    // Update filter in slave tables from the master table
    _updateFilterForSlaves: function (master, filter) {
        if (master === null) {
            return;
        }
        var self = this;
        var tables = scriptInstances.get('MyTableGrid');
        tables.each(function (table) {
            if (table.slave && table.slave === master && table.actual) {
                // Обновим фильтр и повторно отобразим таблицу
                table.tableGrid.request['filter'] = filter;
                table.reViewTable();

                // Если подчиненная таблица является еще и главной для других таблиц
                // повторим цикл обновления фильтра для ее подчиненных таблиц
                if (table.master) {
                    self._updateFilterForSlaves(table.master, filter);
                }
            }
        })
    },
    // Update filter in slave table from the master table
    _updateFilterForSlave: function (slave) {
        if (slave === null) {
            return;
        }
        var self = this;
        var tables = scriptInstances.get('MyTableGrid');
        tables.each(function (table) {
            if (table.master && table.master === slave && table.tableGrid) {
                // Обновим фильтр и повторно отобразим таблицу
                self.tableGrid.request['filter'] = table.tableGrid.request['filter'];
            }
        })
    },
    //============== ComboBox ===========//

    // Update list in the ComboBox
    _updateItemsForComboBox: function () {
        var editor;
        var id = '';
        var items;

        var cm = this.tableGrid.columnModel;
        //----------------------------------
        for (var i = 0; i < cm.length; i++) {
            id = cm[i].id;
            editor = cm[i].editor || null;
            if (editor && editor instanceof MY.ComboBox) {
                if (cm[i].referenceMap) {
                    id = cm[i].referenceMap.refColum;
                }
                items = this.itemsComboBox.get(id);
                editor.options.items = items;
            }
        }
    },
    // Transform the data received through Ajax 
    // depending on the type of the data
    _prepAjaxData: function (column_id, listAjaxData) {
        var typeAjaxData;
        var newList = [];
        var objColumn = this._getColumnObj(column_id);
        //------------------------------
        if (objColumn && objColumn.ajaxdata) {
            typeAjaxData = objColumn.ajaxdata;
            switch (typeAjaxData) {
                case 'selfFieldTable':
                    listAjaxData.each(function (data) {
                        newList.push({
                            value: data.text,
                            text: data.text
                        });
                    })
                    break;
                default:
                    break;
            }
        }
        if (newList.length > 0) {
            return newList;
        } else {
            return listAjaxData;
        }

    },
    // Get value for ComboBox
    _getValueForComboBox: function (params) {
        var newValue = '';
        var objColumn;
        var refMap;
        var myParams = {
            field: params.field,
            value: params.value
        };
        //---------------------

        // Изменим название поля в params, если это поле иммеет ссылку на другое поле
        objColumn = this._getColumnObj(myParams.field);
        if (objColumn && objColumn.referenceMap) {
            refMap = objColumn.referenceMap;
            myParams.field = refMap.refColum;
        }

        // Проверим значение для ComboBox field
        // если это список значений, то проверим только значение - value
        // значение - text игнорируем!!!
        if (this.itemsComboBox.get(myParams.field)) {
            var lists = this.itemsComboBox.get(myParams.field);
            lists.each(function (list) {
                if (list.text == myParams.value) {
                    newValue = list.value;
                    return;
                }
            });
        }
        if (newValue) {
            return newValue;
        } else {
            return myParams.value;
        }
    },
    //========== Table ===========//

    // Get column object from columnModel by ID column
    _getColumnObj: function (column_id) {
        var cm = this._getTableModel().columnModel;
        var id = '';
        var childrens;
        var length = cm.length;
        //-----------------
        for (var i = 0; i < length; i++) {
            id = cm[i].id;
            if (cm[i].children) {
                childrens = cm[i].children;
                for (var j = 0; j < childrens.length; j++) {
                    id = childrens[j].id;
                    if (id == column_id) {
                        return childrens[j];
                    }

                }
            } else {
                id = cm[i].id;
                if (id == column_id) {
                    return cm[i];
                }

            }
        }
        return null;
    },
    // Search the DOM element for a column -> id and value (ex. id=21)
    _getCellElementAtValue: function (id, value) {
        var rowIndex = -1;
        var y = 0;
        for (var i = 0; i < this.tableGrid.rows.length; i++) {
            if (this.tableGrid.rows[i][id] == value) {
                rowIndex = i;
                break;
            }
        }
        if (rowIndex >= 0) {
            y = this.tableGrid.getIndexOf(id);
            return $('mtgC' + this.tableGrid._mtgId + '_' + y + ',' + rowIndex);
        } else {
            return null;
        }
    },
    // Re-mapping table
    reViewTable: function (page) {
        // Отобразим измененную таблицу на текущей странице
        if (page === undefined) {
            var pager = this.tableGrid.pager;
            page = pager.currentPage;
        }
        this.tableGrid._retrieveDataFromUrl(page, false);
    },
    //========== ACCORDION ===========//

    // Subscribe to the events in the accordion
    _subscribeAccordionEvents: function () {
        var indexSection = this.accordion.section;
        var section;
        var idTimeout;
        //-----------------------------
        var boxAccordions = scriptInstances.get('AccordionBox');
        boxAccordions.each(function (box) {
            if (box.id == this.accordion.id) {

                box.onHiddenSectionEvent.push({
                    client: this,
                    handlerEvent: this.onHiddenSectionEvent
                });

                box.onShownSectionEvent.push({
                    client: this,
                    handlerEvent: this.onShownSectionEvent
                });

                // Получим соответсвующую секцию и откроем ее
                if (this.accordion.show) {
                    idTimeout = window.setTimeout(function () {
                        section = box.accordion.sections[indexSection];
                        box.accordion.showSection(section);
                        window.clearTimeout(idTimeout);
                    }, 300);
                }
            }
        }.bind(this))
    },
    // Hidden section of the accordion
    onHiddenSectionEvent: function (self, params) {
        var section = params.section.elements.section;
        var hrefSection = section.down('a').readAttribute('href');
        if (hrefSection == self.accordion.section) {
            self.actual = false;
        }
    },
    // Show section in the accordion
    onShownSectionEvent: function (self, params) {
        var section = params.section.elements.section;
        var hrefSection = section.down('a').readAttribute('href');
        if (hrefSection == self.accordion.section) {
            self.actual = true;

            // Обновим данные в таблице или создадим таблицу
            if (self.tableGrid) {
                self._updateFilterForSlave(self.slave);
                // Обновим списки если нужно и повторно отобразим таблицу
                if (self.getParamsForAjaxData()) {
                    self.retrieveAjaxData();
                } else {
                    self.reViewTable();
                }
            } else {
                // Получим данные по URL или создадим таблицу
                if (self.getParamsForAjaxData()) {
                    self.retrieveAjaxData();
                } else {
                    self._createTable();
                }
                // Инициализация подсказок
                if (self.tooltipContainer) {
                    self._iniTooltip();//.bind(self);
                }
            }
        }
    },
    //---------- Additional functions ------- //

    _toggleProgressBarOverlay: function (text) {
        var etalon = 15;
        var count = text.length;
        var id = this.tableGrid._mtgId;
        var overlayDiv = $('overlayDiv' + id);

        var overlayBox = overlayDiv.down('.loadingBox');

        if (overlayDiv.getStyle('visibility') == 'hidden') {

            // Установим длину блока, в зависимости от длины текста
            // и установим сам текст
            if (overlayBox) {
                if (count > etalon) {
                    var delta = (count - etalon) * 6;
                    overlayBox.setStyle({
                        width: (110 + delta) + 'px'
                    });
                }
                overlayBox.innerHTML = text;
            }

            overlayDiv.setStyle({
                visibility: 'visible'
            });
        } else {
            overlayDiv.setStyle({
                visibility: 'hidden'
            });
        }
    },
    //---------- ERRORS ------- //

    _clearListErrors: function () {
        var self = this;
        //Очистим сообщение об ошибке
        BSA.Sys.messagebox_clear();

        // Очистим список ошибок
        self.listError.each(function (err) {
            self.listError.unset(err.key);
        });
    },

    onFailure: function (message) {
        var msgs;
        if (message.class_message) {
            //Очистим сообщение об ошибке
            BSA.Sys.messagebox_clear();
            msgs = message.messages;
            BSA.Sys.messagebox_write(message.class_message, msgs);
        } else {
            BSA.Sys.err_message_write(message);
        }

    }

});

// The function is executed after the download of the browser window
// are created objects, which are entered in the list of instances
// ex. $H(MyTableGrid: [new MyTableGrid(param1), ... ,new MyTableGrid(paramN)])
BSA.MyTableGrid.RegRunOnLoad = function () {
    // Получим параметры для создания обьекта
    var params = scriptParams.get('MyTableGrid');
    // Ф-ия создания обьектов по их параметрам
    var createObject = function (param) {
        var grid = scriptInstances.get('MyTableGrid');
        if (grid) {
            grid.push(new BSA.MyTableGrid(param));
        } else {
            scriptInstances.set('MyTableGrid', [new BSA.MyTableGrid(param)]);
        }
    };
    // Создание обьектов
    if (params) {
        params.each(function (param) {
            createObject(param);
        });
    } else {
        createObject();
    }
};
runOnLoad(BSA.MyTableGrid.RegRunOnLoad);