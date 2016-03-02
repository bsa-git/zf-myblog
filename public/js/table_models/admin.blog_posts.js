/**
 * Function - iniAdminPosts
 * initialized structure of blogs table
 *
 * JavaScript
 *
 * @author   Sergii Beskorovainyi <bsa2657@yandex.ru>
 * @license  MIT <http://www.opensource.org/licenses/mit-license.php>
 * @link     https://github.com/bsa-git/zf-myblog/
 */

BSA.iniAdminPosts = function()
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
            title: ' ',
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
                    var indPosition = self.tableGrid.getCurrentPosition();
                    var row = self.tableGrid.getRow(indPosition[1]);
                    var user_id = row['user_id'];
                    var username = '';
                    self.itemsComboBox.get('username').each(function(item){
                        if(item.value == user_id){
                            username = item.text;
                        }
                    });
                    var params = {
                        username: username,
                        password: username, 
                        title: row.title,
                        url: row.url,
                        post_id: row.id
                    }
                        
                    BSA.Utilities.loginUserToEditMessage(params);
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
            width : 110,
            editable: true,
            editor: 'checkbox'
        },
        {
            id : 'user_id',
            title : lb.getMsg('columnUserLogin'),
            filter: true,
            ajaxdata : self.ajaxData.refKeyTable,
            referenceMap: {
                refTable: 'admin.users',
                refColum: 'username'
            },
            width : 90,
            editable: true,
            editor: new MY.ComboBox({
                items: self.itemsComboBox.get('username'),
                validate : function(value, errors) {
                    var params = {
                        table: self.name,
                        field: 'user_id',
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
            id : 'title',
            title : lb.getMsg('columnPostName'),
            search : true,
            filter: true,
            tooltip:true,
            joinTable: 'blog_posts_profile',
            required : true,
            width : 180,
            editable: true,
            editor: new MY.TextField({
                validate: function(value, errors) {
                    self._validateValue({
                        table: self.name,
                        field: 'title',
                        value: value
                    });
                    return true;
                }
            })
        },
        {
            id : 'url',
            title : lb.getMsg('columnPostNameUrl'),
            filter: true,
            width : 180,
            editable: false
        },
        {
            id : 'status',
            title : lb.getMsg('columnPostStatus'),
            required : true,
            ajaxdata : self.ajaxData.customFieldTable,
            filter: true,
            width : 110,
            editable: true,
            editor: new MY.ComboBox({
                items: self.itemsComboBox.get('status'),
                validate : function(value, errors) {
                    var params = {
                        table: self.name,
                        field: 'status',
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
            id : 'ts_created',
            title : lb.getMsg('columnDate') + ' ' + lb.getMsg('columnPostTsCreated'),
            filter: true,
            width : 120,
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
            id : 'ts_published',
            title : lb.getMsg('columnDate') + ' ' + lb.getMsg('columnPostTsPublished'),
            joinTable: 'blog_posts_profile',
            width : 140,
            editable: true,
            editor: new MY.DatePicker({
                format : 'yyyy-MM-dd HH:mm:ss',
                yearRange: 10,
                validate : function(date, errors) {
                    var value  = self._getDate(date, 'yyyy-MM-dd HH:mm:ss');
                    self._validateValue({
                        table: self.name,
                        field: 'ts_published',
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
