/**
 * BlogPreview - Class
 *
 * With these class you can:
 *  - initialize buttons on the control panel of message
 *  - manage tags
 *  - display information about the loaded resources (images, video, audio)
 *
 * JavaScript
 *
 * @author   Sergii Beskorovainyi <bsa2657@yandex.ru>
 * @license  MIT <http://www.opensource.org/licenses/mit-license.php>
 * @link     https://github.com/bsa-git/zf-myblog/
 */
BSA.BlogPreview = Class.create({
    
    tagsContainer: null,
    
    // Object initialization
    initialize : function()
    {
        // Get the objects of control buttons
        var publishButton   = $('status-publish');
        var unpublishButton = $('status-unpublish');
        var deleteButton    = $('status-delete');
        var tagsContainer = $('preview-tags');

        // Set events for buttons
        if (publishButton) {
            publishButton.observe('click', function(e) {
                if (!confirm(lb.getMsg('msgPublishBlog')))
                    Event.stop(e);
            });
        }

        if (unpublishButton) {
            unpublishButton.observe('click', function(e) {
                if (!confirm(lb.getMsg('msgUnpublishBlog')))
                    Event.stop(e);
            });
        }

        if (deleteButton) {
            deleteButton.observe('click', function(e) {
                if (!confirm(lb.getMsg('msgDeleteBlog')))
                    Event.stop(e);
            });
        }
        
        
        if(tagsContainer){
            
            this.tagsContainer = tagsContainer;
            
            // Assign an event to remove tag
            this.tagsContainer.select('ul form').each(function(form) {
                form.observe('submit', this.onDeleteTagClick.bindAsEventListener(this));
            }.bind(this));
            
            // Assign an event to add tag
            var input = this.tagsContainer.down('input[type=text]');
            var addForm = input.up('form');
            if(addForm){
                addForm.observe('submit', this.onAddTagClick.bindAsEventListener(this));
            }
            
        }
        
        
        this.setInfo();

    },
    
    //------ Add tag --------//
    
    onAddTagClick : function(e)
    {
        Event.stop(e);

        var form = Event.element(e);

        var options = {
            method     : form.method,
            parameters : form.serialize(),
            onSuccess  : this.onAddTagSuccess.bind(this)
        }

        new Ajax.Request(form.action, options);
    },
    
    onAddTagSuccess : function(transport)
    {
        try {

            // Получим данные ответа
            var json = BSA.Sys.getJsonResponse(transport, true);
                    
            // Проверим есть ли ошибки
            if (! json.class_message) {// OK
                BSA.Sys.message_write(json.result);
                
                // Вставим, добавленный элемент метки
                this.tagsContainer.down('ul').insert(json.html);
                // Установим обработчик для добавленного элемента метки
                var input = this.tagsContainer.down('input[value=' + json.tag + ']');
                if(input){
                    input.up('form').observe('submit', this.onDeleteTagClick.bindAsEventListener(this));
                }
            }
        } catch (ex) {
            if (ex instanceof Error) { // Это экземпляр Error или подкласса?
                this.onFailure(ex.name + ": " + ex.message);
            }

        } finally {
            BSA.Sys.message_clear();
        }
    },
    
    //------------ Delete tag -----------//
    
    onDeleteTagClick : function(e)
    {
        Event.stop(e);

        var form = Event.element(e);

        var options = {
            method     : form.method,
            parameters : form.serialize(),
            onSuccess  : this.onDeleteTagSuccess.bind(this)
        }

        new Ajax.Request(form.action, options);
    },

    onDeleteTagSuccess : function(transport)
    {
        try {

            // Получим данные ответа
            var json = BSA.Sys.getJsonResponse(transport, true);
                    
            // Проверим есть ли ошибки
            if (! json.class_message) {// OK
                BSA.Sys.message_write(json.result);
                var tag = json.tag;

                var input = this.tagsContainer.down('input[value=' + tag + ']');
                if (input) {
                    var options = {
                        duration    : 0.3,
                        afterFinish : function(effect) {
                            BSA.Sys.message_clear();
                            effect.element.remove();

                        }.bind(this)
                    }

                    new Effect.Fade(input.up('li'), options);
                }
            }
        } catch (ex) {
            if (ex instanceof Error) { // Это экземпляр Error или подкласса?
                this.onFailure(ex.name + ": " + ex.message);
            }

        } finally {
            BSA.Sys.message_clear();
        }
    },
    
    //----- Get info for upload ------
    setInfo : function() {
        //        var self = this;
        var fileUploaders = scriptInstances.get('FileUploader');
        //------------------------------------------------
        fileUploaders.each(function(fileUploader) {
            // Получим инф. о загрузчиках файлов (размер и расширения файлов)
            var container_id = 'preview-' + fileUploader.container_id.split('-')[0];
            var hintAllowedExtensions = fileUploader.settings.hintAllowedExtensions;
            var sizeLimit = fileUploader.settings.sizeLimit;
            
            var infoUploader = $(container_id).previous('div');
            infoUploader.innerHTML = lb.getMsg('msgAllowableSizeUploadedFile') + ': <em>'+ sizeLimit + 'M</em>. ' + 
            lb.getMsg('msgAllowedExtensionsDownloads') + ': '+ hintAllowedExtensions + '<br />' + 
            infoUploader.innerHTML;
            
        });
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
})

// The function is executed after the download of the browser window
// are created objects, which are entered in the list of instances
// ex. $H(BlogPreview: [new BlogPreview(), ... ,new BlogPreview()])
BSA.BlogPreview.RegRunOnLoad = function() {
    // Получим параметры для создания обьекта
    var params = scriptParams.get('BlogPreview');
    // Ф-ия создания обьектов по их параметрам
    var createObject = function (param) {
        var preview = scriptInstances.get('BlogPreview');
        if (preview) {
            preview.push(new BSA.BlogPreview(param));
        } else {
            scriptInstances.set('BlogPreview', [new BSA.BlogPreview(param)]);
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
runOnLoad(BSA.BlogPreview.RegRunOnLoad);