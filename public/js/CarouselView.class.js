/**
 * Class - CarouselView
 *
 * With these class you can:
 *  - provide scrolling images microformat
 *  - view the image with LightBox
 *
 * JavaScript
 *
 * @author   Sergii Beskorovainyi <bsa2657@yandex.ru>
 * @license  MIT <http://www.opensource.org/licenses/mit-license.php>
 * @link     https://github.com/bsa-git/zf-myblog/
 */
BSA.CarouselView = Class.create({
    
    // Initialization parameters
    iniParams: null,
    
    // Element size
    elementSize: 150,
    
    // Object of carousel
    carousel: null,
    
    // Container of carousel
    containerCarousel: '',
    
    accordion: null,// Accordion object
    isDownloaded: false, // Is uploading images to the page
    actual: false, // Accordion state (open or closed)
    
    // Object initialization
    initialize : function(params)
    {
        
        // Set container of carousel
        if(params && $(params.carousel)){
            this.containerCarousel = params.carousel;
        }else{
            return;
        }
        
        // Is accordion
        if (params.accordion){
            this.accordion = params.accordion;
            this.iniParams = params;
            // Subscribe to the events in the accordion
            this._subscribeAccordionEvents();
        }else{
            // Initialization carousel
            this.iniCarousel(params);
        }
    },
    
    // Initialization carousel
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
    
    // Update carousel size
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
                        section = box.accordion.sections[indexSection];
                        box.accordion.showSection(section);
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
                
                // Инициализация карусели
                self.iniCarousel(self.iniParams);
                
            }
        }
    }
})

// The function is executed after the download of the browser window
// are created objects, which are entered in the list of instances
// ex. $H(CarouselView: [new CarouselView(param1), ... ,new CarouselView(paramN)])
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