<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
    <head>
        <title>{$title|escape}</title>
        <link rel="shortcut icon" href="{'/images/system/users.png'|urlres}">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <script type="text/javascript" src="{'/js/prototype/prototype.js'|urlres}"></script>
        <script type="text/javascript" src="{'/js/scriptaculous/scriptaculous.js'|urlres}"></script>
        <script type="text/javascript" src="{'/js/BSA.Bootstrap.js'|urlres}"></script>
        <script type="text/javascript" src="{'/js/BSA.System.js'|urlres}"></script>
        <script type="text/javascript" src="{'/js/LangBox.class.js'|urlres}"></script>
        
        
        {if $accordion}
            <script type="text/javascript" src="{'/js/accordion/accordion.js'|urlres}"></script>
            <script type="text/javascript" src="{'/js/AccordionBox.class.js'|urlres}"></script>
            <link rel="stylesheet" href="{'/js/accordion/css/accordion.css'|urlres}" type="text/css" />
        {/if}
        {if $buttons}
            <link rel="stylesheet" href="{'/css/buttons/buttons.css'|urlres}" type="text/css" media="screen" />
        {/if}
        {if $feedUrl|strlen > 0 && $feedTitle|strlen > 0}
            <link rel="alternate" type="application/atom+xml" title="{$feedTitle|escape}" href="{$feedUrl|escape}" />
        {/if}
        {if $lightbox}
            <script type="text/javascript" src="{'/js/lightbox/lightbox.js'|urlres}"></script>
            <link rel="stylesheet" href="{'/js/lightbox/css/lightbox.css'|urlres}" type="text/css" media="screen" />
        {/if}
        {if $carousel}
            <script type="text/javascript" src="{'/js/carousel/carousel.js'|urlres}"></script>
            <script type="text/javascript" src="{'/js/CarouselView.class.js'|urlres}"></script>
            <link rel="stylesheet" href="{'/js/carousel/css/carousel.css'|urlres}" type="text/css" />
        {/if}
        {if $fileuploader}
            <script type="text/javascript" src="{'/js/fileuploader/fileuploader.js'|urlres}"></script>
            <link rel="stylesheet" href="{'/js/fileuploader/css/fileuploader.css'|urlres}" type="text/css" />
        {/if}
        {if $ajaxform}
            <script type="text/javascript" src="{'/js/AjaxForm.class.js'|urlres}"></script>
        {/if}
        {if $ckeditor}
            <script type="text/javascript" src="{'/js/ckeditor/ckeditor.js'|urlres}"></script>
            <script type="text/javascript" src="{'/js/ckeditor/config.js?t=CAPD'|urlres}"></script>
            <script type="text/javascript" src="{'/js/ckeditor/styles.js?t=CAPD'|urlres}"></script>
            <link rel="stylesheet" href="{'/js/ckeditor/skins/moono/editor.css'|urlres}" type="text/css" />
            <script type="text/javascript" src="{'/js/CKEditor.class.js'|urlres}"></script>
        {/if}
        {if $windows}
            <script type="text/javascript" src="{'/js/windows/window.js'|urlres}"></script>
            <script type="text/javascript" src="{'/js/windows/tooltip.js'|urlres}"></script>
            <link rel="stylesheet" href="{'/js/windows/themes/default.css'|urlres}" type="text/css" />
            <link rel="stylesheet" href="{'/js/windows/themes/mac_os_x.css'|urlres}" type="text/css" />
            <link rel="stylesheet" href="{'/js/windows/themes/alphacube.css'|urlres}" type="text/css" />
            <link rel="stylesheet" href="{'/css/contents/contents.css'|urlres}" type="text/css" />
        {/if}
        {if $dialog}
            <script type="text/javascript" src="{'/js/BSA.Dialogs.js'|urlres}"></script>
            <link rel="stylesheet" href="{'/css/dialogs/dialogs.css'|urlres}" type="text/css" />
        {/if}
        {if $utilities}
            <script type="text/javascript" src="{'/js/BSA.Utilities.js'|urlres}"></script>
        {/if}
        {if $table}
            <script type="text/javascript" src="{'/js/myui/myui.js'|urlres}"></script>
            <script type="text/javascript" src="{'/js/MyTableGrid.class.js'|urlres}"></script>
            {literal}
                <!--[if lte IE 7]>
                    <style type="text/css">
                        .my-autocompleter { 
                            display: inline; /* Строчный элемент */
                            zoom: 1; /* Устанавливаем hasLayout */
                        }
                        .my-combobox-button {
                            top: 1px; /* добавим  1px сверху*/
                            right : -1px; /* отступим на  1px вправо*/
                            width: 17px; /* добавим  1px в ширину*/
                            border-left: solid #609 1px;
                        }
                        #search-button {
                            position: absolute;
                            top: 12px;
                            left : 180px;
                        }
                    </style>
                <![endif]-->
            {/literal}
        {/if}
        
        {if $maps}
            <script type="text/javascript" src="http://www.google.com/jsapi?key={$googleMapsKey|escape}"></script>

            {if $section == 'blogmanager'}
                <script type="text/javascript" src="{'/js/BlogLocationManager.class.js'|urlres}"></script>
            {/if}
        {/if}
        {if $report_style}
            <link rel="stylesheet" href="{'/css/report/blue-style.css'|urlres}" type="text/css" />
        {/if}
        {if $sections}
            <link rel="stylesheet" href="{'/css/sections/sections.css'|urlres}" type="text/css" />
        {/if}
        <link rel="stylesheet" href="{'/css/styles.css'|urlres}" type="text/css" media="all" />
        {if $report}
            <link rel="stylesheet" href="{'/css/report/report.css'|urlres}" type="text/css" media="screen" />
        {/if}
        <link rel="stylesheet" href="{'/css/print/print.css'|urlres}" type="text/css" media="print" />
        <link rel="stylesheet" href="{'/css/mystyle.css'|urlres}" type="text/css" media="all" />
    </head>
    <body>
        {*Общие данные для передачи в JavaScript*}
        {include file='lib/msg-box/common.tpl'}

        {* Заголовок *}
        <div id="header">
            <img src="{'/images/system/logo.gif'|urlres}" alt="" />
            {*Вывод кнопок локализации сайта*}
            {include file='lib/lang-buttons.tpl'}
        </div>
        <div id="nav">
            {*Вывод верхнего меню*}
            {include file='lib/menu-top.tpl'}
        </div>

        <div id="content-container" class="column">
            <div id="content">
                <div id="breadcrumbs">
                    {breadcrumbs trail=$breadcrumbs->getTrail() separator=' &raquo; '}
                </div>
                <div id="title">
                    <h1>{$title|escape}
                        {if $feedUrl|strlen > 0 && $feedTitle|strlen > 0}
                        <a href="{$feedUrl|escape}" title="{$feedTitle|escape}">
                            <img src="{'/images/system/feed-icon-14x14.png'|urlres}"
                                alt="{$feedTitle|escape}" />
                        </a>
                        {/if}
                    </h1>
                </div>
