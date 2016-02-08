/**
 * BlogAudioManager - Class
 *
 * Класс для управления проигрыванием аудио файлов
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
BSA.BlogViewAudio = Class.create({
    
    fp: null,// Flowplayer
    indexStart: 0,// индекс испльзуется чтобы определить первое проигрывание файла
    indexPause: 0,// индекс испльзуется чтобы определить первое проигрывание файла
    accordion: null,// Аккордион
    isDownloaded: false, // Признак загрузки изображений
    actual: false, // Признак состояния аккордиона (открыт или закрыт)
    
    initialize: function(params) {
			
        this.options = {
            Url: 'data.json',
            Autoplay: false,
            Random: false
        } 
        
        if(params.options){
            params.options.Url = lb.getMsg('urlBase') + params.options.Url;
        }
        
        Object.extend(this.options, params.options || {});
		
        this.container = $(params.container) ? $(params.container) :  $$('body')[0];
        
        // Установим ссылку на загрузку FlashPlayer с моего сайта как альтернативу
        BSA.Sys.downloadFlashPlayer($('post-audio'));
        
        // Назначим обработчик события кнопки "player-close"
        var divCloseVideo = $('post-audio').down('div.player-close');
        var aCloseVideo = divCloseVideo.down('a');
        aCloseVideo.observe('click', this.onCloseAudio.bindAsEventListener(this));
        
        // Определим наличие аккордиона
        if (params.accordion){
            this.accordion = params.accordion;
            // Подпишемся на события в аккордионе
            this._subscribeAccordionEvents();
        }else{
            // Получим данные о аудио файлах
            this.getData();	
        }
    },
	
    getData:function(){
	
        new Ajax.Request(this.options.Url,{
            method: 'GET',
            onSuccess: function(transport){
                var data = BSA.Sys.getJsonResponse(transport, true);
                if (this.options.Random) {
                    data = data.sortBy(Math.random)
                }
                this.buildFlowPlayer(data);
            }.bind(this)
        });
		
    },
    
    buildFlowPlayer: function(tracks){
        var self = this;
        var playlist = [];
        //-------------------
        // Получим список музыки
        tracks.each(function(track){
            playlist.push({
                url: track.file, 
                duration: 0
            })
        });
        
        // Создадим обьект плеера
        this.fp = flowplayer(self.container, lb.getMsg('urlRes') + '/js/flowplayer/flowplayer-3.2.15.swf', {
            screen: {
                top:300
            },
            // fullscreen button not needed here
            plugins: {
                controls: {
                    backgroundColor:'#002200',
                    fullscreen: false,
                    width:425,
                    top:0,
                    height:35,
                    sliderColor:'#ff1207',
                    sliderGradient:'high',
                    volumeSliderColor:'#ff1207',
                    autoHide: false,
                    playlist: true
                },
                audio: {
                    url: lb.getMsg('urlRes') + '/js/flowplayer/flowplayer.audio-3.2.9.swf'
                }
            },

            clip: {
                provider: "audio",
                autoPlay: self.options.Autoplay,
                // optional: when playback starts close the first audio playback
                onBeforeBegin: function() {
//                    $f(self.container).close();
                }
            },
            onLoad: function() {
            },
            // our playlist
            playlist: playlist
        });
        this.addObservers();
    },
		
    	
    addObservers: function(){
        var self = this;
        //---------------------------------
        
        // Подпишемся на события плеера
        self.fp.getCommonClip().onStart(function(clip){
            
            if(! self.options.Autoplay){
                self.setStateItem('start', clip.index);
                return;
            }
            
            if(self.indexStart){// Действительно только для первого пройгрыша файла
                self.setStateItem('start', clip.index);
            }else{
                self.fp.pause();
                self.indexStart ++;
            }
        });
        
        self.fp.getCommonClip().onPause(function(clip){
            
            if(! self.options.Autoplay){
                self.setStateItem('pause', clip.index);
                return;
            }
            
            if(self.indexPause){
                self.setStateItem('pause', clip.index);
            }else{
                self.indexPause ++;
            }
        });
        
        self.fp.getCommonClip().onStop(function(clip){
            self.setStateItem('stop', clip.index);
        });
        
        self.fp.getCommonClip().onFinish(function(clip){
            self.setStateItem('finish', clip.index);
        });
        
        self.fp.getCommonClip().onResume(function(clip){
            self.setStateItem('resume', clip.index);
        });
        

        // Установим события click для списка аудио файлов
        var urlList = $('audio_list');
        urlList.select('a').each(function(track){
            track.observe('click', function(e) {
                var isRepeat = false;
                //-------------------
                Event.stop(e);
                
                var li = Event.element(e).up('li');
                var idClip = li.readAttribute('id').split('_')[1] * 1;
                // Играем 
                isRepeat = this.fp.getClip().index == idClip;
                this.fp.play(idClip);
                
                if(isRepeat){
                    this.setStateItem('start', idClip);
                }
            }.bindAsEventListener(self));	
        });
    },
    
    setStateItem: function(state, index) {
        var indexItem = null;
        //------------------------
        // Получим список всех файлов
        var urlList = $('audio_list');
        
        // Установим соответствующее состояние для каждого аудио файла в списке
        urlList.select('div').each(function(item){
            indexItem = item.up('li').readAttribute('id').split('_')[1] * 1;
            
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
                        item.className = 'audio-item';
                        break;
                }
 
            }else{
                item.className = 'audio-item';
            }
            
        });
    },
    
    onCloseAudio:function(event){
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
                    section = box.getThisSection(box.accordion, indexSection);
                    if(section){
                        box.accordion.hideSection(section);
                    }
                    window.clearTimeout(idTimeout);
                }, 300);
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
            $f(self.container).stop();
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
                
                // Получим данные
                self.getData();
            }
        }
    }
    
});

// Ф-ия, выполняемая при загрузки окна броузера
// создаются обьекты класса, экземпляры их
// заносяться в список экземпляров
// пр. $H(BlogAudioManager: [new BlogAudioManager(param1), ... ,new BlogAudioManager(paramN)])
BSA.BlogViewAudio.RegRunOnLoad = function() {
    // Получим параметры для создания обьекта
    var params = scriptParams.get('BlogViewAudio');
    // Ф-ия создания обьектов по их параметрам
    var createObject = function (param) {
        var viewAudio = scriptInstances.get('BlogViewAudio');
        if (viewAudio) {
            viewAudio.push(new BSA.BlogViewAudio(param));
        } else {
            scriptInstances.set('BlogViewAudio', [new BSA.BlogViewAudio(param)]);
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
runOnLoad(BSA.BlogViewAudio.RegRunOnLoad);