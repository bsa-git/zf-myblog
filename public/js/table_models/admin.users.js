/**
 * BSA.iniAdminUsers - Function
 *
 * С помощью этой ф-ии инициализируется структура таблицы пользователей сайта
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

BSA.iniAdminUsers = function()
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
            title: ' ',//lb.getMsg('titleTableUsers'),
            addSettingBehavior : false,
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
            onCellBlur : function(element, value, x, y, id) {
                value = element.innerText.strip();
            },
            onCellFocus : function(element, value, x, y, id) {
            },
            toolbar : {
                elements: [MY.TableGrid.ADD_BTN, MY.TableGrid.DEL_BTN, MY.TableGrid.SAVE_BTN],
                elements_user: [],
                elementsReportMenu: ['ReportHTML','ReportPDF'],
                    
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

                    if (confirm(lb.getMsg('msgAttentionDeleteSelectedRowsFromTable'))){
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
            width : 110,
            editable: true,
            editor: 'checkbox'
        },
        {
            id: 'credentials',
            title: lb.getMsg('columnCredentials'),
            children : [
            {
                id : 'username',
                title : lb.getMsg('columnUserLogin'),
                filter: true,
                required : true,
                width : 70,
                editable: true,
                validators: {
                    alnum:{
                        required : true,
                        allowWhiteSpace: false,
                        unicodeEnabled: false
                    }
                },
                editor: new MY.TextField({
                    validate: function(value, errors) {
                        self._validateValueNoAjax({
                            table: self.name,
                            field: 'username',
                            value: value
                        });
                        return true;
                    }
                })
            },
            {
                id : 'user_type',
                title : lb.getMsg('columnUserType'),
                required : true,
                filter: true,
                ajaxdata : self.ajaxData.customFieldTable,
                width : 100,
                editable: true,
                editor: new MY.ComboBox({
                    items: self.itemsComboBox.get('user_type'),
                    validate : function(value, errors) {
                        self._validateValue({
                            table: self.name,
                            field: 'user_type',
                            value: value
                        });
                        return true;
                    }
                })
            },
            {
                id : 'password',
                title : lb.getMsg('columnUserPassword'),
                required : true,
                width : 220,
                editable: true,
                editor: new MY.TextField({
                    validate: function(value, errors) {
                        self._validateValue({
                            table: self.name,
                            field: 'password',
                            value: value
                        });
                        return true;
                    }
                })
            },
            {
                id : 'email',
                title : lb.getMsg('columnEmail'),
                joinTable: 'users_profile',
                required : true,
                width : 180,
                editable: true,
                editor: new MY.TextField({
                    validate : function(value, errors) {
                        self._validateValue({
                            table: self.name,
                            field: 'email',
                            value: value
                        });
                        return true;
                    }
                })
            },
            {
                id : 'last_name',
                title : lb.getMsg('columnLastName'),
                joinTable: 'users_profile',
                search : true,
                width : 170,
                editable: true,
                editor: new MY.TextField({
                    validate : function(value, errors) {
                        self._validateValue({
                            table: self.name,
                            field: 'last_name',
                            value: value
                        });
                        return true;
                    }
                })
            },
            {
                id : 'first_name',
                title : lb.getMsg('columnFirstName'),
                joinTable: 'users_profile',
                search : true,
                width : 140,
                editable: true,
                editor: new MY.TextField({
                    validate : function(value, errors) {
                        self._validateValue({
                            table: self.name,
                            field: 'first_name',
                            value: value
                        });
                        return true;
                    }
                })
            }
            ]
        },

        {
            id: 'public_information',
            title: lb.getMsg('columnPublicInformation'),
            children : [
            {
                id : 'blog_public',
                title : lb.getMsg('columnBlogPublic'),
                type: 'boolean',
                joinTable: 'users_profile',
                width : 220,
                editable: true,
                editor: 'checkbox'
            },
            {
                id : 'num_posts',
                title : lb.getMsg('columnNumPosts'),
                type: 'number',
                joinTable: 'users_profile',
                width : 220,
                editable: true,
                editor: new MY.TextField({
                    validate : function(value, errors) {
                        self._validateValue({
                            table: self.name,
                            field: 'num_posts',
                            value: value
                        });
                        return true;
                    }
                })
            },
            {
                id : 'public_last_name',
                title : lb.getMsg('columnLastName'),
                joinTable: 'users_profile',
                width : 170,
                editable: true,
                editor: new MY.TextField({
                    validate : function(value, errors) {
                        self._validateValue({
                            table: self.name,
                            field: 'public_last_name',
                            value: value
                        });
                        return true;
                    }
                })
            },
            {
                id : 'public_first_name',
                title : lb.getMsg('columnFirstName'),
                joinTable: 'users_profile',
                width : 140,
                editable: true,
                editor: new MY.TextField({
                    validate : function(value, errors) {
                        self._validateValue({
                            table: self.name,
                            field: 'public_first_name',
                            value: value
                        });
                        return true;
                    }
                })
            },
            {
                id : 'public_email',
                title : lb.getMsg('columnEmail'),
                joinTable: 'users_profile',
                width : 180,
                editable: true,
                editor: new MY.TextField({
                    validate : function(value, errors) {
                        self._validateValue({
                            table: self.name,
                            field: 'public_email',
                            value: value
                        });
                        return true;
                    }
                })
            },
            {
                id : 'public_home_phone',
                title : lb.getMsg('columnHomePhone'),
                joinTable: 'users_profile',
                width : 150,
                editable: true,
                editor: new MY.TextField({
                    validate : function(value, errors) {
                        self._validateValue({
                            table: self.name,
                            field: 'public_home_phone',
                            value: value
                        });
                        return true;
                    }
                })
            },
            {
                id : 'public_work_phone',
                title : lb.getMsg('columnWorkPhone'),
                joinTable: 'users_profile',
                width : 130,
                editable: true,
                editor: new MY.TextField({
                    validate : function(value, errors) {
                        self._validateValue({
                            table: self.name,
                            field: 'public_work_phone',
                            value: value
                        });
                        return true;
                    }
                })
            },
            {
                id : 'public_mobile_phone',
                title : lb.getMsg('columnMobilePhone'),
                joinTable: 'users_profile',
                width : 150,
                editable: true,
                editor: new MY.TextField({
                    validate : function(value, errors) {
                        self._validateValue({
                            table: self.name,
                            field: 'public_mobile_phone',
                            value: value
                        });
                        return true;
                    }
                })
            }
            ]
        },

        {
            id: 'date',
            title: lb.getMsg('columnDate'),
            children : [
            {
                id : 'ts_created',
                title : lb.getMsg('columnUserTsCreated'),
                width : 100,
                editable: true,
                editor: new MY.DatePicker({
                    format : 'yyyy-MM-dd',
                    yearRange: 10,
                    validate : function(date, errors) {
                        var value  = self._getDate(date, 'yyyy-MM-dd');
                        self._validateValue({
                            table: self.name,
                            field: 'ts_created',
                            value: value
                        });
                        return true;
                    }
                })
                    
            },
            {
                id : 'ts_last_login',
                title : lb.getMsg('columnUserTsLastLogin'),
                filter: true,
                width : 160,
                editable: false
                   
            }
            ]
        }
        ]
    };
    return tableModel;
};
