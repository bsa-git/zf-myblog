/**
 * BSA.iniAdminUsers - Function
 *
 * С помощью этой ф-ии инициализируется структура таблицы изображений блогов
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

BSA.iniAdminPostImages = function()
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
            title: ' ',//lb.getMsg('titleTablePostImages'),
            addSettingBehavior : true,
            pager: {
                pageParameter : 'page'
            },
            onFailure : function(transport) {
                BSA.Sys.getJsonResponse(transport, true);
//                self.onFailure(errText);
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
                //                    elements: [MY.TableGrid.SAVE_BTN],
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
            id : 'title',
            title : lb.getMsg('columnPostName'),
            joinTable: 'blog_posts_profile', 
            tooltip: true,
            width : 220,
            editable: false
        },

        {
            id : 'post_id',
            title : lb.getMsg('columnPostNameUrl'),
            ajaxdata : self.ajaxData.refKeyTable,
            required : true,
            referenceMap: {
                refTable: 'blog_posts',
                refColum: 'url'
            },
            width : 220,
            editable: true,
            editor: new MY.ComboBox({
                items: self.itemsComboBox.get('url'),
                validate : function(value, errors) {
                    var params = {
                        table: self.name,
                        field: 'post_id',
                        value: value
                    };

                    var validateValue = self._getValueForComboBox(params);
                    params.value = validateValue;
                    self._validateValue(params);
                    return true;
                }
            })

        },
        {
            id : 'filename',
            title : lb.getMsg('columnPostFileImage'),
            required : true,
            width : 200,
            editable: false
        },
        {
            id : 'comment',
            title : lb.getMsg('columnPostImageComment'),
            tooltip: true,
            width : 320,
            editable: true,
            editor: new MY.TextField({
                validate: function(value, errors) {
                    self._validateValue({
                        table: self.name,
                        field: 'comment',
                        value: value
                    });
                    return true;
                }
            })
        },
        {
            id : 'ranking',
            title : lb.getMsg('columnPostRankingImage'),
            type: 'number',
            width : 180,
            editable: false
        }
        ]
    };
    return tableModel;
};
