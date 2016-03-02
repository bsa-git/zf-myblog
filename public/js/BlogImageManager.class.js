/**
 * Class - BlogImageManager
 *
 * With these class you can:
 *  - display album images for the respective post
 *  - delete, sort, and change the title image
 *
 * JavaScript
 *
 * @author   Sergii Beskorovainyi <bsa2657@yandex.ru>
 * @license  MIT <http://www.opensource.org/licenses/mit-license.php>
 * @link     https://github.com/bsa-git/zf-myblog/
 */
BSA.BlogImageManager = Class.create({
    
    container_id: '', // Container ID
    container: null, // Container for images
    deletedImage: '', // The image that has been removed
    commentedImage: false, // Indication that a comment has already been changed to the image
    post_id: 0,      // Post ID
    img: null, // The object of the selected image
    type: '', // Image type (ex. image, audio, video ...)
    id: '',   // ID of select object
    accordion: null,// Accordion object
    isDownloadedImages: false, // Is uploading images to the page
    actual: false, // Accordion state (open or closed)
    
    // Object initialization
    initialize : function(params)
    {
        // Get container
        this.container = $(params.container);
        if(this.container){
            this.container_id = params.container;
        }
        

        if (params.post_id){
            this.post_id = params.post_id;
        }else{
            return;
        }
            
        
        // Is accordion
        if (params.accordion){
            this.accordion = params.accordion;
        }else{
            // Initialize event for images: delete, order, change comment
            this.iniEventForImages();
        }
        
        if(this.accordion){
            // Subscribe to the events in the accordion
            this._subscribeAccordionEvents();
        }
    },
    
    iniEventForImages : function()
    {
        
        // Назначим событие удаления изображения
        this.container.getElementsBySelector('form').each(function(form) {
            form.observe('submit', this.onDeleteClick.bindAsEventListener(this));
        }.bind(this));

        // Назначим событие двойного клика на изображении
        // для изменения коментария для него
        this.container.getElementsBySelector('img').each(function(form) {
            form.observe('dblclick',
                this.onCommentClick.bindAsEventListener(this));
        }.bind(this));
            
        // Инициализация обьекта сортировки изображений
        var options = {
            overlap    : 'horizontal',
            constraint : false,
            onUpdate   : this.onSortUpdate.bind(this)
        };

        Sortable.create(this.container, options);
    },

    //========== IMAGE COMMENT ===========//
    
    onCommentClick : function(e)
    {
        this.img = Event.element(e);
        
        var form = this.container.down('form');
        
        // Запретить выполняться одинаковым событиям
        // откуда они возникают - пока не понятно...
        if(this.commentedImage){
            return;
        }
        
        var title =  this.img.readAttribute('alt');
        var comment =  this.img.readAttribute('title');
        
        if(title){
            comment =  title + ((comment)? ('#'+ comment):'');
        }
        var arrId = this.img.up('li').readAttribute('id').split('_');
        this.type = arrId[0];
        this.id = arrId[1];
        
        var comment_ = prompt(lb.getMsg('msgCommentImage'), comment);

        if(comment_ == comment){
            return;
        }
        if(comment_ == null){
            return;
        }
        
        this.commentedImage = true;

        // Если был изменен комментарий к изображению, то
        // передадим новый комментарий на сервер
        var options = {
            method     : form.method,
            parameters : {
                id: this.post_id,
                image: this.id,
                comment_update: true,
                comment: comment_
            },
            onSuccess  : this.onCommentSuccess.bind(this),
            onFailure  : this.onCommentFailure.bind(this)
        }

        BSA.Sys.message_write(lb.getMsg('msgUpdatingCommentImage'));
        new Ajax.Request(form.action, options);

    },
    onCommentSuccess : function(transport)
    {
        var json = BSA.Sys.getJsonResponse(transport, true);
        this.commentedImage = false;
        if (json.commented) {
            BSA.Sys.message_clear();
            
            // Изменим название
            this.img.writeAttribute('alt', json.title);
            this.img.writeAttribute('title', json.comment);
            var divTitle = this.img.next('div',0);
            if(divTitle){
                divTitle.innerHTML = '#'+ this.id + '. ' + json.title;
            }
            
            
            // Выровняем изображение после изменения названия
            this._setAllignImg(this.type, this.id);
            
        }else{
            this.onCommentFailure(transport);
        }
    },

    onCommentFailure : function(transport)
    {
        this.commentedImage = false;
        BSA.Sys.message_write(lb.getMsg('msgErrCommentImage'));
    },

    //========== DELETE IMAGE ===========//
    
    onDeleteClick : function(e)
    {
        Event.stop(e);

        var form = Event.element(e);

        // Запретить выполняться одинаковым событиям
        // откуда они возникают - пока не понятно...
        if(form['image'].value == this.deletedImage){
            return;
        }else{
            this.deletedImage = form['image'].value;
        }

        var options = {
            method     : form.method,
            parameters : form.serialize(),
            onSuccess  : this.onDeleteSuccess.bind(this),
            onFailure  : this.onDeleteFailure.bind(this)
        }

        BSA.Sys.message_write(lb.getMsg('msgDeletingImage'));
        new Ajax.Request(form.action, options);
    },

    onDeleteSuccess : function(transport)
    {
        var json = BSA.Sys.getJsonResponse(transport, true);
        
        if (json.deleted) {
            var image_id = json.image_id;

            var input = this.container.down('input[value=' + image_id + ']');
            if (input) {
                var options = {
                    duration    : 0.3,
                    afterFinish : function(effect) {
                        BSA.Sys.message_clear();
                        effect.element.remove();

                        if(json.count_images == 0){
                            $('preview-images').hide();
                        }
                    }.bind(this)
                }

                new Effect.Fade(input.up('li'), options);
                
                // Обновим значение секции аккордиона
                this._updateAccordionSectionValue(-1);
                
                return;
            }
        }else{
            this.onDeleteFailure();
        }
    },

    onDeleteFailure : function()
    {
        BSA.Sys.message_write(lb.getMsg('msgErrDeleteImage'));
    },
    
    //========== SORT IMAGES ===========//

    onSortUpdate : function(draggable)
    {
        var form = this.container.down('form');

        var options = {
            method     : form.method,
            parameters : 'reorder=1'
            + '&id=' + this.post_id
            + '&' + Sortable.serialize(draggable),
            onSuccess  : function() {
                BSA.Sys.message_clear();
            }
        };

        BSA.Sys.message_write(lb.getMsg('msgUpdatingOrderImages'));
        new Ajax.Request(form.action, options);
    },
    
    // Add to the list of the new downloaded image
    onAfterUploadFile : function(json)
    {
        var self = this;
        var html = '';
        var id = 0;
        if(json.image_id){
            html = '<li id="image_' + json.image_id + '" style="display:none" >';
            html += '<img src="' + json.url_image + '"' + ' alt="' + json.filename + '"/>';
            html += '<form method="post" action="' + json.form_action + '">';
            html += '<div>';
            html += '<input type="hidden" name="id" value="' + json.post_id + '" />';
            html += '<input type="hidden" name="image" value="' + json.image_id + '">';
            html += '<input type="submit" class="btn btn-mini" value="' + lb.getMsg('msgDeleteImage') + '" name="delete" />';
            html += '</div>';
            html += '</form>';
            html += '</li>';
            id = json.image_id;
        } else if(json.audio_id){
            html = '<li id="audio_' + json.audio_id + '" style="display:none" >';
            html += '<img src="' + json.url_image + '"' + ' alt="' + json.filename + '"/>';
            html += '#'+ json.audio_id + '. ' + json.filename;
            html += '<form method="post" action="' + json.form_action + '">';
            html += '<div>';
            html += '<input type="hidden" name="id" value="' + json.post_id + '" />';
            html += '<input type="hidden" name="image" value="' + json.audio_id + '">';
            html += '<input type="submit" class="btn btn-mini" value="' + lb.getMsg('msgDeleteImage') + '" name="delete" />';
            html += '</div>';
            html += '</form>';
            html += '</li>';
            id = json.audio_id;
        } else if(json.video_id){
            html = '<li id="video_' + json.video_id + '" style="display:none" >';
            //            html += '<img src="' + json.url_image + '"' + ' title="' + json.filename + '"/>';
            html += '<img src="' + json.url_image + '"' + '/>';
            html += '<div style="width: 100%; text-align: center">' + '#'+ json.video_id + '. ' + json.filename + '</div>';
            html += '<form method="post" action="' + json.form_action + '">';
            html += '<div>';
            html += '<input type="hidden" name="id" value="' + json.post_id + '" />';
            html += '<input type="hidden" name="image" value="' + json.video_id + '">';
            html += '<input type="submit" class="btn btn-mini" value="' + lb.getMsg('msgDeleteImage') + '" name="delete" />';
            html += '</div>';
            html += '</form>';
            html += '</li>';
            id = json.video_id;
        }
        
        
        // Вставим изображение
        this.container.insert(html);
        
        // Установим видимость контейнера с изображениями
        var fieldset = this.container.up('fieldset');  
        if(fieldset){
            fieldset.show();
        }
        
        // Выполним эффект добавления изображения в список
        var input = this.container.down('input[value=' + id + ']');
        if (input) {
            var options = {
                duration    : 0.5,
                afterFinish : function() {
                    BSA.Sys.message_clear();
                    var type = '';
                    var id = 0;
                    if(json.image_id){
                        type = 'image';
                        id = json.image_id;
                    } else if(json.audio_id){
                        type = 'audio';
                        id = json.audio_id;
                    } else if(json.video_id){
                        type = 'video';
                        id = json.video_id;
                    }
                    
//                    self._setAllignImg(type, id);
                    // Зададим переиодичность выполнения выравнивания изображений
                    var intervalID = setInterval(function() {
                        var result = false;
                        // Выровним изображения
                        result = self._setAllignImg(type, id);
                        if(result){
                            clearInterval(intervalID);
                        }
                    }, 1000);
                }
            }
            new Effect.Appear(input.up('li'), options);
        }
        
        // Инициализация событий для изображений: удаление, 
        // порядок расположения, изменение комментария
        this.iniEventForImages();
        
        // Обновим значение секции аккордиона
        this._updateAccordionSectionValue(1);
    },
    
    // Update the value of the accordion section
    _updateAccordionSectionValue : function(newValue) {
        var a = $(this.accordion.id).down('a[href=' + this.accordion.section + ']');
        var value = a.innerText;
        var arrValues = value.split('(');
        value = arrValues[1].replace(')', '');
        value = Number(value) + newValue;
        a.innerHTML = a.innerHTML.replace(a.innerText, '') + arrValues[0] + '(' + value + ')';
    },
    
    // Align image in the middle
    _setAllignImg : function(type, id) {
        var container = null;
        var dimensionsContainer = null;
        var maxHeight = 65; 
        //----------------------
        if(type == 'image'){
            container = $('image_' + id); 
        }else if(type == 'audio'){
            container = $('audio_' + id); 
        }else if(type == 'video'){
            container = $('video_' + id); 
        }    
        if(container){
            dimensionsContainer = container.getDimensions();
            var img = container.down('img');
            var dimensionsImg = img.getDimensions();
            // Если картинка еще не прорисовалась, то выйдем из ф-ии
            // с результатом - false
            if(dimensionsImg.width == 0 || dimensionsContainer.height < maxHeight ){
                return false;
            }
            var left = Math.floor((dimensionsContainer.width - dimensionsImg.width)/2);
            if(left >= 5){
                left = left - 5;
                img.setStyle({
                    left: left + 'px'
                });
            }
            
            return true;
        }else{
            return false;
        }
    },
    
    //========== ACCORDION ===========//

    // Subscribe to the events in the accordion
    _subscribeAccordionEvents : function() {
        var self = this;
        var indexSection = self.accordion.section;
        var section;
        var idTimeout;
        //-----------------------------
        var boxAccordions = scriptInstances.get('AccordionBox');
        boxAccordions.each(function(box){
            if(box.id == self.accordion.id ){
                
                box.onHiddenSectionEvent.push({
                    client: self,
                    handlerEvent: self.onHiddenSectionEvent
                });
                
                box.onShownSectionEvent.push({
                    client: self,
                    handlerEvent: self.onShownSectionEvent
                });
                
                // Получим соответсвующую секцию и откроем ее
                if(self.accordion.show){
                    idTimeout = window.setTimeout(function() {
                        section = box.getThisSection(box.accordion, indexSection);
                        if(section){
                            box.accordion.showSection(section);
                        }
                        window.clearTimeout(idTimeout);
                    }, 300);
                }
            }
        })
    },
    
    // Hidden section of the accordion
    onHiddenSectionEvent : function(self, params) {
        var section = params.section.elements.section;
        var hrefSection = section.down('a').readAttribute('href');
        if(hrefSection == self.accordion.section){
            self.actual = false;
        }
    },
    
    // Show section in the accordion
    onShownSectionEvent : function(self, params) {
        var section = params.section.elements.section;
        var hrefSection = section.down('a').readAttribute('href');
        if(hrefSection == self.accordion.section){
            self.actual = true;
            
            // Загрузим изображения на страницу, если они еще не были загружены
            if(! self.isDownloadedImages){
                self.isDownloadedImages = true;
                
                var options = {
                    method     : 'post',
                    parameters : {
                        id: self.post_id,
                        download_images: true
                    },
                    onSuccess  : self.onDownloadImagesSuccess.bind(self),
                    onFailure  : self.onDownloadImagesFailure.bind(self)
                }

                BSA.Sys.message_write(lb.getMsg('msgDownloadingImages'));
                // Установим URL контроллера
                var url = lb.getMsg('urlBase') + self.accordion.url;
                new Ajax.Request(url, options);
            }
        }
    },
    
    onDownloadImagesSuccess : function(transport)
    {
        var self = this;
        //-------------------
        var json = BSA.Sys.getJsonResponse(transport, true);
        this.commentedImage = false;
        if (json.downloaded) {
            BSA.Sys.message_clear();
            
            // Вставим код HTML изображений в контейнер изображений
            this.container.insert(json.html);
            
            // Инициализация событий для изображений: удаление, 
            // порядок расположения, изменение комментария
            this.iniEventForImages();
            
            // Зададим переиодичность выполнения выравнивания изображений
            var intervalID = setInterval(function() {
                // Выровним изображения
                var type = '';
                var id = 0;
                var ids = [];
                var result = false;
                //----------------------
                self.container.select('li').each(function(li) {
                    
                    // Выравним изображения в контейнере
                    ids = li.readAttribute('id').split('_');
                    type = ids[0];
                    id = ids[1];
                    result = self._setAllignImg(type, id);
                    if(! result){
                        return;
                    }
                });
                if(result){
                    clearInterval(intervalID);
                }
            }, 1000);
        }else{
            this.onDownloadImegesFailure();
        }
    },

    onDownloadImagesFailure : function()
    {
        this.commentedImage = false;
        BSA.Sys.message_write(lb.getMsg('msgErrDownloadImages'));
    },
    
    // Handling errors
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
// ex. $H(BlogImageManager: [new BlogImageManager(param1), ... ,new BlogImageManager(paramN)])
BSA.BlogImageManager.RegRunOnLoad = function() {
    // Получим параметры для создания обьекта
    var params = scriptParams.get('BlogImageManager');
    // Ф-ия создания обьектов по их параметрам
    var createObject = function (param) {
        var imageManager = scriptInstances.get('BlogImageManager');
        if (imageManager) {
            imageManager.push(new BSA.BlogImageManager(param));
        } else {
            scriptInstances.set('BlogImageManager', [new BSA.BlogImageManager(param)]);
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
runOnLoad(BSA.BlogImageManager.RegRunOnLoad);