/**
 * BlogView - Class
 *
 * С помощью класса BlogView вы можете:
 *  - изменить картинки, для просмотра с помощью LightBox
 *  - Изменить события ссылок содержания блога
 *    при нажатии на ссылку, должно открываться отдельное окно
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
BSA.BlogView = Class.create({

    url_report       : null,
    url_params: null,

    post_id   : null,   // ID для сообщения
    user_id   : null,   // ID для пользователя, создателя сообщения
    containers : [],   // массив контейнеров, с которыми производяться действия
    isModifiedEventsMarker : null,
    dialog_info: null,
    local: 'ru',
    pimp3: null, // Обьект проигрывателя Аудио

    initialize : function(params)
    {
        if(params.containers){
            this.containers = params.containers;
        }
        
        // Определим язык сайта
        this.local = lb.getMsg('languageSite');
        
        // Инициализация кнопки - 'content-in-new-window' 
        var btnShowInWindow = $('content-in-new-window');
        if(btnShowInWindow){
            // Покажем иконку
            btnShowInWindow.show();
            
            // Назначим событие - отобразить содержание в отдельном окне
            btnShowInWindow.observe('click', this.onOpenContentWin.bind(this));
        }
        
        // Запомним параметры диалога
        if(params.dialog_info){
            this.dialog_info = params.dialog_info;
            
            // Откроем диалог
            if(params.dialog_info.open){
                BSA.Dialogs.openDialogInfo(params.dialog_info);
            }
        }
        
        // Инициализация кнопки - 'get-report-pdf'
        var btnGetReportPDF = $('get-report-pdf');
        if(btnGetReportPDF){
            // Назначим событие - отобразить содержание в отдельном окне
            btnGetReportPDF.observe('click', this.onGetReportPDF.bindAsEventListener(this));
        }
        
        
        // Изменим html код картинок и ссылок
        if(this.containers.size()){
            this.modifyContentImages();
            this.modifyContentLinks();
        }
        
        // Установим события для инф. помощи
        var listInfoWin = $$('.help-info-win');
        if(listInfoWin){
            listInfoWin.each(function(aInfoWin) {
                aInfoWin.observe('click', this.onClickInfoWin.bind(this));
            }.bind(this))
        }
        
        // Инициализация инф. подсказок
        TooltipManager.init("tooltip", {
            url: lb.getMsg('urlBase') + '/admin/info/hint?local=' + this.local, 
            options: {
                method: 'get'
            }
        }, {
            showEffect: Element.show, 
            hideEffect: Element.hide
        });
        
        // Инициализация фреймов
        this.containers.each(function(container_id) {
            if($(container_id)){
                var iframes = $(container_id).select('iframe');
                iframes.each(function(iframe) {
                    var frameborder =  iframe.readAttribute('frameborder');
                    if(!frameborder){
                        iframe.writeAttribute('frameborder', 0);
                    }
            
                    // Перезагрузим страницу если атрибут фрейма 'src' = ""
                    // эта ошибка появляется у хрома в режиме выхода из редактирования
                    // при создании фрейма, который загружает страницу из собственного модуля
                    var src =  iframe.readAttribute('src');
                    if(!src){
                        window.location.reload();
                    }
                })
            }
        })
        
    },
    
    
    //-------------- Работа с информационной помощью ---------------
    
    // Открыть окно с информацией
    onClickInfoWin : function(event){
        Event.stop(event);
        var aInfo = Event.element(event);
        if(aInfo.up('a[href="#"]')){
            aInfo = aInfo.up('a[href="#"]');
        }
        var info_key = aInfo.readAttribute('id');
        var options = {
            parameters : {
                info_key: info_key,
                local: this.local
            },
            onSuccess  : this.loadInfoSuccess.bind(this)
        }

        new Ajax.Request(lb.getMsg('urlBase') + '/admin/info/view', options);
    },

    loadInfoSuccess : function(transport)
    {
        //----------------------
        try {

            // Получим данные ответа
            var json = BSA.Sys.getJsonResponse(transport, true);
                    
            // Проверим есть ли ошибки
            if (! json.class_message) {// OK

                // Откроем окно для редактирования
                this.openInfoWin(json.title, json.content);
            }
        } catch (ex) {
            if (ex instanceof Error) { // Это экземпляр Error или подкласса?
                this.onFailure(ex.name + ": " + ex.message);
            }

        } finally {
            BSA.Sys.message_clear();
        }
    },
    
    // Открыть окно с информацией
    openInfoWin : function(title, content)
    {
        // Создадим обьект окна
        var win = new Window({
            className: "mac_os_x",
            //className: "dialog",
            title: lb.getMsg('msgInformation'),
            width:500,
            height:400,
            destroyOnClose: true,
            recenterAuto:false
        });
        var info_content =  '<div class="info-content" id="info_content" >'
        + '<table width="100%" height="100%" border="0" cellpadding="5" cellspacing="0">'
        + '<tr>'
        + '<th><h1>'
        + title
        + '</h1></th>'
        + '</tr>'
        + '<tr>'
        + '<td align="left" valign="top" >'
        + content
        + '</td>'
        + '</tr>'
        + '</table>'
        + '</div>'
        win.getContent().update(info_content);
        win.showCenter();

    },
    
    //-------------- Модификация содержания: изображений, ссылок ---------------

    // Изменить html код картинок, что бы их можно было
    // просматривать с помощью lightbox.js
    //
    // - пр. преобразовать тег <img alt="" src="/upload/users/user1/.thumbs/images/bsa.jpg" style="width: 100px; height: 100px; ">
    //   в теги -> <a href="/upload/users/user1/images/bsa.jpg"" rel="lightbox[location]">
    //               <img alt="" src="/upload/users/user1/.thumbs/images/bsa.jpg" style="width: 100px; height: 100px; ">
    //             </a>
    modifyContentImages : function()
    {
        var scr = '';
        var rel_lightbox = '';
        //--------------------------------------------
        this.containers.each(function(container_id) {
            if($(container_id)){
                $(container_id).select('img').each(function(img) {
                    scr = img.readAttribute('src');
                    if(scr.include('/.thumbs/')){
                        scr = scr.sub('.thumbs/', '');
                        // Определим есть ли у img обертка в виде ссылки 
                        var img_wrap = img.up('a[href="' + scr + '"]');
                        if(img_wrap){// Yes
                            rel_lightbox =  img_wrap.readAttribute("rel");
                            if(!rel_lightbox){
                                img_wrap.writeAttribute('rel', 'lightbox[' + container_id + ']');
                            }
                        }else{// No
                            img.wrap('a', {
                                'href': scr,
                                'rel': 'lightbox[' + container_id + ']'  //content
                            });
                        }
                    }
                    scr = '';
                });
            }  
        })
    },

    //Изменить события ссылок содержания блога
    // При нажатии на ссылку, должно открываться отдельное окно
    modifyContentLinks : function()
    {
        var rel;
        //-----------------------------
        // Проверим были уже изменены ссылки
        if(this.isModifiedEventsMarker){
            return;
        }else{
            this.isModifiedEventsMarker = true;
        }


        var arrListFileResources = $A(BSA.Sys.settings.list_file_resources);
        var isFileResource = false;
        var href = '';
        // Назначим для ссылок, кроме ссылок на фотографии
        // новые события, которые будут открывать отдельное окно для этой ссылки
        this.containers.each(function(container_id) {
            if($(container_id)){
                $(container_id).select('a').each(function(a) {
                    rel = a.readAttribute('rel');
                    if(! rel){
                        // Получим URL ссылки
                        href = a.readAttribute('href');
                        // Не обрабатываем пустую ссылку
                        if(! href){
                            return;
                        }
                
                        // Не обрабатываем ссылку, которая не начинается с "/"
                        // пр. href="39"
                        if(! href.startsWith("http") && 
                            ! href.startsWith("https") && 
                            ! href.startsWith("/")){
                            return;
                        }

                        // Определим является ли ссылка файловым ресурсом
                        arrListFileResources.each(function(s) {
                            if(href.endsWith(s)){
                                isFileResource = true;
                                return;
                            }
                        });
                
                        // Если ссылка не файловый ресурс, то откроем ее в отдельном окне
                        if(! isFileResource){
                            a.observe('click', this.onOpenWinForURL.bind(this));
                        }else{
                            isFileResource = false;
                        }

                    }
                }.bind(this));
            }
        }.bind(this))
    },
    

    //-------------- Работа с отдельным окном для отображения сообщения блога и ссылок URL ---------------

    onOpenContentWin : function(event)
    {
        var container = null;
        //-----------------
        
        if(this.containers.size()){
            container = $(this.containers[0]);
        }else{
            return;
        }
        
        // Отменим поведение по умолчанию
        Event.stop(event);
        
//        var title = $$('header.title h2 span');
//        title = title.first();
//        title = title.innerHTML;
        
        var title = $('title').innerHTML;
        // Создадим обьект окна
        var win = new Window({
            className: "mac_os_x",
            //className: "dialog",
            title: lb.getMsg('msgMessage'),
            width:600,
            height:400,
            destroyOnClose: true,
            recenterAuto:false
            
        });// 
        var win_content =  '<div class="win-content" id="win_content" >'
        + '<table width="100%" height="100%" border="0" cellpadding="5" cellspacing="0">'
        + '<tr>'
        + '<th><h2>'
        + title
        + '</h2></th>'
        + '</tr>'
        + '<tr>'
        + '<td align="left" valign="top" >'
        + container.innerHTML
        + '</td>'
        + '</tr>'
        + '</table>'
        + '</div>'
        win.getContent().update(win_content);
        win.showCenter();

    },

    onOpenWinForURL : function(event)
    {
        // Отменим поведение по умолчанию
        Event.stop(event);

        // Получим элемент, вызвавший событие
        var element = Event.element(event);
        var nodeName = element.nodeName;
        nodeName = nodeName.toLowerCase();
        if(nodeName == "img"){
            element = element.up("a");
        }

        // Получим URL ссылки
        var href = element.readAttribute('href');

        // Откроем окно для этого URL
        //this.onOpenWinWithURL(href);
        var win = new Window({
            className: "mac_os_x",
            title: "",
            top:0,
            left:0,
            width:600,
            height:400,
            url: href,
            showEffectOptions: {
                duration:1.5
            }
        })
        win.showCenter();
        win.show();
    },
    
    //-------------- Работа с отчетами ---------------
    
    onGetReportPDF : function(event)
    {
        Event.stop(event);
        
        $('wait-loading').show();
        
        // Откроем диалог ожидания
        BSA.Dialogs.openDialogInfo(this.dialog_info);
        
    //--- Обратимся к котроллеру по URL --
    //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
    },
    
    
    
    closeDialogInfo : function(event)
    {
        // Отменим поведение по умолчанию
        //        Event.stop(event);
        
        BSA.Dialogs.closeDialogInfo();
        
    //        window.location =  lb.getMsg('urlBase') + this.dialog_info.url_cancel;
        
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
// пр. $H(BlogView: [new BlogView(param1), ... ,new BlogView(paramN)])
BSA.BlogView.RegRunOnLoad = function() {
    // Получим параметры для создания обьекта
    var params = scriptParams.get('BlogView');
    // Ф-ия создания обьектов по их параметрам
    var createObject = function (param) {
        var view = scriptInstances.get('BlogView');
        if (view) {
            view.push(new BSA.BlogView(param));
        } else {
            scriptInstances.set('BlogView', [new BSA.BlogView(param)]);
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
runOnLoad(BSA.BlogView.RegRunOnLoad);