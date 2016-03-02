/**
 * Class - Tabs
 *
 * With these class you can:
 *  - organize the work with bookmarks
 *
 * JavaScript
 *
* @author   Sergii Beskorovainyi <bsa2657@yandex.ru>
 * @license  MIT <http://www.opensource.org/licenses/mit-license.php>
 * @link     https://github.com/bsa-git/zf-myblog/
 */

BSA.Tabs = Class.create({
    
    tabs: null,             // Container for tabs
    tabs_id: "",            // Container ID for tabs 
    numelems: 0,            // Count tabs
    animate: true,          // Is animate

    activate: {},         
    
    /**
     * Object initialization
     * 
     * @param object    params
     *        string    params.id -> Container for tabs
     *        bool      params.animate -> enable/disable the animation for all tabs
     *        object    params.activate -> settings for individual tab
     *        bool      params.activate.animate -> animation for a separate tab
     *        int       params.activate.opennums -> tab number for simultaneous opening
     * 
     */
    initialize : function(params)
    {
        // Set container
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
        
        // Get the number of tabs
        this.numelems = this.tabs.down("div.tabsHeader").select("li").size();
        
        // We get the number of containers with the contents of tabs
        var tabsContent = this.tabs.down("div.tabsContent").select("div.tabContent")
        
        // Set ID for tabs and set the event "click"
        if(this.numelems && tabsContent.size()){
            this.tabs.down("div.tabsHeader").select("a").each(function(tab, index) {
                var params_animate, animate;
                var opennums = null;
                //-------------------
                // Params
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

// The function is executed after the download of the browser window
// are created objects, which are entered in the list of instances
// ex. $H(Tabs: [new Tabs(param1), ... ,new Tabs(paramN)])
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