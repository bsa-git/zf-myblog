/**
 * Class - Highlight
 * syntax highlighting for the Web
 *
 * JavaScript
 *
* @author   Sergii Beskorovainyi <bsa2657@yandex.ru>
 * @license  MIT <http://www.opensource.org/licenses/mit-license.php>
 * @link     https://github.com/bsa-git/zf-myblog/
 */
BSA.Highlight = Class.create({
    // Object initialization
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

// The function is executed after the download of the browser window
// are created objects, which are entered in the list of instances
// ex. $H(Highlight: [new Highlight(), ... ,new Highlight()])
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
