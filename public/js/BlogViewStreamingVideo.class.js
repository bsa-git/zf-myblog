/**
 * Class - BlogViewStreamingVideo
 * 
 * With these class you can:
 *  - view streaming videos of different formats
 *
 * JavaScript
 *
 * @author   Sergii Beskorovainyi <bsa2657@yandex.ru>
 * @license  MIT <http://www.opensource.org/licenses/mit-license.php>
 * @link     https://github.com/bsa-git/zf-myblog/
 */
BSA.BlogViewStreamingVideo = Class.create({
    
    fp: null,// Flowplayer
    accordion: null,// Accordion
    isDownloaded: false, // Is downloaded images
    actual: false, // Tag accordion state (open or closed)
    
    // Object initialization
    initialize: function(params) {
        var divCloseVideo = null;
        var aCloseVideo = null;
        //-----------------------
        
        if(! $(params.container)){
            return;
        }
        
        this.options = {
            Url: 'data.json',
            Autoplay: false
        } 
        
        if(params.options){
            params.options.Url = lb.getMsg('urlBase') + params.options.Url;
        }
        
        Object.extend(this.options, params.options || {});
		
        this.container = $(params.container) ? $(params.container) :  $$('body')[0];
        
        // Установим ссылку на загрузку FlashPlayer с моего сайта как альтернативу
        BSA.Sys.downloadFlashPlayer($('post-streaming-video'));
        
        // Assign button event handler "player-close"
        divCloseVideo = $('post-streaming-video').down('div.player-close');
        if(divCloseVideo){
            aCloseVideo = divCloseVideo.down('a');
            aCloseVideo.observe('click', this.onCloseVideo.bindAsEventListener(this));
        }
        // Is accordion
        if (params.accordion){
            this.accordion = params.accordion;
            // Subscribe to the events in the accordion
            this._subscribeAccordionEvents();
        }else{
            // Get details about the video
            this.getPlaylist();	
        }
    },
	
    getPlaylist:function(){
	
        // Инициализация Ajax запроса
        new Ajax.Request(this.options.Url, {
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
                } catch (ex) {
                    if (ex instanceof Error) { // Это экземпляр Error или подкласса?
                        this.onFailure(ex.name + ": " + ex.message);
                    }

                } finally {
                    BSA.Sys.message_clear();
                }
            }.bind(this),
            // Ошибочный ответ
            onFailure : function(transport) {// Error Ajax.Request
                var errText = transport.responseText;
                var msgs = [lb.getMsg('msgErrVideo'), errText];
                BSA.Sys.messagebox_write('warning', msgs);
                self.onFailure(lb.getMsg('msgErrVideo'));
                
            }
        });
		
    },
    // The default configuration of the clip
    fpDefaultControls: {
        backgroundColor:'#002200',
        height: 30,
        // which buttons are visible and which are not?
        play:true,
        volume:true,
        mute:true,
        time:true,
        stop:false,
        scrubber: true,
        fullscreen:true,
        autoHide: true,
        playlist: true
    },
    
    buildFlowPlayer: function(playlist){
        var self = this;
        var playlist_ = [];
        var provider_ = 'rtmp';
        var live_= true;
        var url_ = '';
        var controls_ = null;
        var type_ = 'clip';
        var urlResolvers_ = null;
        var oUrl = null;
        //-------------------
        // Получим список видео
        playlist.each(function(clip){
            if(clip.clip_type == 'url-rtmp' || clip.clip_type == 'url-pseudostreaming' || clip.clip_type == 'url-httpstreaming'){
                type_ = 'clip';
                provider_ = 'rtmp';
                live_= true;
                url_ = '';
                controls_ = self.fpDefaultControls;
                urlResolvers_ = null;
                // Разложим URL на составляющие
                oUrl = new URL();
                oUrl.parseURL(clip.url);
                var params = oUrl.params;
                params.each(function(p) {
                    if(p.name == 'url'){
                        url_ = p.value;
                    }
                    if(p.name == 'provider'){
                        provider_ = p.value;
                        if(provider_ == 'httpstreaming'){
                            urlResolvers_ = ['f4m'];
                        }
                    }
                    if(p.name == 'live'){
                        live_ = p.value;
                    }
                    if(p.name == 'type'){
                        type_ = p.value;
                        switch (p.value) {
                            case 'tv':
                                controls_ = {
                                    play:true,  
                                    scrubber: false,
                                    autoHide: true
                                };
                                break;
                            case 'radio':
                                controls_ = {
                                    scrubber: false,
                                    fullscreen: false,
                                    autoHide: false
                                };
                                break;
                            default:
                                controls_ = self.fpDefaultControls;
                                break;
                        }
                    }
                })
                if(url_){
                    url_ = oUrl.scheme +'://' + oUrl.host + oUrl.path + '/' + url_;
                }else{
                    url_ =  oUrl.scheme +'://' + oUrl.host + oUrl.path;
                }
                playlist_.push({
                    clip_type: type_,
                    url: url_, 
                    provider: provider_,
                    live: live_,
                    urlResolvers: urlResolvers_,
                    controls: controls_
                })
            }
        });
        
        //------ Create a player object ------
        this.fp = flowplayer(self.container, {
            src:lb.getMsg('urlRes') + '/js/flowplayer/flowplayer-3.2.15.swf'
        //            width:450, 
        //            height:300
        }, {
            plugins: {
                controls: self.fpDefaultControls,
                // this plugin provides access to servers that support the Real
                // Time Messaging Protocol (RTMP)
                rtmp: {
                    // path to provider implementation
                    url: lb.getMsg('urlRes') + '/js/flowplayer/flowplayer.rtmp-3.2.11.swf',
                    failOverDelay: 1000
                },
                // access to "pseudo-streaming" servers such as lighthttpd
                pseudo: {
                    url: lb.getMsg('urlRes') + '/js/flowplayer/flowplayer.pseudostreaming-3.2.11.swf'
                },
                f4m: {
                    url: lb.getMsg('urlRes') + "/js/flowplayer/flowplayer.f4m-3.2.9.swf",
                    dvrBufferTime: 12,
                    liveBufferTime: 12
                },
 
                httpstreaming: {
                    url: lb.getMsg('urlRes') + "/js/flowplayer/flowplayer.httpstreaming-3.2.9.swf"
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
                    html: ''
                }
            },
            clip: {
                scaling: 'fit',
                autoPlay: self.options.Autoplay,
                //                url: 'rtmp://37.220.162.129/secure/mp4:TVRain_640k.stream',
                live: true,
                provider: 'rtmp',
                onStart: function (clip) {
                    // show play button on pause
                    this.getPlugin('play').show();
                    
                    //throw new Exc
                },
                onBeforeFinish: function () {
                    // Скроем кнопку повторить проигрывание видео
                    this.getPlugin('play').hide();
                }
            },
            screen: {
                width: 425,
                height: 300,
                left:1
            },
            // our playlist
            playlist: playlist_,
            onLoad: function() {
                if(! self.options.Autoplay){
                    this.play(0);
                }
            },
            
            // Error event
            onError: function(errorCode, errorMessage ) {
                // Получим обьект контейнера плеера
                //var wrap = $(this.getParent());
                var clip_id = this.getClip().index;
                var clip = $("streaming-video-list").down('a[href='+ clip_id + ']');
                var clip_name = clip.innerHTML;
                
                // Сообщение об ошибке
                var errMessage = {
                    class_message : 'warning',
                    messages : [
                    '<em>' + lb.getMsg('msgErrVideo') + '! (id='+ clip_id +') - "' + clip_name +'"</em>',
                    lb.getMsg('msgErrCode') + ': ' + errorCode,
                    lb.getMsg('msgErrMessage') + ': ' + errorMessage
                    ]
                }
                
                self.onFailure(errMessage);
            }
        });
        this.addObservers();
    },
    
    onCloseVideo:function(event){
        var self = this;
        var indexSection = self.accordion.section;
        var section;
        var idTimeout;
        //---------------------
        if(event){
            Event.stop(event);
        }

        self.fp.stop();
        
        var boxAccordions = scriptInstances.get('AccordionBox');
        boxAccordions.each(function(box){
            if(box.id == self.accordion.id ){
                // Получим соответсвующую секцию и закроем ее
                idTimeout = window.setTimeout(function() {
                    section = box.accordion.sections[indexSection];
                    box.accordion.hideSection(section);
                    window.clearTimeout(idTimeout);
                }, 300);
            }
        })
    },
    
    addObservers: function(){
        var self = this;
        //---------------------------------
        
        // Подпишемся на событие плеера - "onStart"
        self.fp.getCommonClip().onStart(function(clip){
            var contentPlugin = this.getPlugin("myContent");
            if(clip.clip_type == 'radio'){
                contentPlugin.css({
                    backgroundImage:"url("+ lb.getMsg('urlRes') + '/images/media/player/music425x320.jpg' +")"
                });
            }else{
                contentPlugin.css({
                    backgroundImage:"",
                    backgroundColor: '#000000'
                });
            }
            
            self.setStateItem('start', clip.index);
            
        });
        // Подпишемся на событие плеера - "onPause"
        self.fp.getCommonClip().onPause(function(clip){
            self.setStateItem('pause', clip.index);
        });
        // Подпишемся на событие плеера - "onStop"
        self.fp.getCommonClip().onStop(function(clip){
            if(clip.clip_type == 'radio' || clip.clip_type == 'tv'){
                self.setStateItem('pause', clip.index);
            }else{
                self.setStateItem('stop', clip.index);
            }
            
        });
        // Подпишемся на событие плеера - "onFinish"
        self.fp.getCommonClip().onFinish(function(clip){
            self.setStateItem('finish', clip.index);
        });
        // Подпишемся на событие плеера - "onResume"
        self.fp.getCommonClip().onResume(function(clip){
            self.setStateItem('resume', clip.index);
        });
        

        // Установим события click для списка потокового видео
        var urlList = $('streaming-video-list');
        urlList.select('a').each(function(clip){
            clip.observe('click', function(e) {
                var isRepeat = false;
                var self = this;
                //-------------------
                Event.stop(e);
                
                var wrap = Event.element(e);
                var idClip = wrap.readAttribute('href') * 1;
                
                if(this.fp.getClip()){
                    if(this.fp.getClip().index == idClip){// Продолжаем играть тот же клип
                        if(this.fp.isPaused()){
                            this.fp.play();
                        }
                    }else{// Играем новый клип
                        // Стоп предыдущего клипа
                        this.fp.stop();  
                        // Играем 
                        setTimeout( function() {
                            self.fp.play(idClip);
                        } , 1000)
                    }
                    
                    isRepeat = this.fp.getClip().index == idClip;
                    if(isRepeat){
                        this.setStateItem('start', idClip);
                    }
                }
                
            }.bindAsEventListener(self));	
        });
    },
    
    setStateItem: function(state, index) {
        var indexItem = null;
        //------------------------
        // Получим список всех файлов
        var urlList = $('streaming-video-list');
        
        // Установим соответствующее состояние для каждого аудио файла в списке
        urlList.select('div').each(function(item){
            indexItem = item.next('a').readAttribute('href') * 1;
            
            if(indexItem == index){
                switch (state) {
                    case 'pause':
                        item.className = 'audio-pause';
                        break
                    case 'start':
                    case 'resume':
                        item.className = 'audio-play';
                        break
                    default:
                        item.className = 'streaming-video-item';
                        break;
                }
 
            }else{
                item.className = 'streaming-video-item';
            }
            
        });
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
            $f(self.container).stop();
        }
        
    },
    
    // Show section in the accordion
    onShownSectionEvent : function(self, params) {
        var section = params.section.elements.section;
        var hrefSection = section.down('a').readAttribute('href');
        if(hrefSection == self.accordion.section){
            self.actual = true;
            
            // Загрузим изображения на страницу, если они еще не были загружены
            if(! self.isDownloaded){
                self.isDownloaded = true;
                
                // Получим данные о видео
                self.getPlaylist();
            }
        }
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
// ex. $H(BlogViewStreamingVideo: [new BlogViewStreamingVideo(param1), ... ,new BlogViewStreamingVideo(paramN)])
BSA.BlogViewStreamingVideo.RegRunOnLoad = function() {
    // Получим параметры для создания обьекта
    var params = scriptParams.get('BlogViewStreamingVideo');
    // Ф-ия создания обьектов по их параметрам
    var createObject = function (param) {
        var viewStreamingVideo = scriptInstances.get('BlogViewStreamingVideo');
        if (viewStreamingVideo) {
            viewStreamingVideo.push(new BSA.BlogViewStreamingVideo(param));
        } else {
            scriptInstances.set('BlogViewStreamingVideo', [new BSA.BlogViewStreamingVideo(param)]);
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
runOnLoad(BSA.BlogViewStreamingVideo.RegRunOnLoad);