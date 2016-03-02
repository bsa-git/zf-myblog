/**
 * Class - LangBox
 * posts localization
 *
 * JavaScript
 *
 * @author   Sergii Beskorovainyi <bsa2657@yandex.ru>
 * @license  MIT <http://www.opensource.org/licenses/mit-license.php>
 * @link     https://github.com/bsa-git/zf-myblog/
 */
BSA.LangBox = Class.create({
    // Object initialization
    initialize : function()
    {
        var id;
        var msg;
        var pattern;
        var matchResult;
        //------------
        // Messages
        var boxs = Prototype.Selector.select('div.msg-box');
        if(boxs){
            boxs.each(function(box) {
                box.select('p').each(function(p) {
                    id = p.readAttribute('id').replace(/-/g,'_');
                    msg = p.innerHTML;
                    // Replace symbols to use templates - Template (ex. 'Post #[source] accepted!')
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

// The function is executed after the download of the browser window
// are created objects, which are entered in the list of instances
// ex. $H(LangBox: [new LangBox(), ... ,new LangBox()])
BSA.LangBox.RegRunOnLoad = function() {

    lb = new BSA.LangBox();
    if(scriptInstances.get('LangBox')){
        scriptInstances.get('LangBox').push(lb);
    }else{
        scriptInstances.set('LangBox',[lb]);
    }
}
runOnLoad(BSA.LangBox.RegRunOnLoad);