/**
 * Class - CKEditorHtml
 *
 * With these class you can:
 *  - create an editor for Textarea
 *  - create an editor for Ajax
 *  - set and retrieve the contents of the editor
 *  - execute command editor
 *
 * JavaScript
 *
 * @author   Sergii Beskorovainyi <bsa2657@yandex.ru>
 * @license  MIT <http://www.opensource.org/licenses/mit-license.php>
 * @link     https://github.com/bsa-git/zf-myblog/
 */

BSA.CKEditorHtml = Class.create({

    toolbars: {
        toolbar_max: [
        {
            name: 'document', 
            items : [ 'Source','-','Save','NewPage','DocProps','Preview','-','Templates' ]
        },
        {
            name: 'clipboard', 
            items : [ 'Cut','Copy','Paste','PasteText','PasteFromWord','-','Undo','Redo' ]
        },
        {
            name: 'editing', 
            items : [ 'Find','Replace','-','SelectAll' ]
        },
        {
            name: 'forms', 
            items : [ 'Form', 'Checkbox', 'Radio', 'TextField', 'Textarea', 'Select', 'Button', 'ImageButton','HiddenField' ]
        },
        '/',
        {
            name: 'basicstyles', 
            items : [ 'Bold','Italic','Underline','Strike','Subscript','Superscript','-','RemoveFormat' ]
        },
        {
            name: 'paragraph', 
            items : [ 'NumberedList','BulletedList','-','Outdent','Indent','-','Blockquote','CreateDiv','-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock' ]
        },
        {
            name: 'links', 
            items : [ 'Link','Unlink','Anchor' ]
        },
        {
            name: 'insert', 
            items : [ 'Image','Flash','Table','HorizontalRule','Smiley','SpecialChar','PageBreak','Iframe' ]
        },
        '/',
        {
            name: 'styles', 
            items : [ 'Styles','Format','Font','FontSize' ]
        },
        {
            name: 'colors', 
            items : [ 'TextColor','BGColor' ]
        },
        {
            name: 'tools', 
            items : [ 'Maximize', 'ShowBlocks','-','About' ]
        }
        ],
        toolbar_medium: [//
        {
            name: 'document', 
            items : [ 'Save','Preview','-','Templates' ]
        },
        {
            name: 'clipboard', 
            items : [ 'Cut','Copy','Paste','PasteText','PasteFromWord','-','Undo','Redo' ]
        },
        {
            name: 'links', 
            items : [ 'Link','Unlink','Anchor' ]
        },
        {
            name: 'tools', 
            items : [ 'Maximize']
        },
        {
            name: 'document', 
            items : [ 'Source']
        },
        '/',
        {
            name: 'basicstyles', 
            items : [ 'Bold','Italic','Underline','-','RemoveFormat' ]
        },
        {
            name: 'paragraph', 
            items : [ 'NumberedList','BulletedList','-','Outdent','Indent','-','Blockquote','CreateDiv','-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock' ]
        },
        {
            name: 'insert', 
            items : [ 'Image','Flash','Table','HorizontalRule','Smiley','SpecialChar','PageBreak','Iframe' ]
        },
        '/',
        {
            name: 'styles', 
            items : [ 'Styles','Format','Font','FontSize' ]
        },
        {
            name: 'colors', 
            items : [ 'TextColor','BGColor' ]
        },
        {
            name: 'tools', 
            items : [ 'ShowBlocks','-','About' ]
        }
        ],
        toolbar_min: [
        {
            name: 'clipboard', 
            items : [ 'Cut','Copy','Paste','PasteText','PasteFromWord','-','Undo','Redo' ]
        },
        {
            name: 'links', 
            items : [ 'Link','Unlink','Anchor']
        },
        {
            name: 'insert', 
            items : [ 'Image','Table','HorizontalRule','SpecialChar']
        },
        {
            name: 'tools', 
            items : [ 'Maximize']
        },
        {
            name: 'document', 
            items : [ 'Source']
        },
        '/',
        {
            name: 'basicstyles', 
            items : [ 'Bold','Italic','Underline']
        },
        {
            name: 'paragraph', 
            items : [ 'NumberedList','BulletedList','-','Outdent','Indent','-','Blockquote','-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock']
        },
        {
            name: 'styles', 
            items : [ 'Styles','Format','Font','FontSize']
        },
        {
            name: 'tools', 
            items : [ 'About']
        }
        ],
        toolbar_min2: [
        {
            name: 'basicstyles', 
            items : [ 'Bold','Italic','Underline']
        },
        {
            name: 'paragraph', 
            items : [ 'NumberedList','BulletedList','-','Blockquote','CreateDiv','-','JustifyLeft','JustifyCenter','JustifyRight']
        },
        {
            name: 'links', 
            items : [ 'Link']
        },
        {
            name: 'insert', 
            items : [ 'Image','Table','Smiley']
        },
        {
            name: 'tools', 
            items : [ 'Maximize']
        },
        {
            name: 'document', 
            items : [ 'Source']
        }
        ]
    },

    editors: new Hash(),
    container: "",
    type: "textarea",// Тип обновления редактора: textarea, ajax
    html: null,
    config: {},
    
    // Object initialization
    initialize : function(params)
    {
        
        // Set the default configuration
        this.config.language = lb.getMsg('languageSite');
        
        // Connect the boot files
        this.config.filebrowserBrowseUrl = lb.getMsg('urlRes') + '/js/kcfinder/browse.php?type=files';
        this.config.filebrowserImageBrowseUrl = lb.getMsg('urlRes') + '/js/kcfinder/browse.php?type=images';
        this.config.filebrowserFlashBrowseUrl = lb.getMsg('urlRes') + '/js/kcfinder/browse.php?type=flash';
        this.config.filebrowserUploadUrl = lb.getMsg('urlRes') + '/js/kcfinder/upload.php?type=files';
        this.config.filebrowserImageUploadUrl = lb.getMsg('urlRes') + '/js/kcfinder/upload.php?type=images';
        this.config.filebrowserFlashUploadUrl = lb.getMsg('urlRes') + '/js/kcfinder/upload.php?type=flash';
        
        // If there are parameters that create the Editor
        if(params){
            if($(params.container) && params.config){
                this.container = params.container;
                if(params.type){
                    this.type = params.type;
                }
                if(this.type == "textarea"){
                    this.createEditorForTextarea(params.container, params.config);
                }else if(this.type == "ajax"){
                    this.createEditorForAjax(params.container, params.config, params.html);
                }
            }
        }
    },
    
    // Check whether there is an editor for the container
    isEditor: function(container)
    {
        var result = false;
        if(this.editors[container]){
            result = true;
        }
        return result;
    },

    // Create editor for AJAX
    createEditorForAjax: function(container, config, html)
    {
        if ( this.editors[container] )
            return;

        var toolbar = config.toolbar.toLowerCase();
        
        // Установим конфигурацию для toolbar
        if(!(toolbar == 'full' || toolbar == 'basic')){
            config.toolbar = this.toolbars["toolbar_" + toolbar]; 
        }

        Object.extend(this.config, config);

        this.editors[container] = CKEDITOR.appendTo( container, this.config, html );
    },
    
    // Remove editor for AJAX
    removeEditorForAjax: function(container){
        if (! this.editors[container] )
            return;


        this.html = this.editors[container].getData();

        // Retrieve the editor contents. In an Ajax application, this data would be
        // sent to the server or used in any other way.
        //document.getElementById(container).innerHTML = this.html;
        //document.getElementById( 'contents' ).style.display = '';

        // Destroy the editor.
        this.editors[container].destroy();
        this.editors[container] = null;
    },

    // Create editor for Textarea
    createEditorForTextarea: function(container, config)
    {
        if ( this.editors[container] )
            return;
        
        var toolbar = config.toolbar.toLowerCase();
        
        // Установим конфигурацию для toolbar
        if(!(toolbar == 'full' || toolbar == 'basic')){
            config.toolbar = this.toolbars["toolbar_" + toolbar]; 
        }

        Object.extend(this.config, config);
        this.editors[container] = CKEDITOR.replace( container, this.config );
    },
    
    // Insert HTML
    InsertHTML: function(container, html)
    {
        if (! this.editors[container] )
            return;
        // Get the editor instance that we want to interact with.
        var oEditor = this.editors[container];
        //var value = document.getElementById( 'htmlArea' ).value;

        // Check the active editing mode.
        if ( oEditor.mode == 'wysiwyg' )
        {
            // Insert HTML code.
            // http://docs.cksource.com/ckeditor_api/symbols/CKEDITOR.editor.html#insertHtml
            oEditor.insertHtml( html );
        }
        else
            alert( 'You must be in WYSIWYG mode!' );
    },

    // Insert text
    InsertText: function(container, text)
    {
        if (! this.editors[container] )
            return;
        // Get the editor instance that we want to interact with.
        var oEditor = this.editors[container];
        //var value = document.getElementById( 'txtArea' ).value;

        // Check the active editing mode.
        if ( oEditor.mode == 'wysiwyg' )
        {
            // Insert as plain text.
            // http://docs.cksource.com/ckeditor_api/symbols/CKEDITOR.editor.html#insertText
            oEditor.insertText( text );
        }
        else
            alert( 'You must be in WYSIWYG mode!' );
    },

    SetContent: function(container, html)
    {
        if (! this.editors[container] )
            return;
        // Get the editor instance that we want to interact with.
        var oEditor = this.editors[container];
        //var value = document.getElementById( 'htmlArea' ).value;

        // Set editor contents (replace current contents).
        // http://docs.cksource.com/ckeditor_api/symbols/CKEDITOR.editor.html#setData
        oEditor.setData( html );
    },

    GetContent: function(container)
    {
        if (! this.editors[container] )
            return "";
        // Get the editor instance that you want to interact with.
        var oEditor = this.editors[container];

        // Get editor contents
        // http://docs.cksource.com/ckeditor_api/symbols/CKEDITOR.editor.html#getData
        return oEditor.getData();
    },

    ExecuteCommand: function(container, commandName)
    {
        if (! this.editors[container] )
            return;
        // Get the editor instance that we want to interact with.
        var oEditor = this.editors[container];

        // Check the active editing mode.
        if ( oEditor.mode == 'wysiwyg' )
        {
            // Execute the command.
            // http://docs.cksource.com/ckeditor_api/symbols/CKEDITOR.editor.html#execCommand
            oEditor.execCommand( commandName );
        }
        else
            alert( 'You must be in WYSIWYG mode!' );
    },

    CheckDirty: function (container)
    {
        if (! this.editors[container] )
            return;
        // Get the editor instance that we want to interact with.
        var oEditor = this.editors[container];
        // Checks whether the current editor contents present changes when compared
        // to the contents loaded into the editor at startup
        // http://docs.cksource.com/ckeditor_api/symbols/CKEDITOR.editor.html#checkDirty
        return oEditor.checkDirty();
    },

    ResetDirty: function (container)
    {
        if (! this.editors[container] )
            return;
        // Get the editor instance that we want to interact with.
        var oEditor = this.editors[container];
        // Resets the "dirty state" of the editor (see CheckDirty())
        // http://docs.cksource.com/ckeditor_api/symbols/CKEDITOR.editor.html#resetDirty
        oEditor.resetDirty();
    //alert( 'The "IsDirty" status has been reset' );
    }
})

// The function is executed after the download of the browser window
// are created objects, which are entered in the list of instances
// ex. $H(CKEditorHtml: [new CKEditorHtml(param1), ... ,new CKEditorHtml(paramN)])
BSA.CKEditorHtml.RegRunOnLoad = function() {
    // Получим параметры для создания обьекта
    var params = scriptParams.get('CKEditorHtml');
    // Ф-ия создания обьектов по их параметрам
    var createObject = function (param) {
        var editor = scriptInstances.get('CKEditorHtml');
        if (editor) {
            editor.push(new BSA.CKEditorHtml(param));
        } else {
            scriptInstances.set('CKEditorHtml', [new BSA.CKEditorHtml(param)]);
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
runOnLoad(BSA.CKEditorHtml.RegRunOnLoad);