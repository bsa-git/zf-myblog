/**
 * Class - AdminTools
 * using the class can:
 *  - make an Ajax request to restore the search index
 *
 * JavaScript
 *
 @author   Sergii Beskorovainyi <bsa2657@yandex.ru>
 * @license  MIT <http://www.opensource.org/licenses/mit-license.php>
 * @link     https://github.com/bsa-git/zf-myblog/
 */
BSA.AdminTools = Class.create({

    container : null,
    
    // Object initialization
    initialize : function(params)
    {
        //-----------------------------
        // Контейнер для вывода сообщений пользователей
        if($(params.container)){
            this.container = $(params.container);
        }
        
        // Установим события для работы с поисковым индексом 
        if($$('a.search_lucene-index')){
            $$('a.search_lucene-index').each(function(searchIndex){
                searchIndex.observe('click', this.onSearchIndex_Click.bind(this));
            }.bind(this))
            
        }
        
    },

    onSearchIndex_Click : function(event)
    {
        //------------------
        // Остановим распространение события
        Event.stop(event);
        
        var indexSearch = Event.element(event).up('a');

        //Очистим сообщение об ошибке
        BSA.Sys.messagebox_clear();
        
        // Откроем диалог
        BSA.Dialogs.openDialogInfo({
            type: 'WaiteServerAction',
            msg: indexSearch.title//lb.getMsg('msgRestoringSearchIndex') 
        });
        // Получим параметры для Ajax
        var params =  {};

        // Инициализация Ajax запроса
        new Ajax.Request(indexSearch.href, {
            parameters: params,
            // Успешный ответ
            onSuccess: function(response) {
                try {
                    // Получим данные ответа
                    var json = BSA.Sys.getJsonResponse(response, true);
                    
                    // Проверим есть ли ошибки
                    if (json.result) {// ОК
                        BSA.Sys.message_write(lb.getMsg('msgRestoredSearchIndexOK'));
                    }
                } catch (ex) {
                    if (ex instanceof Error) { // Это экземпляр Error или подкласса?
                        this.onFailure(ex.name + ": " + ex.message);
                    }

                } finally {
                    // Очистим сообщения
                    BSA.Sys.message_clear();
                    // Закроем диалог
                    BSA.Dialogs.closeDialogInfo();
                }
            }.bind(this)
        });
    },
    
    // Error handling
    onFailure : function(message) {
        var msgs;
        if(message.class_message){
            //Очистим сообщение об ошибке
            BSA.Sys.messagebox_clear();
            msgs = message.messages;
            BSA.Sys.messagebox_write(message.class_message, msgs);
        }else{
            BSA.Sys.err_message_write(message);
        }

    }
    
    
});

// The function is executed after the download of the browser window
// are created objects, which are entered in the list of instances
// ex. $H(AdminTools: [new AdminTools(param1), ... ,new AdminTools(paramN)])
BSA.AdminTools.RegRunOnLoad = function() {
    // Получим параметры для создания обьекта
    var params = scriptParams.get('AdminTools');
    // Ф-ия создания обьектов по их параметрам
    var createObject = function (param) {
        var adminTools = scriptInstances.get('AdminTools');
        if (adminTools) {
            adminTools.push(new BSA.AdminTools(param));
        } else {
            scriptInstances.set('AdminTools', [new BSA.AdminTools(param)]);
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

}
runOnLoad(BSA.AdminTools.RegRunOnLoad);