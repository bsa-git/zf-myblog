/**
 * Class - BlogInfoManager
 *
 * With these class you can:
 *  - represent information help for different languages (en, ru, uk)
 *  - edit the title and content of the information help
 *
 * JavaScript
 *
 * @author   Sergii Beskorovainyi <bsa2657@yandex.ru>
 * @license  MIT <http://www.opensource.org/licenses/mit-license.php>
 * @link     https://github.com/bsa-git/zf-myblog/
 */


BSA.BlogInfoManager = Class.create({

    url: null,
    info_key: '',
    local: '',
    container: null,
    ckeditor: null,
    accordion: null,
    boxAccordion: null,
    formTemplate: null,
    win:null,
    isSavedContent: false,

    // Object initialization
    initialize : function(params)
    {
        // Obtain parameters for the object
        this.url = lb.getMsg('urlBase') + params.url;
        if (params.container){
            this.container = params.container;
        }
        if (params.accordion){
            this.accordion = params.accordion;
        }
        
        // Create editor
        this.ckeditor = new BSA.CKEditorHtml();
        
        // Load the edit form
        this.loadForm();
    },
    
    //--------------- Establish the events for a form --------------

    addEventsObserve : function()
    {
        var self = this;
        //----------------------------
        // Establish the events to the buttons on the edit form
        if($('save_content')){
            Event.observe($('save_content'), 'click', this.onSaveEditContent.bindAsEventListener(this));
        }
        
        if($('cancel_content')){
            Event.observe($('cancel_content'), 'click', this.onCancelEditContent.bindAsEventListener(this));
        }
        
        // Establish the events to edit keys and opening the accordion section
        if($(this.accordion.id)){
            $(this.accordion.id).select('.info-edit-content').each(function(a) {
                Event.observe(a, 'click', self.onEditContent.bindAsEventListener(self));
            });
            
            $(this.accordion.id).select('.info-edit-title').each(function(a) {
                Event.observe(a, 'click', self.onEditTitle.bindAsEventListener(self));
            });
            
            $(this.accordion.id).select('.info-edit-close').each(function(a) {
                Event.observe(a, 'click', self.onInfoClose.bindAsEventListener(self));
            });
        }
        
        // Determine availability of accordion
        if (this.accordion){
            // Subscribe to the events in the accordion
            this._subscribeAccordionEvents();
        }
    },

    //--------------- Loading form of information help --------------
    
    loadForm : function(params)
    {
        var options = {
            onSuccess  : this.loadFormSuccess.bind(this)
        }

        new Ajax.Request(this.url + '/load', options);
    },

    loadFormSuccess : function(transport)
    {
        //----------------------
        try {

            // Получим данные ответа
            var json = BSA.Sys.getJsonResponse(transport, true);
                    
            // Проверим есть ли ошибки
            if (! json.class_message) {// OK
                this.formTemplate = new Template(json.html);
            }
        } catch (ex) {
            if (ex instanceof Error) { // Это экземпляр Error или подкласса?
                this.onFailure(ex.name + ": " + ex.message);
            }

        } finally {
            BSA.Sys.message_clear();
        }
    },
    // Load the value of information help
    loadInfo : function(params)
    {

        var options = {
            parameters : params,
            onSuccess  : this.loadInfoSuccess.bind(this)
        }

        new Ajax.Request(this.url + '/edit', options);
    },

    loadInfoSuccess : function(transport)
    {
        //----------------------
        try {

            // Получим данные ответа
            var json = BSA.Sys.getJsonResponse(transport, true);
                    
            // Проверим есть ли ошибки
            if (json.class_message) {// ERROR

                // Выведем сообщение об ошибке
                BSA.Sys.messagebox_write(json.class_message, json.messages);
                        
            }else{ // OK

                BSA.Sys.message_write(json.result);
                
                // Сохраним ключевое cлово инф. помощи
                this.info_key = json.info_key;
                
                // Откроем окно для редактирования
                this.openInfoWin(json.title, json.info_values);
            }
        } catch (ex) {
            if (ex instanceof Error) { // Это экземпляр Error или подкласса?
                this.onFailure(ex.name + ": " + ex.message);
            }

        } finally {
            BSA.Sys.message_clear();
        }
    },
    
    //------- Save the changes in information help content from the editor --------------
    
    onCancelEditContent : function(event)
    {
        // Остановим событие
        if(event){
            Event.stop(event);
        }
        
        this.ckeditor.removeEditorForAjax('ckeditor_content');
        $('edit-info-content').hide();
        
        // Покажем инф. окно, если оно открыто
        if(this.win){
            this.win.show();
        }
    },

    onSaveEditContent : function(event)
    {
        var options;
        //----------------
        // Остановим событие
        if(event){
            Event.stop(event);
        }
        // Проверим признак сохранения содержимого
        if(this.isSavedContent){
            return;
        }else{
            this.isSavedContent = true;
        }
        
        var ckeditor_content = this.ckeditor.GetContent('ckeditor_content');
        
        // Удалим классы, которые используются в других компонентах системы
        ckeditor_content = ckeditor_content.gsub(/class="[\w ]+"/, function(match){ 
            var s = match[0];
            if(s.include('section')||
                s.include('title')||
                s.include('toggle')||
                s.include('expanded')){
                s =  s.sub('section', '');
                s =  s.sub('title', '');
                s =  s.sub('toggle', '');
                s =  s.sub('expanded', '');
                return s; 
            }else{
                return match[0];
            }
            
        });
        options = {
            parameters : {
                my_action: 'content',
                info_key: this.info_key,
                local: this.local,
                content: ckeditor_content
            },
            onSuccess  : this.onSaveEditContentSuccess.bind(this)
        }
        new Ajax.Request(this.url + '/edit', options);
    },
    
    onSaveEditContentSuccess : function(transport)
    {
        var self = this;
        //----------------------
        try {

            // Получим данные ответа
            var json = BSA.Sys.getJsonResponse(transport, true);
                    
            // Проверим есть ли ошибки
            if (! json.class_message) {// OK

                // Закроем редактор
                this.ckeditor.removeEditorForAjax('ckeditor_content');
                $('edit-info-content').hide();
                
                // Откроем инф. окно с новой инф.
                BSA.Sys.messagebox_clear();
                BSA.Sys.message_write(json.result);
                var sectionContent = $('section-content-' + this.local);
                
                if(sectionContent){
                    sectionContent.innerHTML = json.content; 
                }
                
                // Покажем инф. окно, если оно открыто
                if(this.win){
                    this.win.show();
                }
                
                // Сбросим признак сохранения содержимого
                this.isSavedContent = false;
            }
        } catch (ex) {
            if (ex instanceof Error) { // Это экземпляр Error или подкласса?
                self.onFailure(ex.name + ": " + ex.message);
            }

        } finally {
            BSA.Sys.message_clear();
        }
    },
    
    //------- Edit the title and content of information help --------------
    
    onInfoClose : function(event)
    {
        var self = this;
        var indexSection = 0;
        var section;
        var idTimeout;
        //---------------------
        if(event){
            Event.stop(event);
        }
        
        var a = Event.element(event).up('a');
        
        indexSection = a.getAttribute('href');
        
        // Получим соответсвующую секцию и закроем ее
        idTimeout = window.setTimeout(function() {
            section = self.boxAccordion.accordion.sections[indexSection];
            self.boxAccordion.accordion.hideSection(section);
            window.clearTimeout(idTimeout);
        }, 300);
    },
    
    onEditContent : function(event)
    {

        // Остановим событие
        if(event){
            Event.stop(event);
        }
        
        // Получим измененное название 
        var a = Event.element(event).up('a');
        var local = a.getAttribute('local');
        this.local = local;
        
        var content_local = $('section-content-' + local).innerHTML;
        
        $('edit-info-content').show();

        var config = {
            toolbar : 'Min'
        }
        
        if(this.ckeditor.isEditor('ckeditor_content')){
            this.ckeditor.SetContent('ckeditor_content', content_local);
        }else{
            this.ckeditor.createEditorForAjax('ckeditor_content', config, content_local);
        }
        // Скроем инф. окно, если оно есть
        if(this.win){
            this.win.hide(); 
        }
    },
    
    onEditTitle : function(event)
    {
        // Остановим событие
        if(event){
            Event.stop(event);
        }
        
        // Получим измененное название 
        var a = Event.element(event).up('a');
        var local = a.getAttribute('local');
        this.local = local;
        
        var title_local = $('section-title-' + local).innerHTML;
        var title_ = prompt(lb.getMsg('titleTableTitleInfo'), title_local);
        if(title_){
            title_ = title_.escapeHTML();
        }

        if(title_ == title_local){
            return;
        }
        if(title_ == null){
            return;
        }
        
        // Сохраним новое название
        var options = {
            parameters : {
                my_action: 'title',
                info_key: this.info_key,
                local: local,
                title: title_
            },
            onSuccess  : this.onEditTitleSuccess.bind(this)
        }

        new Ajax.Request(this.url + '/edit', options);
    },
    
    onEditTitleSuccess : function(transport)
    {
        var self = this;
        //----------------------
        try {

            // Получим данные ответа
            var json = BSA.Sys.getJsonResponse(transport, true);
                    
            // Проверим есть ли ошибки
            if (! json.class_message) {// OK

                BSA.Sys.message_write(json.result);
                $('section-title-' + this.local).innerHTML = json.title;  
            }
        } catch (ex) {
            if (ex instanceof Error) { // Это экземпляр Error или подкласса?
                self.onFailure(ex.name + ": " + ex.message);
            }

        } finally {
            BSA.Sys.message_clear();
        }
    },

    //-------------- Working with windows ---------------

    // Open the contents of the information help in a separate window
    openInfoWin : function(title, values)
    {
        //        var self = this;
        // Закроем редактор, если он открыт
        this.onCancelEditContent(null);
        // Создадим обьект окна
        this.win = new Window({
            className: "mac_os_x",
            //className: "dialog",
            title: lb.getMsg('msgMessage'),
            width:650,
            height:450,
            zIndex: 100,
            destroyOnClose: true,
            //            closeCallback: function(){
            //                self.onCancelEditContent(null);
            //                self.win.close();
            //            },
            recenterAuto:true
        });
        var win_content =  '<div class="win-content" id="win_content" >'
        + '<table width="100%" height="100%" border="0" cellpadding="5" cellspacing="0">'
        + '<tr>'
        + '<th>'
        + title
        + '</th>'
        + '</tr>'
        + '<tr>'
        + '<td align="left" valign="top" >'
        + this.formTemplate.evaluate(values)
        + '</td>'
        + '</tr>'
        + '</table>'
        + '</div>';
        this.win.getContent().update(win_content);
        
        // Создадим обьект аккордиона
        this.boxAccordion = new BSA.AccordionBox({
            id: this.accordion.id,
            options : {
                mutuallyExclusive: false
            },
            states: {
                disable: [], 
                show: []
            }
        });
        
        // Установим события для окна
        this.addEventsObserve();
        
        // Покажем окно
        this.win.showCenter();
    },
    
    
    //========== ACCORDION ===========//

    // Subscribe to the events in the accordion
    _subscribeAccordionEvents : function() {
        this.boxAccordion.onHiddenSectionEvent.push({
            client: this,
            handlerEvent: this.onHiddenSectionEvent
        });
                
        this.boxAccordion.onShownSectionEvent.push({
            client: this,
            handlerEvent: this.onShownSectionEvent
        });
    },
    
    // Hidden section of the accordion
    onHiddenSectionEvent : function(self, params) {
        var section = params.section.elements.section;
        var hrefSection = section.down('a').readAttribute('href');
    },
    
    // Show section in the accordion
    onShownSectionEvent : function(self, params) {
        var section = params.section.elements.section;
        var hrefSection = section.down('a').readAttribute('href');
    },

    //----- Handling errors ------
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
// ex. $H(BlogInfoManager: [new BlogInfoManager(param1), ... ,new BlogInfoManager(paramN)])
BSA.BlogInfoManager.RegRunOnLoad = function() {
    // Получим параметры для создания обьекта
    var params = scriptParams.get('BlogInfoManager');
    // Ф-ия создания обьектов по их параметрам
    var createObject = function (param) {
        var infoManager = scriptInstances.get('BlogInfoManager');
        if (infoManager) {
            infoManager.push(new BSA.BlogInfoManager(param));
        } else {
            scriptInstances.set('BlogInfoManager', [new BSA.BlogInfoManager(param)]);
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
runOnLoad(BSA.BlogInfoManager.RegRunOnLoad);