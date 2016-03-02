/**
 * Class - Paginator
 *
 * With these class you can:
 *  - do Ajax requests passing on the page
 *  - change the number of messages per page
 *  - move on a given page
 *
 * JavaScript
 *
 * @author   Sergii Beskorovainyi <bsa2657@yandex.ru>
 * @license  MIT <http://www.opensource.org/licenses/mit-license.php>
 * @link     https://github.com/bsa-git/zf-myblog/
 */
BSA.Paginator = Class.create({

    container : null,
    container_id : '',
    pagerContainer: null,
    pagerContainer_id: '',
    url_mvc: '',
    
    // Object initialization
    initialize : function(params)
    {
        //-----------------------------
        // Paginator container
        if($('paginator-container')){
            this.pagerContainer = $('paginator-container');
            this.pagerContainer_id = 'paginator-container';
        }else{
            return;
        }
        
        // A container for display user messages
        if($(params.container)){
            this.container_id = params.container;
            this.container = $(params.container);
        }else{
            return;
        }
        
        // Get url_mvc
        this.url_mvc =  lb.getMsg('urlBase') + lb.getMsg('urlMVC');

        // Establish an event handler navigate pages
        this.pagerContainer.select('a.my-pager-control').each(function(link) {
            link.observe('click', this.onLinkPagerClick.bindAsEventListener(this));
        }.bind(this));
        
        // Establish an event handler of the page input
        $('page-input').observe('keydown', this.keyHandlerPageInput.bindAsEventListener(this));
        
        // Establish an event handler set the number of posts per page
        $('itemCountPerPage').observe('keydown', this.keyHandlerItemCountPerPage.bindAsEventListener(this));

    },

    onLinkPagerClick : function(e)
    {
        //------------------
        
        // Получим кол. сообщений на странице
        var itemCountPerPage = $('itemCountPerPage').getValue().escapeHTML();
        if(typeof (itemCountPerPage * 1) !== 'number'){
            itemCountPerPage = 10;
        }
        
        // Получим ссылку
        var link = Event.element(e);
        link = link.up('a.my-pager-control');
        
        // Создадим URL
        var url = link.href + '&itemCountPerPage=' + itemCountPerPage;
        
        // Покажем изображение ожидания загрузки обновления
        $('page-loader').show();
        
        var options = {
            onComplete : this.updateObserveClick.bind(this)
        };
        new Ajax.Updater(this.container,
            url,
            options);

        Event.stop(e);
    },
    
    keyHandlerPageInput : function(e)
    {
        //------------------
        if (e.keyCode == Event.KEY_RETURN) {
            
            // Получим кол. сообщений на странице
            var itemCountPerPage = $('itemCountPerPage').getValue().escapeHTML();
            if(typeof (itemCountPerPage * 1) !== 'number'){
                itemCountPerPage = 10;
            }
            
            // Получим страницу
            var input = Event.element(e);
            var page = input.getValue().escapeHTML(); 
            if(typeof (page * 1) !== 'number'){
                page = 1;
            }
            
            // Создадим URL
            var url = this.url_mvc + '?page=' + page + '&itemCountPerPage=' + itemCountPerPage;
        
            // Покажем изображение ожидания загрузки обновления
            $('page-loader').show();
            
            // Сделаем Ajax запрос
            var options = {
                onComplete : this.updateObserveClick.bind(this)
            };
            new Ajax.Updater(this.container,
                url,
                options);
        }
    },
    
    keyHandlerItemCountPerPage : function(e)
    {
        //------------------
        if (e.keyCode == Event.KEY_RETURN) {
            
            // Получим страницу
            var page = $('page-input').getValue().escapeHTML();
            if(typeof (page * 1) !== 'number'){
                page = 1;
            }
            // Получим кол. сообщений на странице
            var input = Event.element(e);
            var itemCountPerPage = input.getValue().escapeHTML();
            if(typeof (itemCountPerPage * 1) !== 'number'){
                itemCountPerPage = 10;
            }
            
            // Создадим URL
            var url = this.url_mvc + '?page=' + page + '&itemCountPerPage=' + itemCountPerPage;
        
            // Покажем изображение ожидания загрузки обновления
            $('page-loader').show();
            
            // Сделаем Ajax запрос
            var options = {
                onComplete : this.updateObserveClick.bind(this)
            };
            new Ajax.Updater(this.container,
                url,
                options);
        }
    },
    
    
    updateObserveClick : function(remote_call)
    {
        var blogSummary = null;
        //---------------------
        // Установим события перемещения по страницам
        $(this.pagerContainer_id).select('a.my-pager-control').each(function(link) {
            link.observe('click', this.onLinkPagerClick.bind(this));
        }.bind(this));
        
        // Установим события ввода страницы
        $('page-input').observe('keydown', this.keyHandlerPageInput.bindAsEventListener(this));
        
        // Установим события ввода количества сообщений на странице
        $('itemCountPerPage').observe('keydown', this.keyHandlerItemCountPerPage.bindAsEventListener(this));
        
        // Если это удаленный вызов, то выйти из ф-ии
        // что бы не было зацикливания 
        if(remote_call === true){
            return;
        }
        
        // Удаленно вызовем ф-ию для установки событий элементов "a.ajax-links-summary"
        if(scriptInstances.get('BlogSummary')){
           blogSummary = scriptInstances.get('BlogSummary')[0]; 
           blogSummary.updateObserveClick(true);
        }
    }
});

// The function is executed after the download of the browser window
// are created objects, which are entered in the list of instances
// ex. $H(Paginator: [new Paginator(param1), ... ,new Paginator(paramN)])
BSA.Paginator.RegRunOnLoad = function() {
    // Получим параметры для создания обьекта
    var params = scriptParams.get('Paginator');
    // Ф-ия создания обьектов по их параметрам
    var createObject = function (param) {
        var paginator = scriptInstances.get('Paginator');
        if (paginator) {
            paginator.push(new BSA.Paginator(param));
        } else {
            scriptInstances.set('Paginator', [new BSA.Paginator(param)]);
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
runOnLoad(BSA.Paginator.RegRunOnLoad);