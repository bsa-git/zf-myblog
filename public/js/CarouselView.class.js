/**
 * CarouselView - Class
 *
 * С помощью класса вы можете:
 *  - обеспечить прокрутку изображений в микро формате
 *  - просмотреть изображение с помощью LightBox
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
BSA.CarouselView = Class.create({
    
    // Параметры инициализации
    iniParams: null,
    
    // Размер элемента
    elementSize: 150,
    
    // Обьект карусель
    carousel: null,
    
    // Контейнер карусели
    containerCarousel: '',
    
    accordion: null,// Аккордион
    isDownloaded: false, // Признак загрузки изображений
    actual: false, // Признак состояния аккордиона (открыт или закрыт)
    
    // Конструктор класса
    initialize : function(params)
    {
        
        // Определим контейнер для карусели
        if(params && $(params.carousel)){
            this.containerCarousel = params.carousel;
        }else{
            return;
        }
        
        // Определим наличие аккордиона
        if (params.accordion){
            this.accordion = params.accordion;
            this.iniParams = params;
            // Подпишемся на события в аккордионе
            this._subscribeAccordionEvents();
        }else{
            // Инициализация карусели
            this.iniCarousel(params);
        }
    },
    
    // Инициализация карусели
    iniCarousel: function(params) {
        var self = this;
        //---------------------
        
        // Сделаем видимым элемент карусель
        var images = $(params.images);
        if(images){
            $(images).show();
            var legendValue = images.down('legend').innerHTML;
        }else{
            return;
        }
        
        // Размер
        this.updateCarouselSize();
        if(params.ajax){
            // Запомним размер элемента
            this.elementSize = params.ajax.elementSize;

            // Создадим обьект класса карусель
            this.carousel = new UI.Ajax.Carousel(params.carousel, {
                //url: lb.getMsg('urlBase') + params.ajax.url + '?post_id=' + params.ajax.post_id + '&username=' + params.ajax.username, 
                url: lb.getMsg('urlBase') + params.ajax.url, 
                elementSize: params.ajax.elementSize
            })
            // Событие - начало загрузки элементов
            .observe("request:started", function() {
                $('spinner-' + self.containerCarousel).show().morph("opacity:0.8", {
                    duration:0.5
                });
            })
            // Событие - конец загрузки элементов
            .observe("request:ended", function() {
                $('spinner-' + self.containerCarousel).morph("opacity:0", {
                    duration:0.5, 
                    afterFinish: function(obj) {
                        obj.element.hide();
                        //self.params.images_count
                        var uploadImages = images.select('li');
                        images.down('legend').update(legendValue + ' (' + uploadImages.length + ' ' + lb.getMsg('msgOf')+ ' ' + params.images_count + ' )');
                    }
                });
            });
        }else{
            // Создадим обьект карусель
            this.carousel = new UI.Carousel(params.carousel);
        }
        // Обновление размера элемента при изменении размера окна
        Event.observe(window, 'resize', function(event) {
            self.updateCarouselSize();  
            if (self.carousel)
                self.carousel.updateSize();
        });
    },
    
    // Обновление размера карусели
    updateCarouselSize: function() {
        //Определим обьект контейнера 
        var contentContainer = $('content-container');
        var layout = new Element.Layout(contentContainer);
        
        // Установим длину 
        var widthPostImages = layout.get('width') - 175;//75  
        $("post-images").style.width = widthPostImages + "px"; // 90 + '%';
        
        var widthCarousel = Math.floor(widthPostImages / this.elementSize) * this.elementSize;
        widthCarousel = widthCarousel + 20;
        
        // Выравнивание посредине
        var leftCarousel = Math.floor((widthPostImages - widthCarousel)/2);
        
        // Задание свойств
        $(this.containerCarousel).style.width = widthCarousel + "px";
        $(this.containerCarousel).style.left = leftCarousel + "px";
        $$("#" + this.containerCarousel + " .carousel-container").first().style.width =  (widthCarousel - 70) + "px";
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
                        section = box.accordion.sections[indexSection];
                        box.accordion.showSection(section);
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
                
                // Инициализация карусели
                self.iniCarousel(self.iniParams);
                
            }
        }
    }
})

// Ф-ия, выполняемая при загрузки окна броузера
// создаются обьекты класса, экземпляры их
// заносяться в список экземпляров
// пр. $H(CarouselView: [new CarouselView(param1), ... ,new CarouselView(paramN)])
BSA.CarouselView.RegRunOnLoad = function() {
    // Получим параметры для создания обьекта
    var params = scriptParams.get('CarouselView');
    // Ф-ия создания обьектов по их параметрам
    var createObject = function (param) {
        var carouselView = scriptInstances.get('CarouselView');
        if (carouselView) {
            carouselView.push(new BSA.CarouselView(param));
        } else {
            scriptInstances.set('CarouselView', [new BSA.CarouselView(param)]);
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
runOnLoad(BSA.CarouselView.RegRunOnLoad);