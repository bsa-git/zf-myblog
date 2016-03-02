/**
 * Class - BlogComments
 *
 * This class allows:
 *  - add comment user
 *  - delete comment user
 *  - to respond to user comments
 *
 * JavaScript
 *
 * @author   Sergii Beskorovainyi <bsa2657@yandex.ru>
 * @license  MIT <http://www.opensource.org/licenses/mit-license.php>
 * @link     https://github.com/bsa-git/zf-myblog/
 */
BSA.BlogComments = Class.create({
    
    commentsContainer: null,
    ckeditor: null,
    win: null,
    reply_id: 0,
    type_action: "add",
    replyTemplate: "",
    
    // Object initialization
    initialize : function(params)
    {
        // Получим контейнер для комментариев
        if(params && $(params.container)){
            this.commentsContainer = $(params.container);
        }else{
            return;
        }
        
        // Найдем редактор
        if(scriptInstances.get('CKEditorHtml') && !this.ckeditor){
            scriptInstances.get('CKEditorHtml').each(function(ckeditor) {
                if(ckeditor.container == "ckeditor_comment"){
                    this.ckeditor = ckeditor;
                }
            }.bind(this));
        }
            
        // Назначим событие удаления комментария
        this.commentsContainer.select('a.delComment').each(function(del) {
            del.observe('click', this.onDeleteCommentClick.bind(this));
        }.bind(this));
            
        // Назначим событие ответа на комментарий
        this.commentsContainer.select('a.replyComment').each(function(reply) {
            reply.observe('click', this.onReplyCommentClick.bind(this));
        }.bind(this));
        
        // Назначим событие редактирования комментария
        this.commentsContainer.select('a.editComment').each(function(edit) {
            edit.observe('click', this.onEditCommentClick.bind(this));
        }.bind(this));
            
        // Назначим событие добавления комментария
        var addComment = $('form-add-comment');
        if(addComment){
            addComment.observe('submit', this.onCommentClick.bind(this));
        }
        
        // Назначим событие очистить комментарий
        if(addComment){
            addComment.observe('reset', this.onResetCommentClick.bind(this));
        }
        
        // Шаблон формы для ответа на комментарий
        var replyTemplate = new Template('<fieldset id="container-reply-comment" class="myfrm">'
            +'<legend>#{msgReplyToComment}</legend>'
            +'<div id="ckeditor_reply_comment"></div>'
            +'<br />'
            +'<input type="submit" class="btn btn-primary" name="save_reply_content" id="save_reply_content" value="#{msgAddComment}" /> '
            +'<input type="reset" class="btn" name="reset_reply_content" id="reset_reply_content" value="#{msgСlean}" />'
            +'</fieldset>');
    
        // our data to be formatted by the template
        var show = {
            msgReplyToComment: lb.getMsg('msgReplyToComment'),
            msgAddComment: lb.getMsg('msgAddComment'),
            msgСlean: lb.getMsg('msgСlean')
        };
        // let's format our data
        this.replyTemplate = replyTemplate.evaluate(show);
    },
    
    //------ Add/Delete user comment --------//
    
    onCommentClick : function(e)
    {
        var ckeditor_container = "";
        //--------------
        Event.stop(e);

        var form = $("form-add-comment");
        
        // Получим комментарий
        if(this.reply_id){
            ckeditor_container = 'ckeditor_reply_comment';
        }else{
            ckeditor_container = 'ckeditor_comment';
        }
        var comment = this.ckeditor.GetContent(ckeditor_container);
        
        // Сделаем запрос на сервер на добавление/изменение комментрария
        var options = {
            method     : form.method,
            parameters : {
                reply_id: this.reply_id,
                ckeditor_comment: comment
            },
            onSuccess  : this.onCommentSuccess.bind(this)
        }
        var formAction = form.action + "?type_action=" + this.type_action;
        new Ajax.Request(formAction, options);
    },
    
    onCommentSuccess : function(transport)
    {
        try {

            // Получим данные ответа
            var json = BSA.Sys.getJsonResponse(transport, true);
                    
            // Проверим результат
            if (json.added) {// OK
                
                // Выведем результат операции
                BSA.Sys.message_write(json.result);
                
                if(this.type_action == "reply"){
                    // Вставим комментарий
                    $("itemComment-" + this.reply_id).insert(json.html);
                }else if(this.type_action == "add"){
                    // Вставим комментарий
                    $("allComment").insert(json.html);
                    // Очистим комментарий
                    this.ckeditor.SetContent("ckeditor_comment","");
                }else if(this.type_action == "edit"){
                    // Обновим комментарий
                    $("itemComment-" + this.reply_id).down("div.bodyComment").update(json.html);
                }
                if(this.type_action == "add" || this.type_action == "reply"){
                    // Установим кол. оставшихся комментариев
                    this.setCommentCount();
                
                    // Назначим событие удаления комментария
                    var del = $("itemComment-" + json.comment_id).down("a.delComment");
                    del.observe('click', this.onDeleteCommentClick.bind(this));
            
                    // Назначим событие ответа на комментарий
                    var reply = $("itemComment-" + json.comment_id).down("a.replyComment");
                    reply.observe('click', this.onReplyCommentClick.bind(this));
                
                    // Назначим событие редактирования комментария
                    var edit = $("itemComment-" + json.comment_id).down("a.editComment");
                    edit.observe('click', this.onEditCommentClick.bind(this));
                }
                // Закроем окно ввода комментария
                this.closeReplyWin();
                
            }else{ // ERROR
                // Закроем окно ввода комментария
                this.closeReplyWin();
            }
        } catch (ex) {
            if (ex instanceof Error) { // Это экземпляр Error или подкласса?
                this.onFailure(ex.name + ": " + ex.message);
            }

        } finally {
            BSA.Sys.message_clear();
        }
    },
    
    
    onResetCommentClick : function(e)
    {
        var ckeditor_container = "";
        //--------------
        Event.stop(e);
        
        if(this.reply_id){
            ckeditor_container = 'ckeditor_reply_comment';
        }else{
            ckeditor_container = 'ckeditor_comment';
        }
        this.ckeditor.SetContent(ckeditor_container,"");
        
    },
    
        
    //------------ Reply on user comment -----------//
    
    onReplyCommentClick : function(e)
    {
        Event.stop(e);
        
        var aReply = Event.element(e);
        this.reply_id = aReply.readAttribute("href");
        this.type_action = "reply";
        
        // Проверим авторизацию пользователя
        if(aReply.hasClassName('authenticated')){
            // Откроем окно с контейнером
            this.openReplyWin("", this.replyTemplate);
                
            // Вставим редактор ckeditor в контейнер 
            var config = {
                toolbar : 'min2'
            }
            this.ckeditor.createEditorForAjax('ckeditor_reply_comment', config, ""); 
            
            // Назначим событие добавления комментария
            $("save_reply_content").observe('click', this.onCommentClick.bind(this));
            
            // Назначим событие очистки редактора
            $("reset_reply_content").observe('click', this.onResetCommentClick.bind(this));
            
        }else{
            alert(lb.getMsg('msgAddCommentRegisteredUsers')+"!")
        }
        
        
    },
    
    //------------ Edit user comment -----------//
    
    onEditCommentClick : function(e)
    {
        Event.stop(e);
        
        var aEdit = Event.element(e);
        this.reply_id = aEdit.readAttribute("href");
        this.type_action = "edit";
        
        // Откроем окно с контейнером
        this.openReplyWin("", this.replyTemplate);
        
        // Получим существующий комментарий 
        var editComment = $("itemComment-" + this.reply_id).down("div.bodyComment").innerHTML;
                
        // Вставим редактор ckeditor в контейнер 
        var config = {
            toolbar : 'min2'
        }
        this.ckeditor.createEditorForAjax('ckeditor_reply_comment', config, editComment); 
            
        // Назначим событие добавления комментария
        $("save_reply_content").observe('click', this.onCommentClick.bind(this));
            
        // Назначим событие очистки редактора
        $("reset_reply_content").observe('click', this.onResetCommentClick.bind(this));
            
    },

    
    //------------ Delete user comment -----------//
    
    onDeleteCommentClick : function(e)
    {
        Event.stop(e);
        
        if (confirm(lb.getMsg('msgDeleteComment')+"?")) {
            var comment_ids = [];
            // Найдем уникальный код комментария и добавим его в массив
            var delete_comment = Event.element(e);
            var comment_id = delete_comment.readAttribute('href');
            
            // Найдем все дочерние комментарии
            $("itemComment-" + comment_id).select("a.replyComment").each(function(replyComment) {
                comment_ids.push(replyComment.readAttribute('href'));
            }.bind(this));
            
            var options = {
                parameters : {
                    comment_ids: Object.toJSON(comment_ids)
                },
                onSuccess  : this.onDeleteCommentSuccess.bind(this)
            }
        
            var form = $("form-add-comment");
            var formAction = form.action + "?type_action=delete"
            new Ajax.Request(formAction, options);
        }
    },

    onDeleteCommentSuccess : function(transport)
    {
        try {
            // Получим данные ответа
            var json = BSA.Sys.getJsonResponse(transport, true);
                    
            // Проверим результат
            if (json.deleted) {// OK
                // Выведем результат операции
                BSA.Sys.message_write(json.result);
                // Удалим комментарий
                $("itemComment-" + json.comment_id).remove();
                
                // Установим кол. оставшихся комментариев
                this.setCommentCount();
            }
        } catch (ex) {
            if (ex instanceof Error) { // Это экземпляр Error или подкласса?
                this.onFailure(ex.name + ": " + ex.message);
            }

        } finally {
            BSA.Sys.message_clear();
        }
    },
    
    setCommentCount : function()
    {
        // Определим кол. оставшихся комментариев
        var count = $("allComment").select("div.itemComment").size();
        var tabActive = $("tabs-post").down("a.tabActive");
        tabActive.innerHTML = lb.getMsg('msgComments')+"("+count+")";
        
        // Покажем сообщение
        (count > 0)? $("no-comments").hide():$("no-comments").show();
    },
    
    //-------------- Working with windows ---------------

    openReplyWin : function(title, values)
    {
        // Удалим окно, если оно есть
        this.closeReplyWin();

        // Создадим обьект окна
        this.win = new Window({
            className: "mac_os_x",
            title: lb.getMsg('msgMessage'),
            width:680,
            height:440,
            zIndex: 100,
            minimizable: false,
            maximizable: false,
            destroyOnClose: false,
            closeCallback: function(){// Ф-ия при закрытии окна
                this.closeReplyWin();
            }.bind(this),
            recenterAuto:true
        });
        
        var win_content =  '<div class="win-content" id="win_content" >'
        + '<table width="100%" height="100%" border="0" cellpadding="5" cellspacing="0">'
        + '<tr>'
        + '<th>'
        + title
        + '</th>'
        + '</tr>'
        + '<tr>'
        + '<td align="left" valign="top" >'
        + values
        + '</td>'
        + '</tr>'
        + '</table>'
        + '</div>';
        this.win.getContent().update(win_content);
        // Покажем окно
        //        this.win.showCenter(false, null, null);
        this.win.showCenter();
    },
    
    closeReplyWin : function()
    {
        if(this.win){
            
            this.reply_id = 0;
            this.type_action = "add";
            
            // Закроем редактор
            if(this.ckeditor.isEditor('ckeditor_reply_comment')){
                this.ckeditor.removeEditorForAjax('ckeditor_reply_comment');
            }
            if($('container-reply-comment')){
                $('container-reply-comment').remove();
            }
            this.win.destroy();
            this.win = null;
        }
    },
    
    onFailure : function(message) {
        var msgs;
        if(message.class_message){
            //Очистим сообщение об ошибке
            BSA.Sys.messagebox_clear();
            msgs = message.messages;
            BSA.Sys.messagebox_write(message.class_message, msgs);
        }else{
            BSA.Sys.err_message_write(message);
        }

    }
})

// The function is executed after the download of the browser window
// are created objects, which are entered in the list of instances
// ex. $H(BlogComments: [new BlogComments(), ... ,new BlogComments()])
BSA.BlogComments.RegRunOnLoad = function() {
    // Получим параметры для создания обьекта
    var params = scriptParams.get('BlogComments');
    // Ф-ия создания обьектов по их параметрам
    var createObject = function (param) {
        var blogComments = scriptInstances.get('BlogComments');
        if (blogComments) {
            blogComments.push(new BSA.BlogComments(param));
        } else {
            scriptInstances.set('BlogComments', [new BSA.BlogComments(param)]);
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
runOnLoad(BSA.BlogComments.RegRunOnLoad);