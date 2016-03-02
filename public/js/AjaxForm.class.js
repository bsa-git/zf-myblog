/**
 * Class - AjaxForm
 * Work with the form via Ajax
 *
 * JavaScript
 *
 * @author   Sergii Beskorovainyi <bsa2657@yandex.ru>
 * @license  MIT <http://www.opensource.org/licenses/mit-license.php>
 * @link     https://github.com/bsa-git/zf-myblog/
 */
BSA.AjaxForm = Class.create({

    form   : null,
    
    // Object initialization
    initialize : function(form)
    {
        // Установим контейнер
        if ($(form)){
            this.form = $(form);
            this.container = form;
        }else{
            return;
        }

        this.form.observe('submit', this.onSubmit.bindAsEventListener(this));

        BSA.Sys.form_errors_clear(this.form);
    },


    onSubmit : function(e)
    {
        Event.stop(e);

        var options = {
            parameters : this.form.serialize(),
            method     : this.form.method,
            onSuccess  : this.onFormSuccess.bind(this)
        };

        new Ajax.Request(this.form.action, options);
    },

    onFormSuccess : function(transport)
    {
        var json = BSA.Sys.getJsonResponse(transport, true);
        var errors = json.errors;
        var messages = json.messages;
        var errMessages = null;
        var result = false;
        //----------------------
        //Удалим все ошибки и сообщения
        BSA.Sys.form_errors_clear(this.form);
        BSA.Sys.messagebox_clear();

        // Получим результат выполнения формы
        result = (messages == null) && (errors == null);

        // Если данные в форму введены без ошибок
        // то закончим выполнение формы
        if(result){ // ОК
            this.form.submit();
        }else{ // ERRORS

            errors = $H(errors);
            
            // Выведем ошибки, если они есть
            // иначе отработаем ф-ию form.submit()
            if (errors.size() > 0) {
                errors.each(function(pair) {
                    errMessages = $H(pair.value);
                    BSA.Sys.form_errors_write(this.form, pair.key, errMessages)
                }.bind(this));
            }
        }
    }
});

// The function is executed after the download of the browser window
// are created objects, which are entered in the list of instances
// ex. $H(AjaxForm: [new AjaxForm(param1), ... ,new AjaxForm(paramN)])
BSA.AjaxForm.RegRunOnLoad = function() {
    // Получим параметры для создания обьекта
    var params = scriptParams.get('AjaxForm');
    // Ф-ия создания обьектов по их параметрам
    var createObject = function (param) {
        var ajaxForm = scriptInstances.get('AjaxForm');
        if (ajaxForm) {
            ajaxForm.push(new BSA.AjaxForm(param));
        } else {
            scriptInstances.set('AjaxForm', [new BSA.AjaxForm(param)]);
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
runOnLoad(BSA.AjaxForm.RegRunOnLoad);