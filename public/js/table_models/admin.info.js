/**
 * BSA.iniAdminInfo - Function
 *
 * С помощью этой ф-ии инициализируется структура таблицы информационной помощи
 *
 * JavaScript
 *
 * Copyright (c) 2012 Бескоровайный Сергей
 *
 * @author     Бескоровайный Сергей <bs261257@gmail.com>
 * @copyright  2012 Бескоровайный Сергей
 * @license    BSD
 * @version    1.00.00
 * @link       http://my-site.com/web
 */

BSA.iniAdminInfo = function()
{
    var self = this;
    var myURL = this.url + '/rows';

    var tableModel = {
        name: self.name,
        url: myURL,
        request: {
            table: self.name,
            rowsByPage: self.rowsByPage
        },
        updateRequestForSort: function(){
            self.updateRequestForSort();
        },
        options : {
            title: ' ',//lb.getMsg('titleTableBlogs'),
            addSettingBehavior : true,
            addReportMenu : true,
            pager: {
                pageParameter : 'page'
            },
            onFailure : function(transport) {
                BSA.Sys.getJsonResponse(transport, true);
            },
            afterRender : function() {

                // Сделаем задержку выполнения ф-ии
                // для загрузки всех элементов DOM
                self.idTimeout = window.setTimeout(function() {
                    if(this.idSearch){
                        // Получим ячейку на которую мы хотим установить фокус
                        var element = this._getCellElementAtValue('id', this.idSearch);

                        //Установим фокус
                        if(element){
                            this.tableGrid.keys.setFocus(element);
                            this.tableGrid.keys.eventFire("focus", element);
                        }
                        // Сбросим ID найденой записи
                        this.idSearch = 0;
                    }
                    // Обработка строк
                    this.RowsProcessing();
                    
                    window.clearTimeout(this.idTimeout);


                }.bind(self), 500);

            },
            toolbar : {
                elements: [MY.TableGrid.ADD_BTN, MY.TableGrid.DEL_BTN, MY.TableGrid.SAVE_BTN],
                elements_user: ['EditButton'],
                elementsReportMenu: ['ReportHTML','ReportPDF'],
                    
                onEditButton: function() {
                    var infoManager = null;
                    //--------------------
                    // Получим обьект управления инф. помощью
                    if(scriptInstances.get('BlogInfoManager')){
                        infoManager = scriptInstances.get('BlogInfoManager')[0];
                    }
                    
                    var indPosition = self.tableGrid.getCurrentPosition();
                    var row = self.tableGrid.getRow(indPosition[1]);
                    var info_id = row['id'];
                    if(! info_id){
                        alert(lb.getMsg('errYouMustSaveRecordAndThenEditIt') + "!"); 
                        return;
                    }
                    
                    // Закроем окно, если оно открыто
                    if(infoManager.win){
                        infoManager.win.close();
                        infoManager.win = null;
                    }
                    
                    // Загрузим значения инф. помощи
                    var info_key = row['info_key'];
                    var params = {
                        my_action: 'load',
                        info_key: info_key
                    }
                    infoManager.loadInfo(params);
                },
                    
                onReportPDF: function() {
                    self.onReportPDF();
                },
                    
                onReportHTML: function() {
                    self.onReportHTML();
                },
                    
                onSave: function() {
                    var rowsModified = self.tableGrid.getModifiedRows();
                    var rowsAdded = self.tableGrid.getNewRowsAdded();
                    var isRowsModifiedAndAdded = rowsModified.length && rowsAdded.length;

                    // Проверим есть ли измененные или добавленные значения
                    if(rowsModified.length == 0 && rowsAdded.length == 0){
                        var msg = lb.getMsg('errDataStorageNotPossible')+'\n'+lb.getMsg('errNoChangesOrAddedRows');
                        alert(msg);
                        return;
                    }

                    if(isRowsModifiedAndAdded){
                        if (confirm(lb.getMsg('msgRowsModifiedAndAdded') + '\n' + lb.getMsg('msgSaveChangedValuesToDataBase') + '?')){
                            self.onSaveValues('modified');
                            return;
                        }
                        else
                            return;
                    }

                    if(rowsModified.length){
                        if (confirm(lb.getMsg('msgSaveChangedValuesToDataBase') + '?')){
                            self.onSaveValues('modified');
                            return;
                        }
                        else
                            return;
                    }

                    if(rowsAdded.length){
                        if (confirm(lb.getMsg('msgSaveAddedValuesToDataBase') + '?')){
                            self.onSaveValues('add');
                            return;
                        }
                        else
                            return;
                    }
                },
                onAdd: function() {
                    return confirm(lb.getMsg('msgAddNewRowToTable') + '?');
                },
                onDelete: function() {
                    var rowsSelected = self.tableGrid.getSelectedRowsByColumn('_nn_');

                    // Проверим есть ли выбранные строки для удаления
                    if(! rowsSelected.length){
                        var msg = lb.getMsg('errDataDeleteNotPossible')+'\n'+lb.getMsg('errDoNotSelectRowsToDelete');
                        alert(msg);
                        return false;
                    }

                    if (confirm(lb.getMsg('msgDeleteSelectedRowsFromTable') + '?')){
                        self.onDeleteValues();
                        return false;
                    }
                    else
                        return  false;
                }
                    
            },
            rowClass : function(rowIdx) {
                var className = '';
                if (rowIdx % 2 == 0) {
                    className = 'hightlight';
                }

                // Очистить список ошибок
                self._clearListErrors();

                return className;
            }
        },
        columnModel : [
        {
            id : '_nn_',
            title : '#',
            width : 30,
            editable: true,
            sortable: false,
            editor: new MY.TableGrid.CellCheckbox({
                selectable : true
            })
        },
        {
            id : 'id',
            title : 'No',
            type: 'number',
            width : 50,
            editable: false
        },
        {
            id : 'actual',
            title : lb.getMsg('columnActual'),
            type: 'boolean',
            width : 100,
            editable: true,
            editor: 'checkbox'
        },
        {
            id : 'info_key',
            title : lb.getMsg('titleTableInfoKey'),
            width : 350,
            editable: true,
            editor: new MY.TextField({
                validate: function(value, errors) {
                    self._validateValue({
                        table: self.name,
                        field: 'info_key',
                        value: value
                    });
                    return true;
                }
            })
        },
        {
            id : 'title_info',
            title : lb.getMsg('titleTableTitleInfo'),
            required : true,
            search : true,
            tooltip: true,
            width : 350,
            editable: true,
            editor: new MY.TextField({
                validate: function(value, errors) {
                    self._validateValue({
                        table: self.name,
                        field: 'title_info',
                        value: value
                    });
                    return true;
                }
            })
        }

        ]
    };
    return tableModel;
};
