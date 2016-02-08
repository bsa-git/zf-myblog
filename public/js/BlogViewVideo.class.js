/**
 * BlogViewVideo - Class
 *
 * С помощью класса BlogViewVideo вы можете:
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
BSA.BlogViewVideo = Class.create({
    
    fp: null,// Flowplayer
    container : null,   // Контейнер для видео
    url: '',// URL для получения с сервера списка параметров видео 
    listCheckVideo: new Hash(), // Список, проверенных на доступность видео
    img: null,
    li: null, // Контейнер для видео
    id: 0,    // ID для видео
    options_youtube: {	
        overlay: true, // if false embed the video player directly		
        // overlay and preview options
        duration: 0.5, // overlay appare/fade effect
        opacity: 0.8,  // overlay opacity
        imagePreview: true, // show video thumb
        imageID: 1, // 0,1,2,3			
        // player configuration
        playerWidth: 425,
        playerHeight: 350,
        fs: 1, // fullscreen button
        autoplay:0,
        loop:0,
        hd:1, // High definition
        showinfo:0, // show video title and rating before start 
        rel:1, // show related video at end			
        // You Tube url
        youtubeVideoUrl: 'http://www.youtube.com/v/',
        youtubeImageUrl: 'http://img.youtube.com/vi/'
    },
    accordion: null,// Аккордион
    isDownloaded: false, // Признак загрузки изображений
    actual: false, // Признак состояния аккордиона (открыт или закрыт)
    isUpdateURL: false,// Признак обновления URL
    
    initialize : function(params)
    {
        if(params.container){
            this.container = $(params.container);
            this.url = lb.getMsg('urlBase') + params.url;
        }else{
            return;
        }
        
        if(params.options_youtube){
            Object.extend(this.options_youtube, params.options_youtube || {});
        }
        
        this.container.select('div.video-row').each(function(video){
            var wrap = video.down('a.player');
            var clip_id = wrap.up('div.video-row').readAttribute('id').split('_')[1];
            // Установим событие нажатия кнопки PLAY
            wrap.observe('click', function() {
                // Сохраним событие для статистики
                new Ajax.Request(this.url, {
                    parameters : {
                        type_action: 'play',
                        clip_id: clip_id
                    },
                    // Успешный ответ
                    onSuccess: function(response) {
                        // Получим данные ответа
                        var json = BSA.Sys.getJsonResponse(response, true);
                        if(json.result !== 'OK'){
                            this.onFailure('Error saving statistics');
                        }
                    }
                });
                
                //если вызывается проигрыватель не FlowPlayer 
                if(! wrap.hasClassName('flow-player')){
                    //Очистим сообщение об ошибке
                    BSA.Sys.messagebox_clear();
                    BSA.Sys.message_clear();
                    
                    if($f().isLoaded()){
                        $f().stop();
                        $f().close();
                        $f().unload();
                    }
                }
            }.bind(this))	
            
            if(wrap.hasClassName('flow-player')){// Установим событие для кнопки "Close"
                var divCloseVideo = video.down('div.player-close');
                var aCloseVideo = divCloseVideo.down('a');
                aCloseVideo.observe('click', this.onCloseVideo.bindAsEventListener(this));
            }
        }.bind(this))
        
        // Установим ссылку на загрузку FlashPlayer с моего сайта как альтернативу
        BSA.Sys.downloadFlashPlayer(this.container);
        
        // Определим наличие аккордиона
        if (params.accordion){
            this.accordion = params.accordion;
            // Подпишемся на события в аккордионе
            this._subscribeAccordionEvents();
        }else{
            // Получим данные о видео файлах
            this.getPlaylist();
        }
    },
    
    // Выравнивание видео посредине
    _setAllignVideo : function(container) {
        var dimensionsContainer = null;
        //----------------------
        if(container){
            dimensionsContainer = container.getDimensions();
            var rowVideo = container.up('div.video-row');
            var dimensionsRowVideo = rowVideo.getDimensions();
            var left = Math.floor((dimensionsRowVideo.width - dimensionsContainer.width)/2);
            container.setStyle({
                marginLeft: left + 'px'
            });
            return true;
        }else{
            return false;
        }
    },
    
    //============== Работа с видео FlowPlayer =============
    
    getPlaylist:function(){
	
        // Инициализация Ajax запроса
        new Ajax.Request(this.url, {
            parameters : {
                type_action: 'playlist'
            },
            // Успешный ответ
            onSuccess: function(response) {
                
                try {
                    // Получим данные ответа
                    var playlist = BSA.Sys.getJsonResponse(response, true);
                    
                    // Проверим есть ли ошибки
                    if (playlist.class_message) {// ERROR
                        // Создадим плееры
                        this.buildFlowPlayer(null);
                    }else{ // OK
                        // Создадим плееры
                        this.buildFlowPlayer(playlist);
                    }
                    this.buildProtoTubePlayer();
                } catch (ex) {
                    if (ex instanceof Error) { // Это экземпляр Error или подкласса?
                        this.onFailure(ex.name + ": " + ex.message);
                    }

                } finally {
                    BSA.Sys.message_clear();
                }
            }.bind(this)
        });
		
    },
    
    onCloseVideo:function(event){
        Event.stop(event);
        
        //Очистим сообщение об ошибке
        BSA.Sys.messagebox_clear();
        BSA.Sys.message_clear();
        
        var aCloseVideo = Event.element(event);
        var divCloseVideo = aCloseVideo.up('div');
        var wrap = divCloseVideo.next('a.player');
        
        // Выгрузим предыдущий загруженный плеер
        var player = $f(wrap);
        player.unload();
    },
    
    //-------------- Clip типа - MP3 -----------------
    
    fpCliptype_mp3: function(itemPlayList){
        // Определим характеристики клипа
        var clip_mp3 = {
            provider: "audio",
            onCuepoint: [itemPlayList['cuepoints'], this.fpCuepoint_mp3]
        };
        Object.extend(this.fpClip, clip_mp3 || {});
                        
        // Определим характеристики плагинов
        var plugins_mp3 = {
            controls: {
                backgroundColor:'#002200',
                height: 30,
                fullscreen: false
            },
            // "myContent" is the name of our plugin
            myContent: {

                // location of the plugin
                url: lb.getMsg('urlRes') + '/js/flowplayer/flowplayer.content-3.2.8.swf',
                // display properties
                top: 0,
                bottom: 0,
                left: 0,
                right: 0,
                opacity:1,
                width: '100%',
                height: '100%',
                zIndex: 0,

                // styling properties
                borderRadius: 0,
                // "inline" styling (overrides external stylesheet rules),
                style: {
                    '.title': {
                        fontSize: 20,
                        fontFamily: 'verdana,arial,helvetica',
                        color: '#ffffff'
                    }

                },
                html: ''
            },
            audio: {
                url: lb.getMsg('urlRes') + '/js/flowplayer/flowplayer.audio-3.2.9.swf'
            }
        }
        
        Object.extend(this.fpPlugins, plugins_mp3 || {});
    },
    
    fpCuepoint_mp3: function(clip, cuepoint){
        var self = this;
        //----------------------
        var contentPlugin = self.getPlugin("myContent");
        // Обновить изображение или текст
        if (cuepoint.image) {
            //Делает плагин контекста невидимым
            contentPlugin.fadeOut(2000,function(){
                contentPlugin.setHtml('');
                contentPlugin.css({
                    backgroundImage:"url("+ cuepoint.image +")"
                });
                //Делает плагин контекста видимым
                contentPlugin.fadeIn(2000,function(){
                    if (cuepoint.title) {
                        contentPlugin.setHtml('' + cuepoint.title);
                    }
                });
            });
        }
        // Добавить флеш плагин
        if (cuepoint.flash) {
            if(cuepoint.location)
                self.loadPlugin("swfPlugin", cuepoint.flash, cuepoint.location);
            else
                self.loadPlugin("swfPlugin", cuepoint.flash);
        }
    },
    
    //-------------- Создание FlowPlayer -----------------
    
    // Конфигурация клипа
    fpClip: {},
    
    // Конфигурация плагина
    fpPlugins: {},
    
    // Конфигурация клипа по умолчанию
    fpDefaultClip: {
        scaling: 'fit',
        onStart: function () {
            // show play button on pause
            this.getPlugin('play').show();
        },
        onBeforeFinish: function () {
            // Скроем кнопку повторить проигрывание видео
            this.getPlugin('play').hide();
        }
    },
    // Конфигурация плагина по умолчанию
    fpDefaultPlugins: {
        controls: {
            backgroundColor:'#002200',
            height: 30
        }
    },
    
    buildFlowPlayer: function(playlist){
        var self = this;
        var clipType;
        //-------------------
        var flowPlayers = this.container.select('a.flow-player');
        flowPlayers.each(function(flowPlayer) {
            
            // Установим параметры по умолчанию
            Object.extend(self.fpClip, self.fpDefaultClip || {});
            Object.extend(self.fpPlugins, self.fpDefaultPlugins || {});
            
            // Получим clip_id
            var clip_id = flowPlayer.up('div.video-row').readAttribute('id').split('_')[1];
            // Найдем данные на каждый клип в playlist
            playlist.each(function(pl) {
                if(pl.clip_id == clip_id){
                    // Определим тип клипа
                    clipType = pl['clip_type'].split('-')[1];
                    //Определим конфигурацию для файлов типа - mp3
                    if(pl['cuepoints'] && clipType == 'mp3'){
                        self.fpCliptype_mp3(pl);
                    }
                    return;
                }
            });
            // Создадим плеер и сконфигурируем его
            new flowplayer(flowPlayer, lb.getMsg('urlRes') + '/js/flowplayer/flowplayer-3.2.15.swf', {
                clip: self.fpClip,
                plugins: self.fpPlugins,
                // Выполним пользовательские действия до нажатия на кнопку играть видео
                onBeforeClick: function() {
                    
                    //Очистим сообщение об ошибке
                    BSA.Sys.messagebox_clear();
                    BSA.Sys.message_clear();
                    
                    // Сбросим признак обновления URL
                    self.isUpdateURL = false;
                    
                    $f().unload();

                    // Получим обьект контейнера плеера
                    var wrap = $(this.getParent());
                
                    //Получим  и преобразуем background-image
                    var imageBackground = wrap.getStyle('background-image');
                    imageBackground = imageBackground.replace('128x128','300x300');

                    // Скроем кнопку играть на плеере
                    wrap.down("img").hide();

                    // Установим новое фоновое изображение
                    wrap.setStyle({
                        background: imageBackground
                    }); 
                    wrap.setStyle({
                        backgroundRepeat: 'no-repeat'
                    }); 
                
                    // Скроем информацию о видео
                    wrap.next('div.video-info').hide();
                
                    // Покажем кнопку закрытия видео
                    wrap.previous('div.player-close').show();
                
                    // Выполним эффект плавного увелечения размера контейнера плеера
                    var options = {
                        style: 'width:425px;height:300px;', // CSS Properties width:406px; height:303px
                        afterFinish : function() {
                            // Когда аннимация будет окончена, то загрузим плеер
                            $f(wrap).load();
                        
                            // Выровняем плеер посредине контейнера
                            self._setAllignVideo(wrap);
                        }
                    }

                    new Effect.Morph(wrap, options);
                    // Запретим стандартное поведение по клику
                    return false;
                },
            
                onLoad: function() {
                    self.fp = this;
                },
                onBeforeUnload: function() {
                },
                
                // Событие выгрузки плеера и возвращение к оригинальным размерам плеера
                onUnload: function() {
                    // Получим обьект контейнера плеера
                    var wrap = $(this.getParent());
                
                    // Скроем кнопку закрытия видео
                    wrap.previous('div.player-close').hide();
                
                    //Получим  и преобразуем background-image
                    var imageBackground = wrap.getStyle('background-image');
                    imageBackground = imageBackground.replace('300x300','128x128');
                
                    // Установим новое фоновое изображение
                    wrap.setStyle({
                        background: imageBackground
                    }); 
                    wrap.setStyle({
                        backgroundRepeat: 'no-repeat'
                    }); 
                    wrap.setStyle({
                        marginLeft: '0px'
                    });
                
                    // Выполним эффект плавного уменьшения размера контейнера плеера
                    var options = {
                        style: 'width:128px; height:128px', // CSS Properties
                        //                    duration    : 2,
                        afterFinish : function() {
                            // when animation finishes we will load our player
                            // hide nested play button
                            wrap.down("img").show();
                        
                            // Покажем информацию о видео
                            wrap.next('div.video-info').show();
                        }
                    }

                    new Effect.Morph(wrap, options);
                },

                // Событие завершения видео
                onFinish: function() {
                    // Получим обьект контейнера плеера
                    var wrap = $(this.getParent());
                
                    // Если это не флеш видео, то выгрузим его
                    if(! wrap.hasClassName('swf')){
                        this.unload();
                    }
                },
                
                // Событие ошибки
                onError: function(errorCode, errorMessage ) {
                    // Получим обьект контейнера плеера
                    var wrap = $(this.getParent());
                    // Получим clip_name
                    var clip_name = wrap.next('div.video-info').down('.video-title').innerHTML;
                    // Получим clip_id
                    var clip_id = wrap.up('div.video-row').readAttribute('id').split('_')[1];
                    
                    // Сообщение об ошибке
                    var errMessage = {
                        class_message : 'warning',
                        messages : [
                        '<em>' + lb.getMsg('msgErrVideo') + '! (id='+ clip_id +') - "' + clip_name +'"</em>',
                        lb.getMsg('msgErrCode') + ': ' + errorCode,
                        lb.getMsg('msgErrMessage') + ': ' + errorMessage
                        ]
                    }
                    //----------------------------
                    if(wrap.hasClassName('godtv')){
                        if(!self.isUpdateURL){
                            self.errorFlowPlayer(this);
                        }else{
                            self.onFailure(errMessage);
                        }
                        
                    }else{
                        self.onFailure(errMessage);
                    }
                }
            });
            // Очистим параметры
            self.fpClip = {};
            self.fpPlugins = {};
        });
    },
    
    errorFlowPlayer: function(player){
        // Получим обьект контейнера плеера
        var wrap = $(player.getParent());
        //----------------------------
        
        if(wrap.hasClassName('godtv')){
            
            // Получим clip_name
            var clip_name = wrap.next('div.video-info').down('.video-title').innerHTML;
            // Получим clip_id
            var clip_id = wrap.up('div.video-row').readAttribute('id').split('_')[1];
            
            // Инициализация Ajax запроса
            new Ajax.Request(this.url, {
                parameters : {
                    type_action: 'godtv_url',
                    clip_name: clip_name,
                    clip_id: clip_id
                },
                // Успешный ответ
                onSuccess: function(response) {
                
                    try {
                        // Получим данные ответа
                        var json = BSA.Sys.getJsonResponse(response, true);
                    
                        // Проверим есть ли ошибки
                        if (! json.class_message) {// OK
                            // Установим признак обновления URL
                            this.isUpdateURL = true;
                            // Установим новый путь к клипу
                            player.setClip(json.url).play();
                            
                            // Установим обновленный URL
                            wrap.href = json.url;
                        }
                        this.buildProtoTubePlayer();
                    } catch (ex) {
                        if (ex instanceof Error) { // Это экземпляр Error или подкласса?
                            this.onFailure(ex.name + ": " + ex.message);
                        }

                    } finally {
                        BSA.Sys.message_clear();
                    }
                }.bind(this)
            });
        }
    },

    
    //-------------- Работа с видео ProtoTubePlayer ---------------
    
    buildProtoTubePlayer: function(){
        var self = this;
        var image = '';
        //------------------
        var urlList = this.container.select('a.proto-tube');
        urlList.each(function(video){
            
            try{
                //                var pt = new ProtoTube(video, self.options_youtube);
                new ProtoTube(video, self.options_youtube);
                
                if(Prototype.Browser.IE){
                    image = video.down('img');
                    image.writeAttribute('src',lb.getMsg('urlRes')+'/images/media/play.png');
                }else{
                    image = video.down('img');
                    var urlImage = image.readAttribute('src');
                    var url = new URL();
                    var isparseURL = url.parseURL(urlImage);
                    if(isparseURL){
                        image.writeAttribute('src',lb.getMsg('urlRes')+'/images/media/play.png');
                    }else{
                        video.up('div.video-row').hide();
                    }
                }
            } catch (ex) {
                if (ex instanceof Error) { // Это экземпляр Error или подкласса?
                    video.up('div.video-row').hide();
                }

            }
        })
    },
    
    
    //========== РАБОТА С АККОРДИОНОМ ===========//

    // Подпишемся на события в аккордионе
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
    
    // Свернуть секцию в аккордионе
    onHiddenSectionEvent : function(self, params) {
        var section = params.section.elements.section;
        var hrefSection = section.down('a').readAttribute('href');
        if(hrefSection == self.accordion.section){
            self.actual = false;
        }
    },
    
    // Развернуть секцию в аккордионе
    onShownSectionEvent : function(self, params) {
        var section = params.section.elements.section;
        var hrefSection = section.down('a').readAttribute('href');
        if(hrefSection == self.accordion.section){
            self.actual = true;
            
            // Загрузим изображения на страницу, если они еще не были загружены
            if(! self.isDownloaded){
                self.isDownloaded = true;
                
                // Получим данные о видео файлах
                self.getPlaylist();
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
// пр. $H(BlogViewVideo: [new BlogViewVideo(param1), ... ,new BlogViewVideo(paramN)])
BSA.BlogViewVideo.RegRunOnLoad = function() {
    // Получим параметры для создания обьекта
    var params = scriptParams.get('BlogViewVideo');
    // Ф-ия создания обьектов по их параметрам
    var createObject = function (param) {
        var viewVideo = scriptInstances.get('BlogViewVideo');
        if (viewVideo) {
            viewVideo.push(new BSA.BlogViewVideo(param));
        } else {
            scriptInstances.set('BlogViewVideo', [new BSA.BlogViewVideo(param)]);
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
runOnLoad(BSA.BlogViewVideo.RegRunOnLoad);