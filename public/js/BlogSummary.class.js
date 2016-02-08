/**
 * BlogSummary - Class
 *
 * С помощью класса вы можете:
 *  - делать Ajax запросы по ссылкам (метки, архивы)
 *  - делать Ajax запросы по поиску данных в сообщениях
 *
 * JavaScript
 *
 * Copyright (c) 2011 Бескоровайный Сергей
 *
 * @author     Бескоровайный Сергей <bs261257@gmail.com>
 * @copyright  2011 Бескоровайный Сергей
 * @license    BSD
 * @version    1.00.00
 * @link       http://my-site.com/web
 */
BSA.BlogSummary = Class.create({

    container : null,
    container_id : '',
    
    initialize : function(params)//container, linkContainer
    {
        var self = this;
        //-----------------------------
        // Контейнер для вывода сообщений пользователей
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
                
                // Если класс елемента не соответвует ID контейнера, то
                // НЕ делать Ajax запрос 
                if(! this.hasClassName(self.container_id)){
                    return true;
                }
                
                // Покажем изображение ожидания загрузки обновления
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

// Ф-ия, выполняемая при загрузки окна броузера
// создаются обьекты класса, экземпляры их
// заносяться в список экземпляров
// пр. $H(BlogSummary: [new BlogSummary(param1), ... ,new BlogSummary(paramN)])
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