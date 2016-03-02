/**
 * Class - AccordionBox
 * event management component - Accordion
 *
 * JavaScript
 *
 * @author   Sergii Beskorovainyi <bsa2657@yandex.ru>
 * @license  MIT <http://www.opensource.org/licenses/mit-license.php>
 * @link     https://github.com/bsa-git/zf-myblog/
 */
BSA.AccordionBox = Class.create({
    
    id: '', // Container for accordion
    accordion: null, // The object of the accordion
    options: null, // Options for creating accordion
    states: null,  // Accordion states (which sections are opened or deactivated)
    // List of events
    events: {
        initializedAccordion: 'initialized',
        clickedAccordion: 'clicked',
        disabledAccordion: 'disabled',
        enabledAccordion: 'enabled',
        hiddenSection: 'hidden',
        shownSection: 'shown',
        disabledSection: 'disabled',
        enabledSection: 'enabled'
    },
    
    // Subscribed events
    onInitializedAccordionEvent: [],
    onClickedAccordionEvent: [],
    onDisabledAccordionEvent: [],
    onEnabledAccordionEvent: [],
    onHiddenSectionEvent: [],
    onShownSectionEvent: [],
    onDisabledSectionEvent: [],
    onEnabledSectionEvent: [],
    
    // Object initialization
    initialize : function(params)
    {
        
        // Установим id
        if (params && $(params.id)){
            this.id = params.id;
        }else{
            return;
        }
        
        // Состояние аккордиона
        if(params.states){
            this.states = params.states;
        }
        
        // Создадим пользовательские события
        this.createCustomEvents();
        
        // Создадим обьект аккордион
        if (params.options){
            this.options = params.options;
            this.accordion = new Accordion(this.id, params.options);
        }else{
            this.accordion = new Accordion(this.id);
        }
        
        // Удалим фиксированные размеры по высоте контейнера -> div.toggle 
        this.accordion.elements.toggles.each(function(toggle){
            toggle.setStyle({
                height: ''
            });
            
        })
        // Сделаем видимыми контейнеры -> div.accordion-toggle-container
        $$("div.accordion-toggle-container").invoke('show');
        
        // Сделаем видимыми контейнеры -> div#accordion-container
        // это используется для того что бы правильно отображалась страница
        // когда отключен javascript
        if($('accordion-container')){
            if($('accordion-container').select("li.section").size()){
                $('accordion-container').show();
            }
            
        }
    },
    
    // Creating a custom event for accordion
    createCustomEvents: function(){
        var self = this;
        var idTimeout;
        var section;
        //------------------
        // onInitializedAccordionEvent
        document.observe(self.id + ":" + self.events.initializedAccordion, function(event) {
            // Обработаем пользовательское событие и установим состояния секций аккордиона 
            // с задержкой в 300 мсек чтобы за это время успели загеристрироваться все клиенты 
            idTimeout = window.setTimeout(function() {
                // Обработаем пользовательское событие инициализации аккордиона
                if(self.onInitializedAccordionEvent.length > 0){
                    self.onInitializedAccordionEvent.each(function(objEvent){
                        //                        objEvent.handlerEvent(objEvent.client, event.memo);
                        if(objEvent.client.accordion.id && event.memo.accordion.id){
                            if(objEvent.client.accordion.id == event.memo.accordion.id){
                                objEvent.handlerEvent(objEvent.client, event.memo);
                            }
                        }else{
                            objEvent.handlerEvent(objEvent.client, event.memo);
                        }
                    })
                }
                
                // установим состояния секций аккордиона 
                self.setStateAccordion(self.states, event.memo.accordion);
                window.clearTimeout(idTimeout);
            }, 300);
            
            
        });
        
        // onClickedAccordionEvent
        document.observe(self.id + ":" + self.events.clickedAccordion, function(event) {
            if(self.onClickedAccordionEvent.length > 0){
                self.onClickedAccordionEvent.each(function(objEvent){
                    //                    objEvent.handlerEvent(objEvent.client, event.memo);
                    if(objEvent.client.accordion.id && event.memo.accordion.id){
                        if(objEvent.client.accordion.id == event.memo.accordion.id){
                            objEvent.handlerEvent(objEvent.client, event.memo);
                        }
                    }else{
                        objEvent.handlerEvent(objEvent.client, event.memo);
                    }
                })
            }
        });
        
        // onDisabledAccordionEvent
        document.observe(self.id + ":" + self.events.disabledAccordion, function(event) {
            if(self.onDisabledAccordionEvent.length > 0){
                self.onDisabledAccordionEvent.each(function(objEvent){
                    //                    objEvent.handlerEvent(objEvent.client, event.memo);
                    if(objEvent.client.accordion.id && event.memo.accordion.id){
                        if(objEvent.client.accordion.id == event.memo.accordion.id){
                            objEvent.handlerEvent(objEvent.client, event.memo);
                        }
                    }else{
                        objEvent.handlerEvent(objEvent.client, event.memo);
                    }
                })
            }
        });
        
        // onEnabledAccordionEvent
        document.observe(self.id + ":" + self.events.enabledAccordion, function(event) {
            if(self.onEnabledAccordionEvent.length > 0){
                self.onEnabledAccordionEvent.each(function(objEvent){
                    //                    objEvent.handlerEvent(objEvent.client, event.memo);
                    if(objEvent.client.accordion.id && event.memo.accordion.id){
                        if(objEvent.client.accordion.id == event.memo.accordion.id){
                            objEvent.handlerEvent(objEvent.client, event.memo);
                        }
                    }else{
                        objEvent.handlerEvent(objEvent.client, event.memo);
                    }
                })
            }
        });
        
        // onHiddenSectionEvent
        document.observe(self.id + "Section:" + self.events.hiddenSection, function(event) {
            
            if(self.onHiddenSectionEvent.length > 0){
                self.onHiddenSectionEvent.each(function(objEvent){
                    //                    objEvent.handlerEvent(objEvent.client, event.memo);
                    if(objEvent.client.accordion.id && event.memo.accordion.id){
                        if(objEvent.client.accordion.id == event.memo.accordion.id){
                            objEvent.handlerEvent(objEvent.client, event.memo);
                        }
                    }else{
                        objEvent.handlerEvent(objEvent.client, event.memo);
                    }
                })
            }
        });
        
        // onShownSectionEvent
        document.observe(self.id + "Section:" + self.events.shownSection, function(event) {
            
            if(self.onShownSectionEvent.length > 0){
                self.onShownSectionEvent.each(function(objEvent){
                    if(objEvent.client.accordion.id && event.memo.accordion.id){
                        if(objEvent.client.accordion.id == event.memo.accordion.id){
                            objEvent.handlerEvent(objEvent.client, event.memo);
                        }
                    }else{
                        objEvent.handlerEvent(objEvent.client, event.memo);
                    }
                })
            }
        });
        
        // onDisabledSectionEvent
        document.observe(self.id + "Section:" + self.events.disabledSection, function(event) {
            section = event.memo.section.elements.section;
            section.writeAttribute('title', lb.getMsg('msgAccessDenied'));
            
            if(self.onDisabledSectionEvent.length > 0){
                self.onDisabledSectionEvent.each(function(objEvent){
                    //                    objEvent.handlerEvent(objEvent.client, event.memo);
                    if(objEvent.client.accordion.id && event.memo.accordion.id){
                        if(objEvent.client.accordion.id == event.memo.accordion.id){
                            objEvent.handlerEvent(objEvent.client, event.memo);
                        }
                    }else{
                        objEvent.handlerEvent(objEvent.client, event.memo);
                    }
                })
            }
        });
        
        // onEnabledSectionEvent
        document.observe(self.id + "Section:" + self.events.enabledSection, function(event) {
            section = event.memo.section.elements.section;
            section.writeAttribute('title', '');
            
            if(self.onEnabledSectionEvent.length > 0){
                self.onEnabledSectionEvent.each(function(objEvent){
                    //                    objEvent.handlerEvent(objEvent.client, event.memo);
                    if(objEvent.client.accordion.id && event.memo.accordion.id){
                        if(objEvent.client.accordion.id == event.memo.accordion.id){
                            objEvent.handlerEvent(objEvent.client, event.memo);
                        }
                    }else{
                        objEvent.handlerEvent(objEvent.client, event.memo);
                    }
                })
            }
        });
    },
    
    //Set state of the sections for accordion
    setStateAccordion: function(states, accordion){
        var section;
        var self = this;
        //--------------------
        if(states && states.disable.size()){
            states.disable.each(function(index){
                section = self.getThisSection(accordion, index);
                if(section){
                    section.disable();
                }
            })
        }
            
        if(states && states.show.size()){
            states.show.each(function(index){
                section = self.getThisSection(accordion, index);
                if(section){
                    accordion.showSection(section);
                }
            })
        }
    },
    
    // Get accordion section of the index
    getThisSection: function(accordion, index){
        var result = null;
        accordion.sections.each(function(section){
            var index_ = section.elements.title.readAttribute('href');
            if(index_ == index){
                result = section;
            }
        })
        return result;
    }

})

// The function is executed after the download of the browser window
// are created objects, which are entered in the list of instances
// ex. $H(AccordionBox: [new AccordionBox(), ... ,new AccordionBox()])
BSA.AccordionBox.RegRunOnLoad = function() {
    // Получим параметры для создания обьекта
    var params = scriptParams.get('AccordionBox');
    // Ф-ия создания обьектов по их параметрам
    var createObject = function (param) {
        var accordion = scriptInstances.get('AccordionBox');
        if (accordion) {
            accordion.push(new BSA.AccordionBox(param));
        } else {
            scriptInstances.set('AccordionBox', [new BSA.AccordionBox(param)]);
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
}
runOnLoad(BSA.AccordionBox.RegRunOnLoad);