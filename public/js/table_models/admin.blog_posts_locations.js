/**
 * Function - iniAdminPostLocations
 * initialized structure of locations table
 *
 * JavaScript
 *
 * @author   Sergii Beskorovainyi <bsa2657@yandex.ru>
 * @license  MIT <http://www.opensource.org/licenses/mit-license.php>
 * @link     https://github.com/bsa-git/zf-myblog/
 */

BSA.iniAdminPostLocations = function()
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
            id : 'longitude',
            title : lb.getMsg('columnPostLocationLongitude'),
            joinTable: 'blog_posts_locations',
            type: 'number',
            required : true,
            width : 100,
            editable: false

        },
        {
            id : 'latitude',
            title : lb.getMsg('columnPostLocationLatitude'),
            type: 'number',
            required : true,
            width : 100,
            editable: false
        },
        {
            id : 'description',
            title : lb.getMsg('columnPostLocationDescription'),
            tooltip: true,
            width : 320,
            editable: true,
            editor: new MY.TextField({
                validate: function(value, errors) {
                    self._validateValue({
                        table: self.name,
                        field: 'description',
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
