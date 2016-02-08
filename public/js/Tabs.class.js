/**
 * BSA.Tabs - Обьект управления закладками
 *
 * С помощью этого обьекта вы можете:
 *  - организовать работу закладок
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

BSA.Tabs = Class.create({
    
    tabs: null,             // Контейнер для закладок
    tabs_id: "",            // ID контейнера для закладок
    numelems: 0,            // Количество закладок
    animate: true,          // Признак анимации
    // Параметры для отдельных закладок: 
    // animate - "анимация"; opennum - "совместное открытие закладок"
    activate: {},         
    
    /**
     * Инициализация обьекта
     * 
     * @param object    params
     *        string    params.id -> контейнер для закладок
     *        bool      params.animate -> отключить/включить анимацию для всех закладок
     *        object    params.activate -> параметры для отдельных закладок
     *        bool      params.activate.animate -> анимация для отдельной закладки
     *        int       params.activate.opennums -> номеро закладки для одновременного открытия
     * 
     */
    initialize : function(params)
    {
        // Определим контейнер
        if(params && $(params.id)){
            this.tabs_id = params.id;
            this.tabs = $(params.id);
            if(params.animate === false){
                this.animate = params.animate;
            }
            if(params.activate){
                this.activate = params.activate;
            }
        }else{
            return;
        }
        
        // Получим количество закладок
        this.numelems = this.tabs.down("div.tabsHeader").select("li").size();
        
        // Получим количество контейнеров с содержимым для закладок
        var tabsContent = this.tabs.down("div.tabsContent").select("div.tabContent")
        
        // Проставим ID для закладок и установим события "click"
        if(this.numelems && tabsContent.size()){
            this.tabs.down("div.tabsHeader").select("a").each(function(tab, index) {
                var params_animate, animate;
                var opennums = null;
                //-------------------
                // Определим параметры
                if(this.activate[index+1]){
                    params_animate = (typeof this.activate[index+1].animate !== 'undefined');
                    if(params_animate){
                        params_animate = this.activate[index+1].animate;
                    }else{
                        params_animate = this.animate? true:false;
                    }
                    if(this.activate[index+1].opennums){
                        opennums = this.activate[index+1].opennums;
                    }
                    
                }else{
                    params_animate = this.animate? true:false;
                }
                
                
                animate = this.animate? this.animate && params_animate: this.animate || params_animate;
                
                tab.observe('click', this.toggleTab.bindAsEventListener(this, index+1, this.numelems, opennums ,animate));
                tab.id = this.tabs_id + "_" + "tabHeader" + (index+1);
                if(tabsContent[index]){
                    tabsContent[index].id = this.tabs_id + "_" +"tabContent" + (index+1);
                }
            }.bind(this));
        }
        
    },
    
    /*-----------------------------------------------------------
    Toggles element's display value
    Input: any number of element id's
    Output: none 
    ---------------------------------------------------------*/
    toggleDisp: function() {
        for (var i=0;i<arguments.length;i++){
            var d = $(arguments[i]);
            if (d.style.display == 'none')
                d.style.display = 'block';
            else
                d.style.display = 'none';
        }
    },
    /*-----------------------------------------------------------
    Toggles tabs - Closes any open tabs, and then opens current tab
    Input:      1.The number of the current tab
                2.The number of tabs
                3.(optional)The number of the tab to leave open
                4.(optional)Pass in true or false whether or not to animate the open/close of the tabs
    Output: none 
    ---------------------------------------------------------*/
    toggleTab: function(event, num,numelems,opennum,animate) {
        var tempc, temph;
        var h,c = null;
        //---------------------
        Event.stop(event);
        
        tempc = this.tabs_id + "_" + 'tabContent'+num;
        if ($(tempc).style.display == 'none'){
            for (var i=1;i<=numelems;i++){
                if ((opennum == null) || (opennum != i)){
                    temph = this.tabs_id + "_" + 'tabHeader'+i;
                    h = $(temph);
                    if(h.hasClassName('tabActive')){
                        h.removeClassName('tabActive');
                    }
                    tempc = this.tabs_id + "_" + 'tabContent'+i;
                    c = $(tempc);
                    if(c.style.display != 'none'){
                        if (animate || typeof animate == 'undefined')
                            Effect.toggle(tempc,'blind',{
                                duration:0.5, 
                                queue:{
                                    scope:'menus', 
                                    limit: 3
                                }
                            });
                        else
                            this.toggleDisp(tempc);
                    }
                }
            }
            temph = this.tabs_id + "_" + 'tabHeader'+ num;
            h = $(temph);
            if (h){
                h.addClassName('tabActive');
            }
                
            h.blur();
            tempc = this.tabs_id + "_" + 'tabContent'+num;
//            c = $(tempc);
//            c.style.marginTop = '2px';
            if (animate || typeof animate == 'undefined'){
                Effect.toggle(tempc,'blind',{
                    duration:0.5, 
                    queue:{
                        scope:'menus', 
                        position:'end', 
                        limit: 3
                    }
                });
            }else{
                this.toggleDisp(tempc);
            }
        }
    }
})

// Ф-ия, выполняемая при загрузки окна броузера
// создаются обьекты класса, экземпляры их
// заносяться в список экземпляров
// пр. $H(Tabs: [new Tabs(param1), ... ,new Tabs(paramN)])
BSA.Tabs.RegRunOnLoad = function() {
    // Получим параметры для создания обьекта
    var params = scriptParams.get('Tabs');
    // Ф-ия создания обьектов по их параметрам
    var createObject = function (param) {
        var tabs = scriptInstances.get('Tabs');
        if (tabs) {
            tabs.push(new BSA.Tabs(param));
        } else {
            scriptInstances.set('Tabs', [new BSA.Tabs(param)]);
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
runOnLoad(BSA.Tabs.RegRunOnLoad);