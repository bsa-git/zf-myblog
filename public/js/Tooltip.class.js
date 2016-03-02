/**
 * Class - Tooltip
 *
 * With these class you can:
 *  - display tooltips
 *
 * JavaScript
 *
 * @author   Sergii Beskorovainyi <bsa2657@yandex.ru>
 * @license  MIT <http://www.opensource.org/licenses/mit-license.php>
 * @link     https://github.com/bsa-git/zf-myblog/
 */

BSA.Tooltip = Class.create({
    
    widthTip: null,  // The width of the window for tooltip
    floatTip: null,  // Element of the tooltip container
    cursorX: 0,      // The cursor position Х   
    cursorY: 0,      // The cursor position Y
    isIFrame: false, // Sign opening the URL in IFrame
    
    // Object initialization
    initialize : function(width)
    {
        if($("floatTip")){
            this.floatTip = $("floatTip");
        }else{
            return;
        }
        
        if(width){
            this.widthTip = width;
        }
        
        // Sign opening the URL in IFrame
        var urlGet = BSA.Sys.jGet(window.location.href);
        if(urlGet.ajax){
            this.isIFrame = urlGet.ajax == "post";
        }
        
        
    },
    // The position of the window tooltip
    Move: function(e) {
        var x,y;
        
        var display = this.floatTip.getStyle('display');
        if(display == "none"){
            return;
        }
        var layout = this.floatTip.getDimensions();
        var tipHeight = layout.height;
        var tipWidth = layout.width;
        
        var winDimensions = document.viewport.getDimensions();
        
        // Для браузера IE6-8
        if (Prototype.Browser.IE)  { 
            x = window.event.clientX + document.documentElement.scrollLeft
            y = window.event.clientY + document.documentElement.scrollTop

        // Для остальных браузеров
        } else   { 
            x = e.pageX; // Координата X курсора
            y = e.pageY; // Координата Y курсора
        }
        
        this.cursorX = x;
        this.cursorY = y;
        
        // Показывать слой справа от курсора
        if ((x + tipWidth + 10) < winDimensions.width) { 
            if(this.isIFrame){
                this.floatTip.setStyle({
                    left:   x + 'px'
                });
            }else{
                this.floatTip.setStyle({
                    left:   x - 300 + 'px'
                });
            }
            

        // Показывать слой слева от курсора
        } else {
            if(this.isIFrame){
                this.floatTip.setStyle({
                    left:   x - tipWidth + 'px'
                });
            }else{
                this.floatTip.setStyle({
                    left:   x - 300 - tipWidth + 'px'
                });
            }
            
        }
        
        // Положение от  верхнего края окна браузера
        if(tipHeight > y ){
            if(this.isIFrame){
                this.floatTip.setStyle({
                    top:   y + 30 + 'px'
                });
            }else{
                this.floatTip.setStyle({
                    top:   y - 60 + 'px'
                });
            }
            
        }else{
            if(this.isIFrame){
                this.floatTip.setStyle({
                    top:   y - 30 - tipHeight + 'px'
                });
            }else{
                this.floatTip.setStyle({
                    top:   y - 120 - tipHeight + 'px'
                });
            }
            
        }
    },
    
    // View tooltip
    View: function(event, str, element) {
        var tipWidth, elSize, strSize;
        var isView = !str.empty() && !str.blank();
        var display = this.floatTip.getStyle("display");
        //-------------------
        // Выйдем из ф-ии, если подсказка видима
        if(display == "block"){
            return;
        }
        
        // Определимся с шириной окна
        if(this.widthTip){// Строго заданная ширина, устанавливается при создании обьекта
            tipWidth = this.widthTip;
        }else{// Ширина определяется шириной эл. где находиться информация 
            // Определим нужно ли выводить подсказку
            // подсказка выводиться если текст не входит в заданные размеры
            tipWidth = element.getStyle('width');
            elSize = element.getDimensions();
            strSize = this.stringSize(str);
            isView = isView && (strSize.width > elSize.width || strSize.height > elSize.height);
        }
        if(isView){
            // Выводим текст подсказки
            this.floatTip.innerHTML = str;
            // Показываем подсказку и устанавливаем ширину окна подсказки
            this.floatTip.setStyle({
                display:   "block",
                width: tipWidth
            });
        }
    },
    
    // Hide tooltip
    Hide: function(event, element) {
        var isHide = true;
        //-------------------
        
        // Для браузера IE6-8
        // Из за глюка пришлось опрделить условие что бы не скрывать подсказку
        // т.к. IE выполняет лишнее событие "mouseout"
        // когда мы находиться на самом элементе и не выходим за его пределы
        if (element && Prototype.Browser.IE)  { 
            var layout = element.getLayout();
            this.cursorX;
            this.cursorY;
        
            var notHide = this.cursorX > layout.get("left") && 
            this.cursorX < (layout.get("left") + layout.get("width")) &&
            this.cursorY > layout.get("top") &&
            this.cursorY < (layout.get("top") + layout.get("height"));
            isHide = !notHide;
        }
        
        
        if(isHide){
            // Прячем подсказку
            this.floatTip.setStyle({
                display:   "none",
                width: ""
            });
        }
    },
    
    stringSize: function(str)
    {
        // Определим размер строки
        this.floatTip.innerHTML = str;
        var strSize = this.floatTip.getDimensions();
        strSize.height = strSize.height - 20;
        strSize.width = strSize.width - 20;
        return strSize;
    }
})


