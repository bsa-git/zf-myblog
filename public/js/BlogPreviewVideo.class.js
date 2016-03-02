/**
 * Class - BlogPreviewVideo
 *
 * With these class you can:
 *  - process the video URL you entered
 *  - get more information about the video resource
 *  - add an image inputted video resource
 *  - process the form to enter details manually for video
 *
 * JavaScript
 *
* @author   Sergii Beskorovainyi <bsa2657@yandex.ru>
 * @license  MIT <http://www.opensource.org/licenses/mit-license.php>
 * @link     https://github.com/bsa-git/zf-myblog/
 */
BSA.BlogPreviewVideo = Class.create({
    
    container_video: null, // Video container
    container_upload: null, // Video container for upload
    form_upload: null, // form for upload video
    post_id: 0,// Post ID
    objectHandler: null,
    allowedExtensions: null,// Allowed file extensions for video
    
    // Object initialization
    initialize : function(params)
    {
        // Set containers
        if($(params.container_video) && $(params.container_upload)){
            this.container_video = $(params.container_video);
            this.container_upload = $(params.container_upload);
        }else{
            return
        }
        
        // Set post ID
        if(params.post_id){
            this.post_id = params.post_id;
        }else{
            return
        }
        
        // Get permissible extension of video files
        if(this.container_upload){//video-uploader   upload-video
            var container_id = params.container_upload.split('-')[1] + '-uploader';
            var fileUploaders = scriptInstances.get('FileUploader');
            fileUploaders.each(function(fileUploader) {
                // Get information on the download file (size and file extensions)
                if(fileUploader.container_id == container_id){
                    this.allowedExtensions = fileUploader.settings.arrAllowedExtensions;
                    return;
                }
            }.bind(this));
        }
        
        // Establish an event handler after loading the video URL
        var objectHandlers = scriptInstances.get('BlogImageManager');
        for(var i = 0; i < objectHandlers.length; i++){
            //post-video container_id
            if(objectHandlers[i].container_id == params.container_video){
                this.objectHandler = objectHandlers[i];
            }
        }

        // Establish the input event video URL
        this.iniBlogPreviewVideo();
        
        
    // ATTENTION!!!!
    // All view operations does script - BlogView.class.js
        
    },
    
    // Initialize the form of adding video files
    iniBlogPreviewVideo : function()
    {
        var form = this.container_upload.down('form');
        if(form){
            form.observe('submit', this.onInputUrlClick.bindAsEventListener(this));
            this.form_upload = form;
        }
    },
    
    
    // Enter the video in the form of a URL
    onInputUrlClick : function(e)
    {
        var self = this;
        var errors = {};
        //------------------------------------------
        
        //Очистим сообщение об ошибке
        BSA.Sys.messagebox_clear();
        
        Event.stop(e);
        
        var form = Event.element(e);
        var url = form['url-video'].value;
        var infoVideo =  this.getInfoVideo(url);
        if(! infoVideo){// Ошибка URL
            // Определим сообщение ошибки
            errors.class_message = 'warning';
            errors.messages = ['<em>' + lb.getMsg('msgErrURL') + '.</em> ' + lb.getMsg('msgUrlResourceNotFormat')];
            this.onFailure(errors);
            return;
        }
        
        if(infoVideo.type == 'url-unknown'){// Неизвестный URL
            // Определим сообщение ошибки
            errors.class_message = 'caution';
            errors.messages = ['<em>' + lb.getMsg('msgErrURL') + '.</em> ' + lb.getMsg('msgUrlResourceNotKnown') + '"' + url + '"'];
            this.onFailure(errors);
            return;
        }
        
        var params =  {
            id: this.post_id, 
            info_video: Object.toJSON(infoVideo),
            add_video_url: true
        };

        // Инициализация Ajax запроса
        new Ajax.Request(form.action, {
            parameters: params,
            // Успешный ответ
            onSuccess: function(response) {// OK
                
                try {

                    // Получим данные ответа
                    var json = BSA.Sys.getJsonResponse(response, true);
                    
                    // Проверим есть ли ошибки
                    if (! json.class_message) {// OK

                        BSA.Sys.message_write(json.result);
                        
                        // Выполним обработчик после загрузки файла
                        if(self.objectHandler){
                            self.objectHandler.onAfterUploadFile(json);
                        }
                    }
                } catch (ex) {
                    if (ex instanceof Error) { // Это экземпляр Error или подкласса?
                        self.onFailure(ex.name + ": " + ex.message);
                    }

                } finally {
                    BSA.Sys.message_clear();
                    // Очистим URL поле формы
                    self.form_upload['url-video'].value = '';
                }
            },
            // Ошибочный ответ
            onFailure : function(transport) {// Error Ajax.Request
                var errText = transport.responseText;
                var msgs = [lb.getMsg('msgErrUploadFile'), errText];
                BSA.Sys.messagebox_write('warning', msgs);
                self.onFailure(lb.getMsg('msgErrUploadFile'));
                
            }
        });
    },
    
    // Get information about the video
    getInfoVideo : function(url) {
        
        var infoVideo = {};
        var params;
        //--------------------------------
        var oUrl = new URL();
        if(oUrl.parseURL(url)){// OK
            if(oUrl.scheme == 'rtmp'){
                infoVideo.type = 'url-rtmp';
            }else if(oUrl.host == 'www.youtube.com' || oUrl.host == 'youtube.com'){
                infoVideo.type = 'url-youtube';
            }else if(oUrl.host == 's1.godtv.ru' || oUrl.host == 's2.godtv.ru' || oUrl.host == 's3.godtv.ru'){
                infoVideo.type = 'url-godtv';
            }else{
                // Определим является ли видео - pseudostreaming
                params = oUrl.params;
                params.each(function(p) {
                    if(p.name == 'provider' && p.value == 'pseudo'){
                        infoVideo.type = 'url-pseudostreaming';
                    }
                    
                    if(p.name == 'provider' && p.value == 'httpstreaming'){
                        infoVideo.type = 'url-httpstreaming';
                    }
                })
                if(! infoVideo.type){
                    // Определим на какой ресурс ссылается URL
                    var arrRes =  oUrl.path.split('/');
                    if(arrRes.length > 0){
                        var myRes = arrRes[arrRes.length-1];
                        var arrMyRes = myRes.split('.');
                        if(arrMyRes.length > 0){
                            var myExt = arrMyRes[arrMyRes.length-1];
                            if(this.allowedExtensions.indexOf(myExt) == -1){
                                infoVideo.type = 'url-unknown';
                            }else{
                                infoVideo.type = 'url-' + myExt;
                            }
                        }else{
                            infoVideo.type = 'url-unknown';
                        }
                        
                    }else{
                        infoVideo.type = 'url-unknown';
                    }
                }
            }
            infoVideo.url = url;
        }else{
            infoVideo = null;
        }
        
        return  infoVideo;
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
// ex. $H(BlogPreviewVideo: [new BlogPreviewVideo(), ... ,new BlogPreviewVideo()])
BSA.BlogPreviewVideo.RegRunOnLoad = function() {
    // Получим параметры для создания обьекта
    var params = scriptParams.get('BlogPreviewVideo');
    // Ф-ия создания обьектов по их параметрам
    var createObject = function (param) {
        var previewVideo = scriptInstances.get('BlogPreviewVideo');
        if (previewVideo) {
            previewVideo.push(new BSA.BlogPreviewVideo(param));
        } else {
            scriptInstances.set('BlogPreviewVideo', [new BSA.BlogPreviewVideo(param)]);
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
runOnLoad(BSA.BlogPreviewVideo.RegRunOnLoad);