/**
 * LangBox - Class
 *
 * Класс для локализации сообщений
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
BSA.LangBox = Class.create({
    // Инициализация обьекта
    initialize : function()
    {
        var id;
        var msg;
        var pattern;
        var matchResult;
        //------------
        //Сообщения
        //this.urlBase = $('base_url').innerHTML;//Базовый путь к ресурсам
        //this.msgErrorRetrieveDataFromUrl = $('msg-error-retrieve-data-from-url').innerHTML;

        var boxs = Prototype.Selector.select('div.msg-box');
        if(boxs){
            boxs.each(function(box) {
                box.select('p').each(function(p) {
                    id = p.readAttribute('id').replace(/-/g,'_');
                    msg = p.innerHTML;
                    // Заменим символы для использования шаблонов - Template
                    // пр. 'Сообщение #[source] принято!'
                    pattern = /#\[[\w]+\]/;
                    matchResult = msg.match(pattern);
                    if(matchResult !== null){
                        msg = msg.replace(/[/[\[]/g, '{');
                        msg = msg.replace(/[/[\]]/g, '}');
                    }

                    this[id] = msg.escapeHTML();
                }.bind(this));
            }.bind(this));
        }
    },

    getMsg: function(messageId, options) {
        options = options || {};
        var result = '';
        //-----------------------
        try {
            if (this[messageId]) {
                var template = new Template(this[messageId]);
                return template.evaluate(options);
            }
        } catch(e) {
            result = messageId;
        }
        return result;
    }
})

// Ф-ия, выполняемая при загрузки окна броузера
// создаются обьекты класса, экземпляры их
// заносяться в список экземпляров
// пр. $H(LangBox: [new LangBox(), ... ,new LangBox()])
BSA.LangBox.RegRunOnLoad = function() {

    lb = new BSA.LangBox();
    if(scriptInstances.get('LangBox')){
        scriptInstances.get('LangBox').push(lb);
    }else{
        scriptInstances.set('LangBox',[lb]);
    }
}
runOnLoad(BSA.LangBox.RegRunOnLoad);