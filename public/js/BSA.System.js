/**
 * BSA.Sys - object system functions
 *
 * This object allows:
 *  - display messages and errors
 *  - it provides the functionality of ProgressBar (indicator of the degree of the assignment)
 *  - assigns events to AJAX requests and display the results of the implementation of these requests
 *  - work with parts of a URL
 *
 * JavaScript
 *
 * @author   Sergii Beskorovainyi <bsa2657@yandex.ru>
 * @license  MIT <http://www.opensource.org/licenses/mit-license.php>
 * @link     https://github.com/bsa-git/zf-myblog/
 */

var BSA = {};

BSA.Sys = {
    // Global Settings
    settings: {
        ieVersion: 0,
        messages: 'messages',
        message_box: 'container-message',
        message_items: 'message-items',
        close_message: 'close-message-items',
        messages_hide_delay: 10,
        classesAjaxClickActions: [//List of DOM elemets classes for ajax click events
            'ajax-system-click'
        ],
        isLoadFlashPlayerFromMySite: false,
        list_file_resources: [
            // text
            '.txt', '.json', '.xml',
            // images
            '.png', '.jpe', '.jpeg', '.jpg', '.gif', '.bmp', '.ico', '.tiff', '.tif', '.svg', '.svgz',
            // archives
            '.zip', '.rar', '.exe', '.msi', '.cab',
            // audio/video
            '.mp3', '.qt', '.mov', '.mp4', '.m4v', '.f4v', '.flv', '.swf',
            // adobe
            '.pdf', '.psd', '.ai', '.eps', '.ps',
            // ms office
            '.doc', '.rtf', '.xls', '.ppt',
            // open office
            '.odt', '.ods'
        ],
        list_replace_values_for_json: ['\\', '"', '/']
    },
    // Init script function
    init: function ()
    {
        BSA.Sys.settings.platform = navigator.platform;

        // check if the messages element exists and is visible,
        // and if so, apply the highlight effect to it
        var messages = $(BSA.Sys.settings.messages);
        if (messages && messages.visible()) {
            new Effect.Highlight(messages);
        }

        // Назначим Ajax события 'click' для элементов
        BSA.Sys.settings.classesAjaxClickActions.each(function (cls) {
            $$('.' + cls).each(function (el) {
                el.observe('click', BSA.Sys.onClickAjaxAction.bind(BSA.Sys));
            })
        })

        // Назначим событие закрытия сообщения "messagebox" close_message
        if ($(BSA.Sys.settings.close_message)) {
            $(BSA.Sys.settings.close_message).observe('click', function (event) {
                Event.stop(event);
                BSA.Sys.messagebox_clear();
            });
        }

        // Установим признак отработки кеша страницы
        if ($("isDebugHeaderCache")) {
            $("header").up("body").insert({top: $("isDebugHeaderCache").innerHTML});
        }

        // Set IE ver
        if (Prototype.Browser.IE) {
            var engine = null;
            if (window.navigator.appName == "Microsoft Internet Explorer")
            {
                // This is an IE browser. What mode is the engine in?
                if (document.documentMode) // IE8
                    engine = document.documentMode;
                else // IE 5-7
                {
                    engine = 5; // Assume quirks mode unless proven otherwise
                    if (document.compatMode)
                    {
                        if (document.compatMode == "CSS1Compat")
                            engine = 7; // standards mode
                    }
                }
                // the engine variable now contains the document compatibility mode.
            }
            Prototype.Browser.IEVersion = engine;
            Prototype.Browser.IE6 = engine == 6;
            Prototype.Browser.IE7 = engine == 7;
            Prototype.Browser.IE8 = engine == 8;
            Prototype.Browser.IE9 = engine == 9;
            Prototype.Browser.IE10 = engine == 10;
        }

        // Set user logotype
        if ($('logo2') && $('userMainName') && (!Prototype.Browser.IE || Prototype.Browser.IEVersion > 7)) {
            var logotype = $('userMainName').innerHTML;
            $('logo2').innerHTML = logotype;
            if ($('userLogoUrl')) {
                var logo_url = $('userLogoUrl').innerHTML;
                $('logo2').writeAttribute('href', logo_url);
            }
        }
    },
    message_write: function (message)
    {
        var msg = '';
        if ($('msgMessage')) {
            msg = $('msgMessage').innerHTML;
        }
        var messages = $(this.settings.messages);
        if (!messages)
            return;

        if (message.length == 0) {
            messages.hide();
            return;
        }

        // Установим первую букву сообщение в LowCase
        var arrMessage = message.split(' ');
        arrMessage[0] = arrMessage[0].toLowerCase();
        var my_message = arrMessage.join(" ");

        my_message = '<em>' + msg + ': </em>' + my_message;
        messages.update(my_message);
        messages.show();
    },
    err_message_write: function (message)
    {
        var err = '';
        if ($('msgError')) {
            err = $('msgError').innerHTML;
        }
        var messages = $(this.settings.messages);
        if (!messages)
            return;

        if (message.length == 0) {
            messages.hide();
            return;
        }

        // Установим первую букву сообщение в LowCase
        var arrMessage = message.split(' ');
        arrMessage[0] = arrMessage[0].toLowerCase();
        var my_message = arrMessage.join(" ");


        my_message = '<span style="color: red;font-weight: bold;">' + err + ': </span>' + my_message;
        messages.update(my_message);
        messages.show();
    },
    message_clear: function ()
    {
        setTimeout("BSA.Sys.message_write('')", this.settings.messages_hide_delay * 1000);
        setTimeout("BSA.Sys.err_message_write('')", this.settings.messages_hide_delay * 1000);
    },
    // Отобразим сообщения
    messagebox_write: function (class_message, messages) {
        var htmlMessages = '';
        var typeMessages = '';
        var strMessage;
        //---------------------

        // Если отсутсвует контейнер, то выйти
        var message_box = $(this.settings.message_box);
        if (!message_box)
            return;

        // Скрыть сообщение
        if (class_message.length == 0) {
            messagebox_clear();
            return;
        }

        //Добавим сообщения
        messages.each(function (message) {
            if (message === null) {
                message = lb.getMsg('errMessageFromHostIsNULL');
            }
            strMessage = message.replace(/&lt;/g, "<");
            strMessage = strMessage.replace(/&gt;/g, ">");
            htmlMessages = htmlMessages + strMessage + '<br />';
        })
        htmlMessages = '<p>' + htmlMessages + '</p>';

        $('close-message-items').insert({
            after: htmlMessages
        });

        //Добавим тип сообщения - (Информация, Внимание, Сообщение...)
        // Получим ключ в виде пр. msgWarning
        var key = 'msg' + class_message.capitalize();
        typeMessages = lb.getMsg(key);
        htmlMessages = '<b class="' + class_message + '">' + typeMessages + '</b>';

        $('close-message-items').insert({
            after: htmlMessages
        });

        //Установим класс сообщения
        $(this.settings.message_items).addClassName(class_message);

        //Сделаем видимым сообщение
        $(this.settings.message_box).show();

        // Переведем фокус на сообщение
        $('header').scrollIntoView();

    },
    // Сlear message
    messagebox_clear: function () {
        var classNames = null;
        //---------------------
        var msgBox = $(this.settings.message_box);

        if (msgBox) {
            //Удалим все сообщения
            //Удалим классы
            classNames = $(this.settings.message_items).classNames();
            classNames.each(function (class_name) {
                $(this.settings.message_items).removeClassName(class_name);
            }.bind(this))

            var $elements = $(this.settings.message_items).down().siblings();
            $elements.each(function ($element) {
                $element.remove();
            })
            msgBox.hide();
        }
    },
    // Clear message with delay
    messagebox_delay_clear: function () {
        var msgBox = $(this.settings.message_box);
        var options = {
            duration: 0.8,
            afterFinish: function (effect) {
                BSA.Sys.messagebox_clear();
                effect.element.remove();
            }.bind(this)
        }

        new Effect.Fade(msgBox, options);
    },
    // Display errors in the form
    form_errors_write: function (form, element, errors) {
        var htmlErrors = '';
        var formElement = form[element];

        if (!formElement)
            return;


        //Добавим новый элемент ошибки с новыми значениями ошибок
        errors.each(function (pair) {
            htmlErrors = htmlErrors + '<li>' + pair.value + '</li>';
        })
        htmlErrors = '<ul class="errors">' + htmlErrors + '</ul>';
        new Insertion.After(element, htmlErrors);
    },
    // Clear error in the form
    form_errors_clear: function (form)
    {
        form.getElementsBySelector('.errors').invoke('remove');

    },
    /**
     * jGet
     *
     * With jGet you can pick variables in URL (GET method)
     *
     * JavaScript
     *
     * Copyright (c) 2010 Natanael Simoes
     *
     * @author     Natanael Simoes <natanael@fabricadecodigo.com.br>
     * @copyright  2010 Natanael Simoes
     * @license    BSD
     * @version    1.00.00
     * @link       http://www.fabricadecodigo.com.br/web
     * 
     * @param string url
     * 
     * @return object
     * 
     */
    jGet: function (url) {//window.location.href

        var GET = {};

        var value; // Array used for: [0] variable name [1] the value

        var index = url.indexOf('?');
        if (index == -1) {
            return GET;
        }

        var getStrings = url.slice(url.indexOf('?') + 1).split('&'); // Get all after ? and split at &

        for (var i = 0; i < getStrings.length; i++) // For each variable=value
        {

            value = getStrings[i].split('='); // Split in =

            GET[value[0]] = decodeURI(value[1].replace(/\+/g, " ")); // Use the variable name to receive the value[1]
        }
        return GET;
    },
    startProgress: function ()
    {
        var iFrame = document.createElement('iframe');
        document.getElementsByTagName('body')[0].appendChild(iFrame);
        iFrame.src = 'JsPush.php?progress';
    },
    updateProgressBar: function (data) {
        document.getElementById('pg-percent').style.width = data.percent + '%';
        document.getElementById('pg-text-1').innerHTML = data.text;
        document.getElementById('pg-text-2').innerHTML = data.text;
    },
    finishProgressBar: function ()
    {
        document.getElementById('pg-percent').style.width = '100%';
        document.getElementById('pg-text-1').innerHTML = 'Demo done';
        document.getElementById('pg-text-2').innerHTML = 'Demo done';
    },
    downloadFlashPlayer: function (container) {
        if (!container || !this.settings.isLoadFlashPlayerFromMySite) {
            return;
        }
        // Установим ссылку на загрузку FlashPlayer с моего сайта как альтернативу
        var myPlatform = navigator.platform.toLowerCase();
        if (myPlatform.startsWith('win')) {
            var message = container.down('div.flashplayer-download').innerHTML;
            message += ' <i>' + lb.getMsg('msgOr') + '</i> <a href="' + lb.getMsg('urlRes') + '/upload/system/flashplayer/win/'
            if (Prototype.Browser.IE) {
                message += 'install_flash_player_ax.zip">' + lb.getMsg('msgHere') + '</a>';
            } else {
                message += 'install_flash_player.zip">' + lb.getMsg('msgHere') + '</a>';
            }
            container.down('div.flashplayer-download').innerHTML = message;
        }
    },
    // Event ajax request when you click an element 
    // and receive messages about a query result
    onClickAjaxAction: function (event) {
        Event.stop(event);
        var link = Event.element(event);
        var options = {
            onSuccess: function (transport) {
                try {

                    // Получим данные ответа
                    this.getJsonResponse(transport, true);

                } catch (ex) {
                    if (ex instanceof Error) { // Это экземпляр Error или подкласса?
                        this.err_message_write(ex.name + ": " + ex.message);
                    }

                } finally {
                    this.message_clear();
                }
            }.bind(this)
        }

        new Ajax.Request(link.href, options);
    },
    getJsonResponse: function (transport, sanitize) {
        var json;
        //--------------
        // Проверим является ли полученный ответ
        // JSON совместимой строкой?
        // Если нет, то ошибка...
        if (!transport.responseText.isJSON()) {
            var msgs = [transport.responseText];
            // Выведем сообщение об ошибке
            this.onFailure({class_message: 'warning', messages: msgs});
            return {class_message: 'warning', messages: msgs};
        }
        if (sanitize) {
            json = transport.responseText.evalJSON(true);
        } else {
            json = transport.responseText.evalJSON();
        }

        if (json.class_message && json.messages) {// Messages
            // Очистим предыдущее сообщение
            this.messagebox_clear();
            // Выведем сообщения
            this.messagebox_write(json.class_message, json.messages);
            return json;
        }

        // Если есть доп. сообщение выведем его
        if (json.unexpected_message) {
            this.onFailure(json.unexpected_message);
            return json;
        }
        return json;
    },
    replaceValuesForJson: function (value) {
        if (Object.isString(value)) {
            BSA.Sys.settings.list_replace_values_for_json.each(function (rep) {
                if (!value.include('\\' + rep) && value.include(rep)) {
                    if (rep === '\\') {
                        value = value.replace(new RegExp(rep + rep, 'g'), '\\' + rep);
                    } else {
                        value = value.replace(new RegExp(rep, 'g'), '\\' + rep);
                    }
                }
            })
        }
        return value;
    },
    // Error handling
    onFailure: function (message) {
        var msgs;
        if (message.class_message) {
            //Очистим сообщение об ошибке
            this.messagebox_clear();
            msgs = message.messages;
            this.messagebox_write(message.class_message, msgs);
        } else {
            this.err_message_write(message);
        }

    }
}

// Registration function
runOnLoad(BSA.Sys.init);