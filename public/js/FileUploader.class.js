/**
 * Class - FileUploader
 *
 * With these class you can:
 *  - upload files
 *  - control when loading type and file size
 *  - can be uploaded multiple files
 *
 * JavaScript
 *
 * @author   Sergii Beskorovainyi <bsa2657@yandex.ru>
 * @license  MIT <http://www.opensource.org/licenses/mit-license.php>
 * @link     https://github.com/bsa-git/zf-myblog/
 */
BSA.FileUploader = Class.create({
    
    // Default config
    settings: {
        arrAllowedExtensions: ['jpg', 'jpeg', 'png', 'gif'],
        strAllowedExtensions: 'jpg;jpeg;png;gif',
        sizeLimit: 1// The size in megabytes
    },
    container: null,
    container_id: '',
    objectHandler: null,// file upload handler
    ajaxAction: '/blogmanager/images',// URL for Ajax request
    ajaxParams: null,// Ajax params
    
    // Object initialization
    initialize : function(params)
    {
        var hint = '';
        var hintAllowedExtensions = ''; 
        //----------------
        // Set container
        if($(params.container)){
            this.container = $(params.container);
            this.container_id = params.container;
        }else{
            return;
        }
        
        // Set settings
        if(params.settings){
            this.settings = {
                arrAllowedExtensions:  params.settings.allowedExtensions,
                strAllowedExtensions: params.settings.allowedExtensions.join('; '),
                sizeLimit:  params.settings.sizeLimit,
                type: (params.settings.type)?params.settings.type.toLowerCase():''
            }
            // Create a string of hints for valid extensions
            this.settings.arrAllowedExtensions.each(function(extension) {
                hint = '<span class="help-hint ajax_allowed-file-extensions-'+ extension +'">'+ extension +'</span>; ';
                hintAllowedExtensions += hint;
            });
            this.settings.hintAllowedExtensions = hintAllowedExtensions;
        }
        
        
        // Set Ajax params
        this.ajaxParams = {
            allowedExtensions: this.settings.arrAllowedExtensions.join(';'),
            sizeLimit: 1024 * 1024 * this.settings.sizeLimit
        }
        
        // Ajax request
        if(params.ajaxRequest){
            
            this.ajaxAction = params.ajaxRequest.url;// URL for Ajax request
            // Add new parameters
            if(params.ajaxRequest.params){
                this.ajaxParams = Object.extend(this.ajaxParams, params.ajaxRequest.params);
            }
            
        }
        
        // Set file upload handler
        if(params.objectHandler){
            this.objectHandler = scriptInstances.get(params.objectHandler.classObject)[params.objectHandler.indexObject];
        }
        
        // Initialization file uploader
        this.iniFileUploader();
    },
    
    // Event before uploading file
    iniFileUploader : function()
    {
        var messages = {
            typeError: "{file} " + lb.getMsg('msgErrFileExt') + "{extensions}.",
            sizeError: "{file} " + lb.getMsg('msgErrFileSize') + "{sizeLimit}.",
            minSizeError: "{file} " + lb.getMsg('msgErrFileMinSize') + "{minSizeLimit}.",
            emptyError: "{file} " + lb.getMsg('msgErrFileEmpty'),
            onLeave: lb.getMsg('msgUploadingOnLeave')
        };

        // Создание обьекта Загрузчика файлов
        this.uploader = new qq.FileUploader({
            element: this.container,
            action: lb.getMsg('urlBase') + this.ajaxAction,
            allowedExtensions: this.settings.arrAllowedExtensions,
            sizeLimit: 1024 * 1024 * this.settings.sizeLimit,
            dragText: lb.getMsg('msgDropFilesHereToUpload'),
            uploadButtonText: lb.getMsg('msgUploadFile'),
            cancelButtonText: lb.getMsg('msgUploadFileCancel'),
            failUploadText: lb.getMsg('msgErrUploadFile'),
            messages: messages,
            params: this.ajaxParams,
            onSubmit: function(id, fileName){
                this.onSubmit(id, fileName);
            }.bind(this),
            onComplete: function(id, fileName, responseJSON){
                this.onUploadComplete(responseJSON);
            }.bind(this),
            showMessage: function(message){
                // Выведем сообщение в правом поле
                BSA.Sys.message_write(message);
                BSA.Sys.messagebox_clear();
                var messages = new Array('<em>' + lb.getMsg('msgErrUploadFile') + '</em><br />' + message);

                BSA.Sys.messagebox_write('warning', messages);
                BSA.Sys.message_clear();
            }.bind(this),
            debug: true,
            typeUploadHandler: this.settings.type// Дополнительный параметр, для принудительного задания вида загрузчика (IFrame или Xhr)
        });
    },

    onSubmit : function(id, fileName)
    {
    },

    // Event after upload file
    onUploadComplete : function(json)
    {
        BSA.Sys.messagebox_clear();
        BSA.Sys.message_clear();
        
        var strJson = json.toString();
        
        // Проверим есть ли ошибки
        if (json.class_message) {// есть ошибки
                        
            // Выведем сообщение об ошибке
            BSA.Sys.message_write(lb.getMsg('msgErrUploadFile'));
            BSA.Sys.messagebox_write(json.class_message, json.messages);
        }else{ // OK
            
            if(json.post_id){
                BSA.Sys.message_write(lb.getMsg('msgFileUploaded'));

                // Выполним обработчик после загрузки файла
                this.objectHandler.onAfterUploadFile(json);
            
                BSA.Sys.message_clear();
            }else{
                // Выведем сообщение об ошибке
                BSA.Sys.message_write(lb.getMsg('msgErrUploadFile'));
                
                var messages  = '<em>' + 'Неизвесная ошибка при загрузке файла' +'</em>';
                BSA.Sys.messagebox_write('warning', [messages]);
            }
            
        }
    },
    
    //----- ERROR ------
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
// ex. $H(FileUploader: [new FileUploader(param1, inst), ... ,new FileUploader(paramN, inst)])
BSA.FileUploader.RegRunOnLoad = function() {
    // Получим параметры для создания обьекта
    var params = scriptParams.get('FileUploader');
    // Ф-ия создания обьектов по их параметрам
    var createObject = function (param) {
        var fileUploader = scriptInstances.get('FileUploader');
        if (fileUploader) {
            fileUploader.push(new BSA.FileUploader(param));
        } else {
            scriptInstances.set('FileUploader', [new BSA.FileUploader(param)]);
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
runOnLoad(BSA.FileUploader.RegRunOnLoad);