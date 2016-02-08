/**
 * FileUploader - Class
 *
 * С помощью класса вы можете:
 *  - загружать файлы
 *  - контролировать при загрузке тип, размер файла
 *  - возможна загрузка нескольких файлов через перетаскивание файлов
 *  в определенную область формы
 *
 * JavaScript
 *
 * Copyright (c) 2011 Бескоровайный Сергей
 *
 * @author     Бескоровайный Сергей <bs261257@gmail.com>
 * @copyright  2012 Бескоровайный Сергей
 * @license    BSD
 * @version    1.00.00
 * @link       http://my-site.com/web
 */
BSA.FileUploader = Class.create({
    
    settings: {// Настройки по умолчанию
        arrAllowedExtensions: ['jpg', 'jpeg', 'png', 'gif'],
        strAllowedExtensions: 'jpg;jpeg;png;gif',
        sizeLimit: 1//Размер в мегабайтах
    },
    container: null,// Контейнер
    container_id: '',// Контейнер ID
    objectHandler: null,// Обьект обработчика события загрузки файла
    ajaxAction: '/blogmanager/images',// URL для Ajax запроса
    ajaxParams: null,// Параметры для Ajax запроса
    
    initialize : function(params)
    {
        var hint = '';
        var hintAllowedExtensions = ''; 
        //----------------
        // Установим контейнер
        if($(params.container)){
            this.container = $(params.container);
            this.container_id = params.container;
        }else{
            return;
        }
        
        //Определим настройки 
        if(params.settings){
            this.settings = {
                arrAllowedExtensions:  params.settings.allowedExtensions,
                strAllowedExtensions: params.settings.allowedExtensions.join('; '),
                sizeLimit:  params.settings.sizeLimit,
                type: (params.settings.type)?params.settings.type.toLowerCase():''
            }
            // Создадим строку подсказок для допустимых расширений
            this.settings.arrAllowedExtensions.each(function(extension) {//tooltip
                hint = '<span class="help-hint ajax_allowed-file-extensions-'+ extension +'">'+ extension +'</span>; ';
                hintAllowedExtensions += hint;
            });
            this.settings.hintAllowedExtensions = hintAllowedExtensions;
        }
        
        
        // Установим параметры для Ajax запроса
        this.ajaxParams = {
            allowedExtensions: this.settings.arrAllowedExtensions.join(';'),
            sizeLimit: 1024 * 1024 * this.settings.sizeLimit
        }
        
        // Ajax запрос ajaxRequest
        if(params.ajaxRequest){
            
            this.ajaxAction = params.ajaxRequest.url;// URL для Ajax запроса
            // Добавим новые занчения параметров, если они есть
            if(params.ajaxRequest.params){
                this.ajaxParams = Object.extend(this.ajaxParams, params.ajaxRequest.params);
            }
            
        }
        
        //Определим обработчик события загрузки
        if(params.objectHandler){
            this.objectHandler = scriptInstances.get(params.objectHandler.classObject)[params.objectHandler.indexObject];
        }
        
        // Инициализация файлового загрузчика
        this.iniFileUploader();
    },
    
    // Событие перед загрузкой файла
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

    // Событие перед загрузкой файла
    onSubmit : function(id, fileName)
    {
    },

    // Событие после загрузки файла
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
    
    //----- Обработка ошибок ------
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

// Ф-ия, выполняемая при загрузки окна броузера
// создаются обьекты класса, экземпляры их
// заносяться в список экземпляров
// пр. $H(FileUploader: [new FileUploader(param1, inst), ... ,new FileUploader(paramN, inst)])
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