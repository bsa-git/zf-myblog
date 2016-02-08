/**
 * BSA.Dialogs - обьект диалоговых ф-ий
 *
 * С помощью обьекта вы можете:
 *  - выводить ProgressBar
 *  - выводить сообщения
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

BSA.Dialogs = {
    
    timeout:  0, // Время периода обновления данных в 
    intervalID: null,
    
    createOverlay: function(){
        if(! $('overlay-modal')){
            var overlay = '<div id="overlay-modal" class="overlay-dialog" style="position: absolute; top: 0px; left: 0px; z-index: 100; width: 100%; opacity: 0.6000000238418579; height: 6297px; "></div>';
            var body = $$('body')[0];
            body.insert({
                top: overlay
            });
        }
    },
    
    createIFrame: function(params){
        var body = $$('body')[0];
        //var iFrame = $$('iframe')[0];
        var iFrame = $('zend-progress-iframe');
        //---------------------------
        if(! iFrame){
            // Создадим элемент 'iframe' для управления клиентом 
            // со стороны сервера и вставим его в страницу
            if(params.type == 'ZendProgress'){
                iFrame = new Element('iframe', {id: 'zend-progress-iframe',  src: lb.getMsg('urlBase') + params.iframe_src});
                body.insert({
                    top: iFrame
                });
            }
        }
    },
    
    createDialogContainer: function(params){
        var dialog_container = '';
        var dialogWidth = 0;
        var dialogHeight = 0;
        var body = $$('body')[0];
        //---------------------------
        if(! $('modal-dialog-message')){
            
            // Создадим контейнер диалога и вставим его в страницу
            if(params.width && params.height){
                // Создадим шаблон
                dialog_container = '<div id="modal-dialog-message" class="dialog-content" style="width:#{width}px;height:#{height}px"></div>';
                var template = new Template(dialog_container);
                var show = {
                    width: params.width,
                    height: params.height
                };
                // Получим шаблон с данными
                dialog_container = template.evaluate(show);
            }else{
                dialog_container = '<div id="modal-dialog-message" class="dialog-content"></div>';
            }
            
            // Вставим элемент в страницу
            body.insert({
                top: dialog_container
            });
        
            // Получим размер документа
            var dimensions = document.viewport.getDimensions();//{ width: 776, height: 580 }
            // Установим диалог на середине окна
            var dialog = $('modal-dialog-message');
            if(params.width && params.height){
                dialogWidth = params.width;
                dialogHeight = params.height;
            }else{
                var layout = new Element.Layout(dialog);
                dialogWidth = layout.get('width');
                dialogHeight = layout.get('height');
            }
            
            var dialogLeft = Math.floor((dimensions.width - dialogWidth)/2);
            var dialogTop = Math.floor((dimensions.height - dialogHeight)/2);
            dialog.setStyle({
                left: dialogLeft + 'px',
                top: dialogTop + 'px'
            });
        }
    },
    
    openDialogInfo : function(params)
    {
        var self = this;
        //-------------------------
        
        // Установим таймаут
        self.timeout = 0;
        
        // Создадим диалог и оверлей
        this.createDialogContainer(params);
        this.createOverlay();
        
        // Получим содержимое для диалога
        var template = this.getTemplateFor_DialogInfo(params);
        var showData = this.getDataTemplateFor_DialogInfo(params);
        var content = template.evaluate(showData);
        
        // Установим содержание диалога
        this.setInfoMessage(content);
        
        // Создадим элемент - 'iFrame' 
        if(params.type == 'ZendProgress'){
            this.createIFrame(params);
            return;
        }
        
        // Определим переодическую функцию для обновления данных
        this.intervalID = window.setInterval(function() {
            
            self.timeout++;
            
            // Получим содержимое для диалога
            showData = self.getDataTemplateFor_DialogInfo(params);
            content = template.evaluate(showData);
            
            // Обновим содержание диалога
            self.setInfoMessage(content);
            
        }.bind(self), 1000);
    },
    
    setInfoMessage: function(message) {
        if($('modal-dialog-message')){
            $('modal-dialog-message').update(message);
        }
        
    },
    
    closeDialogInfo : function(event)
    {
        if($('overlay-modal')){
            $('overlay-modal').remove();
        }
        
        if($('modal-dialog-message')){
            $('modal-dialog-message').remove();
        }
        
        if($('zend-progress-iframe')){
            $('zend-progress-iframe').remove();
        }
        if(this.intervalID){
            clearInterval(this.intervalID);
        }
    },
    
    getTemplateFor_DialogInfo : function(params)
    {
        var msg = '';
        var strTemplate = '';
        var strMsg = '';
        var strProgress = '';
        var strCancel = '';
//        var myBrowser = BSA.Sys.getBrowserType(); 
        //-------------------------
        
        switch(params.type) {
            case 'ZendProgress': // Диалог ожидания окончания операции сервера c помощью Zend_Progress
            case 'WaiteServerAction': // Диалог ожидания окончания операции сервера
        
                // Созадание шаблона
                strMsg = '<h3>#{msg}</h3>';
        
                // Создадим шаблон сообщения в DialogInfo
                // Особенность! Если эксплоер = Хром, то картинки не вызываются
                // поэтому делаем анализ и уходим от изображений...
                strProgress = '<div id="progress-bar">' +
                '<div class="pg-progressbar">' +
                '<div class="pg-progress" id="pg-percent" style="width: #{width}%">' +
                '<div class="pg-progressstyle" '+ (Prototype.Browser.WebKit? 'style="background: #0782C1"':'') + ' ></div>' +
                '<div class="pg-invertedtext" id="pg-text-1">#{text1}</div>' +
                '</div>' +
                '<div class="pg-text" id="pg-text-2">#{text2}</div>' +
                '</div>' +
                '</div>' +
                '<div id="progressBar"><div id="progressDone"></div></div>';
        
                if(params.cancel){
                    strCancel = '<br /><br /><br />'+
                    '<div id="cancel-dialog-info">'+
                    '<a href="#{url_cancel}">'+
                    (Prototype.Browser.WebKit? '#{cancel}':'<img src="#{url_image}" alt title="#{cancel}" />') +
                    '</a>'+
                    '</div>';
                }
        
                strTemplate = strMsg + strProgress + strCancel;
                break; 
            default: // Если все остальное не подходит...
                break; // Здесь останавливаемся
        }
        return new Template(strTemplate);
    },
    
    getDataTemplateFor_DialogInfo : function(params)
    {
        var msg = '';
        var min = 0;
        var sec = 0;
        var timeProgress = '';
        var show = null;
        //-----------------
        switch(params.type) {
            case 'ZendProgress': // Диалог ожидания окончания операции сервера c помощью Zend_Progress
            case 'WaiteServerAction': // Диалог ожидания окончания операции сервера
                // Определим сообщение
                if(params.msg){
                    msg = params.msg;
                }else{
                    msg = lb.getMsg('msgIsPreparingReport');
                }
                
                // Создадим обьект с данными
                show = {
                    msg: msg, 
                    url_cancel: lb.getMsg('urlRes') + params.url_cancel,
                    url_image: lb.getMsg('urlRes') + '/images/system/stop-error.png',
                    cancel: lb.getMsg('msgCancel'),
                    width: this.timeout,
                    text1: '',
                    text2: ''
                };
                
                // Установим данные
                if(this.timeout > 0){
                    if(this.timeout >= 60){
                        min =  Math.floor(this.timeout/60);
                        sec = this.timeout%60;
                        if(sec == 0){
                            timeProgress = min + ' ' + lb.getMsg('msgMinutes') + ' ';
                    
                        }else{
                            timeProgress = min + ' ' + lb.getMsg('msgMinutes') + ' ' + sec + ' ' + lb.getMsg('msgSeconds') + ' ';
                        }
               
                    }else{
                        timeProgress = this.timeout + ' сек.';
                    }
            
                    // Обновим время в обьекте данных
                    show.width = ((this.timeout % 10)+1)*10;
                    show.text1 = timeProgress;
                    show.text2 = timeProgress;
                }
        
                break;
            default:
                break; 
        }
        return show;
    },
    
   
    Zend_ProgressBar_Update: function(data){
        document.getElementById('pg-percent').style.width = data.percent + '%';
        document.getElementById('pg-text-1').innerHTML = data.text;
        document.getElementById('pg-text-2').innerHTML = data.text;
    },


    Zend_ProgressBar_Finish: function()
    {
        document.getElementById('pg-percent').style.width = '100%';
        document.getElementById('pg-text-1').innerHTML = 'Demo done';
        document.getElementById('pg-text-2').innerHTML = 'Demo done';
        
        this.closeDialogInfo();
    }
}

