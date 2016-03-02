/**
 * Class - BlogSummary
 *
 * With these class you can:
 *  - do Ajax requests on the links (tags, archives)
 *  - do Ajax requests to search for data in the posts
 *
 * JavaScript
 *
 * @author   Sergii Beskorovainyi <bsa2657@yandex.ru>
 * @license  MIT <http://www.opensource.org/licenses/mit-license.php>
 * @link     https://github.com/bsa-git/zf-myblog/
 */
BSA.BlogSummary = Class.create({

    container : null,
    container_id : '',
    
    // Object initialization
    initialize : function(params)
    {
        var self = this;
        //-----------------------------
        // Get a container to display user messages
        if($(params.container)){
            this.container_id = params.container;
            this.container = $(params.container);
        }else{
            return;
        }

        
        $$('a.ajax-links-summary').each(function(link) {
            link.observe('click', this.onLinkSummaryClick.bindAsEventListener(this));
        }.bind(this));
        
        
        $$('form.ajax-links-summary').each(function(form) {
            form.onsubmit = function() {
                
                // If the class of element does not match the container ID, then do not Ajax request
                if(! this.hasClassName(self.container_id)){
                    return true;
                }
                
                // Show waiting download
                $('wait-loading').show();
                
                var options = {
                    parameters: this.serialize(true),
                      onComplete : self.updateObserveClick.bind(self)
                };
                new Ajax.Updater(self.container,
                    this.action,
                    options);
                return false
            }
        })
    },

    onLinkSummaryClick : function(e)
    {
        var link = Event.element(e);
        
        // Если класс елемента не соответвует ID контейнера, то
        // НЕ делать Ajax запрос 
        if(! link.hasClassName(this.container_id)){
            return;
        }
        
        // Покажем изображение ожидания загрузки обновления
        $('wait-loading').show();
        
        var options = {
            parameters : {
                ajax: 'updater'// Этот параметр нужен только что бы разделить при кешировании  ajax запросы от обычных запросов
            },
            onComplete : this.updateObserveClick.bind(this)
        };
        new Ajax.Updater(this.container,
            link.href,
            options);

        Event.stop(e);
    },
    
    updateObserveClick : function(remote_call)
    {
        var paginator = null;
        //---------------
        
        // Установим обработчики событий, на обновленные элементы
        this.container.select('a.ajax-links-summary').each(function(link) {
            link.observe('click', this.onLinkSummaryClick.bindAsEventListener(this));
        }.bind(this));
        
        // Скроем изображение ожидания загрузки обновления
        $('wait-loading').hide();
        
        // Если это удаленный вызов, то выйти из ф-ии
        // что бы не было зацикливания 
        if(remote_call === true){
            return;
        }
        // Удаленно вызовем ф-ию для установки событий элементов:
        // - установим события перемещения по страницам
        // - установим события ввода страницы
        // - установим события ввода количества сообщений на странице
        if(scriptInstances.get('Paginator')){
           paginator = scriptInstances.get('Paginator')[0]; 
           paginator.updateObserveClick(true);
        }
    }
});

// The function is executed after the download of the browser window
// are created objects, which are entered in the list of instances
// ex. $H(BlogSummary: [new BlogSummary(param1), ... ,new BlogSummary(paramN)])
BSA.BlogSummary.RegRunOnLoad = function() {
    // Получим параметры для создания обьекта
    var params = scriptParams.get('BlogSummary');
    // Ф-ия создания обьектов по их параметрам
    var createObject = function (param) {
        var summary = scriptInstances.get('BlogSummary');
        if (summary) {
            summary.push(new BSA.BlogSummary(param));
        } else {
            scriptInstances.set('BlogSummary', [new BSA.BlogSummary(param)]);
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
runOnLoad(BSA.BlogSummary.RegRunOnLoad);