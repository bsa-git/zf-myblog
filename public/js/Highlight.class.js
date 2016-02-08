/**
 * Highlight - Class
 * syntax highlighting for the Web
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
BSA.Highlight = Class.create({
    // Инициализация обьекта
    initialize: function (params)
    {
        try {

            if(Prototype.Browser.IE && Prototype.Browser.IEVersion < 9){
                return;
            }
            
            // Set new configure
            this.params = params || {};
            hljs.configure(this.params);

            // Change class name
            // class = "language-yaml" => class = "yaml"
            var pre_codes = Prototype.Selector.select('pre code');
            if (pre_codes) {
                if (pre_codes.size()) {
                    pre_codes.each(function (block) {
                        var strClass = block.readAttribute('class');
                        if (strClass) {
                            strClass = strClass.replace("language-", "");
                            block.writeAttribute('class', strClass);
                        }
                        hljs.highlightBlock(block);
                    });
                } else {
                    var pre = Prototype.Selector.select('pre');
                    if (pre) {
                        pre.each(function (block) {
                            hljs.highlightBlock(block);
                        });
                    }
                }
            }

        } catch (ex) {
            if (ex instanceof Error) {
                BSA.Sys.onFailure(ex.name + ": " + ex.message);
            }

        }
    }
});

// Ф-ия, выполняемая при загрузки окна браузера
// создаются обьекты класса, экземпляры их
// заносяться в список экземпляров
// пр. $H(Highlight: [new Highlight(), ... ,new Highlight()])
BSA.Highlight.RegRunOnLoad = function () {
    // Получим параметры для создания обьекта
    var params = scriptParams.get('Highlight');
    // Ф-ия создания обьектов по их параметрам
    var createObject = function (param) {
        var highlight = scriptInstances.get('Highlight');
        if (highlight) {
            highlight.push(new BSA.Highlight(param));
        } else {
            scriptInstances.set('Highlight', [new BSA.Highlight(param)]);
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
runOnLoad(BSA.Highlight.RegRunOnLoad);
