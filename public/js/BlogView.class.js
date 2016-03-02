/**
 * Class - BlogView
 *
 * With these class you can:
 *  - edit images, for viewing with LightBox
 *  - Change events links blog content, 
 *  when you click on the link must open a separate window
 *
 * JavaScript
 *
 * @author   Sergii Beskorovainyi <bsa2657@yandex.ru>
 * @license  MIT <http://www.opensource.org/licenses/mit-license.php>
 * @link     https://github.com/bsa-git/zf-myblog/
 */
BSA.BlogView = Class.create({
    url_report: null,
    url_params: null,
    post_id: null, // Post ID
    user_id: null, // User ID
    containers: [], // an array of containers, which are made actions
    isModifiedEventsMarker: null,
    dialog_info: null,
    local: 'ru',
    
    // Object initialization
    initialize: function (params)
    {
        if (params.containers) {
            this.containers = params.containers;
        }

        // Set language
        this.local = lb.getMsg('languageSite');

        // Initialize button - 'content-in-new-window' 
        var btnShowInWindow = $('content-in-new-window');
        if (btnShowInWindow) {
            // Show icon
            btnShowInWindow.show();

            // Assign an event - to display the content in a separate window
            btnShowInWindow.observe('click', this.onOpenContentWin.bind(this));
        }

        // Open dialog
        if (params.dialog_info) {
            this.dialog_info = params.dialog_info;

            if (params.dialog_info.open) {
                BSA.Dialogs.openDialogInfo(params.dialog_info);
            }
        }

        // Initialize button - 'get-report-pdf'
        var btnGetReportPDF = $('get-report-pdf');
        if (btnGetReportPDF) {
            // Assign an event - to display the content in a separate window
            btnGetReportPDF.observe('click', this.onGetReportPDF.bindAsEventListener(this));
        }

        // Change html code of images and links
        if (this.containers.size()) {
            this.modifyContentImages();
            this.modifyContentLinks();
        }

        // Establish event for the information help
        var listInfoWin = $$('.help-info-win');
        if (listInfoWin) {
            listInfoWin.each(function (aInfoWin) {
                aInfoWin.observe('click', this.onClickInfoWin.bind(this));
            }.bind(this))
        }

        // Initialization information hints
        TooltipManager.init("tooltip", {
            url: lb.getMsg('urlBase') + '/admin/info/hint?local=' + this.local,
            options: {
                method: 'get'
            }
        }, {
            showEffect: Element.show,
            hideEffect: Element.hide
        });

        // Initialization iframes
        this.containers.each(function (container_id) {
            if ($(container_id)) {
                var iframes = $(container_id).select('iframe');
                iframes.each(function (iframe) {
                    var frameborder = iframe.readAttribute('frameborder');
                    if (!frameborder) {
                        iframe.writeAttribute('frameborder', 0);
                    }

                    // Reload this page if the frame attribute 'src' = "". 
                    // This error appears at the chromium to exit edit mode 
                    // when you create a frame that loads the page from its own module
                    var src = iframe.readAttribute('src');
                    if (!src) {
                        window.location.reload();
                    }
                })
            }
        })

    },
    //-------------- Information help ---------------

    // Открыть окно с информацией
    onClickInfoWin: function (event) {
        Event.stop(event);
        var aInfo = Event.element(event);
        if (aInfo.up('a[href="#"]')) {
            aInfo = aInfo.up('a[href="#"]');
        }
        var info_key = aInfo.readAttribute('id');
        var options = {
            parameters: {
                info_key: info_key,
                local: this.local
            },
            onSuccess: this.loadInfoSuccess.bind(this)
        }

        new Ajax.Request(lb.getMsg('urlBase') + '/admin/info/view', options);
    },
    loadInfoSuccess: function (transport)
    {
        //----------------------
        try {

            // Получим данные ответа
            var json = BSA.Sys.getJsonResponse(transport, true);

            // Проверим есть ли ошибки
            if (!json.class_message) {// OK

                // Откроем окно для редактирования
                this.openInfoWin(json.title, json.content);
            }
        } catch (ex) {
            if (ex instanceof Error) { // Это экземпляр Error или подкласса?
                this.onFailure(ex.name + ": " + ex.message);
            }

        } finally {
            BSA.Sys.message_clear();
        }
    },
    // Открыть окно с информацией
    openInfoWin: function (title, content)
    {
        // Создадим обьект окна
        var win = new Window({
            className: "mac_os_x",
            //className: "dialog",
            title: lb.getMsg('msgInformation'),
            width: 500,
            height: 400,
            destroyOnClose: true,
            recenterAuto: false
        });
        var info_content = '<div class="info-content" id="info_content" >'
                + '<table width="100%" height="100%" border="0" cellpadding="5" cellspacing="0">'
                + '<tr>'
                + '<th><h1>'
                + title
                + '</h1></th>'
                + '</tr>'
                + '<tr>'
                + '<td align="left" valign="top" >'
                + content
                + '</td>'
                + '</tr>'
                + '</table>'
                + '</div>'
        win.getContent().update(info_content);
        win.showCenter();

    },
    //-------------- Modification of content: images, links ---------------

    // Change the html code images, so they can be viewed using lightbox.js
    //
    // - ex. tag <img alt="" src="/upload/users/user1/.thumbs/images/bsa.jpg" style="width: 100px; height: 100px; ">
    //   to tag -> <a href="/upload/users/user1/images/bsa.jpg"" rel="lightbox[location]">
    //               <img alt="" src="/upload/users/user1/.thumbs/images/bsa.jpg" style="width: 100px; height: 100px; ">
    //             </a>
    modifyContentImages: function ()
    {
        var scr = '';
        var rel_lightbox = '';
        //--------------------------------------------
        this.containers.each(function (container_id) {
            if ($(container_id)) {
                $(container_id).select('img').each(function (img) {
                    scr = img.readAttribute('src');
                    if (scr.include('/.thumbs/')) {
                        scr = scr.sub('.thumbs/', '');
                        // Определим есть ли у img обертка в виде ссылки 
                        var img_wrap = img.up('a[href="' + scr + '"]');
                        if (img_wrap) {// Yes
                            rel_lightbox = img_wrap.readAttribute("rel");
                            if (!rel_lightbox) {
                                img_wrap.writeAttribute('rel', 'lightbox[' + container_id + ']');
                            }
                        } else {// No
                            img.wrap('a', {
                                'href': scr,
                                'rel': 'lightbox[' + container_id + ']'  //content
                            });
                        }
                    }
                    scr = '';
                });
            }
        })
    },
    // Change events links blog content. 
    // Clicking on the link will open a separate window should.
    modifyContentLinks: function ()
    {
        var rel;
        //-----------------------------
        // Проверим были уже изменены ссылки
        if (this.isModifiedEventsMarker) {
            return;
        } else {
            this.isModifiedEventsMarker = true;
        }


        var arrListFileResources = $A(BSA.Sys.settings.list_file_resources);
        var isFileResource = false;
        var href = '';
        // Назначим для ссылок, кроме ссылок на фотографии
        // новые события, которые будут открывать отдельное окно для этой ссылки
        this.containers.each(function (container_id) {
            if ($(container_id)) {
                $(container_id).select('a').each(function (a) {
                    rel = a.readAttribute('rel');
                    if (!rel) {
                        // Получим URL ссылки
                        href = a.readAttribute('href');
                        // Не обрабатываем пустую ссылку
                        if (!href) {
                            return;
                        }

                        // Не обрабатываем ссылку, которая не начинается с "/"
                        // пр. href="39"
                        if (!href.startsWith("http") &&
                                !href.startsWith("https") &&
                                !href.startsWith("/")) {
                            return;
                        }

                        // Определим является ли ссылка файловым ресурсом
                        arrListFileResources.each(function (s) {
                            if (href.endsWith(s)) {
                                isFileResource = true;
                                return;
                            }
                        });

                        // Если ссылка не файловый ресурс, то откроем ее в отдельном окне
                        if (isFileResource) {
                            isFileResource = false;
                            a.writeAttribute('target', '_blank');
                        } else {
                            a.observe('click', this.onOpenWinForURL.bind(this));
                        }

                    }
                }.bind(this));
            }
        }.bind(this))
    },
    //-------------- Windows ---------------

    onOpenContentWin: function (event)
    {
        var container = null;
        //-----------------

        if (this.containers.size()) {
            container = $(this.containers[0]);
        } else {
            return;
        }

        // Отменим поведение по умолчанию
        Event.stop(event);

        var title = $('title').innerHTML;
        // Создадим обьект окна
        var win = new Window({
            className: "mac_os_x",
            //className: "dialog",
            title: lb.getMsg('msgMessage'),
            width: 600,
            height: 400,
            destroyOnClose: true,
            recenterAuto: false

        });// 
        var win_content = '<div class="win-content" id="win_content" >'
                + '<table width="100%" height="100%" border="0" cellpadding="5" cellspacing="0">'
                + '<tr>'
                + '<th><h2>'
                + title
                + '</h2></th>'
                + '</tr>'
                + '<tr>'
                + '<td align="left" valign="top" >'
                + container.innerHTML
                + '</td>'
                + '</tr>'
                + '</table>'
                + '</div>'
        win.getContent().update(win_content);
        win.showCenter();

    },
    onOpenWinForURL: function (event)
    {
        // Отменим поведение по умолчанию
        Event.stop(event);

        // Получим элемент, вызвавший событие
        var element = Event.element(event);
        var nodeName = element.nodeName;
        nodeName = nodeName.toLowerCase();
        if (nodeName == "img") {
            element = element.up("a");
        }

        // Получим URL ссылки
        var href = element.readAttribute('href');
        // Откроем окно для этого URL
        //this.onOpenWinWithURL(href);
        var win = new Window({
            className: "mac_os_x",
            title: "",
            top: 0,
            left: 0,
            width: 600,
            height: 400,
            url: href,
            showEffectOptions: {
                duration: 1.5
            }
        })
        win.showCenter();
        win.show();
    },
    //-------------- Report ---------------

    onGetReportPDF: function (event)
    {
        Event.stop(event);

        $('wait-loading').show();

        // Откроем диалог ожидания
        BSA.Dialogs.openDialogInfo(this.dialog_info);

        //--- Обратимся к котроллеру по URL --
        //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
    },
    closeDialogInfo: function (event)
    {
        // Отменим поведение по умолчанию
        //        Event.stop(event);

        BSA.Dialogs.closeDialogInfo();

        //        window.location =  lb.getMsg('urlBase') + this.dialog_info.url_cancel;

    },
    //----- Handling errors ------
    onFailure: function (message) {
        var msgs;
        if (message.class_message) {
            //Очистим сообщение об ошибке
            BSA.Sys.messagebox_clear();
            msgs = message.messages;
            BSA.Sys.messagebox_write(message.class_message, msgs);
        } else {
            BSA.Sys.err_message_write(message);
        }

    }
});

// The function is executed after the download of the browser window
// are created objects, which are entered in the list of instances
// ex. $H(BlogView: [new BlogView(param1), ... ,new BlogView(paramN)])
BSA.BlogView.RegRunOnLoad = function () {
    // Получим параметры для создания обьекта
    var params = scriptParams.get('BlogView');
    // Ф-ия создания обьектов по их параметрам
    var createObject = function (param) {
        var view = scriptInstances.get('BlogView');
        if (view) {
            view.push(new BSA.BlogView(param));
        } else {
            scriptInstances.set('BlogView', [new BSA.BlogView(param)]);
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
runOnLoad(BSA.BlogView.RegRunOnLoad);