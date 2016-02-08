<!DOCTYPE html>
<html>
    <head>
        <!-- META INFO -->
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">{* edge, 7,8,9,10 *}
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
        <link rel="shortcut icon" href="{'/images/system/users.png'|urlres}">
        <!-- TITLE -->
        <title>{$title|escape}</title>
        <!-- CSS -->
        <link rel="stylesheet" href="{'/css/bootstrap.min.css'|urlres}" type="text/css" media="screen" />
        <link rel="stylesheet" href="{'/css/font-awesome.min.css'|urlres}" type="text/css" media="all" />
        <link href='http://fonts.googleapis.com/css?family=Playball' rel='stylesheet' type='text/css'>
        <link rel="stylesheet" href="{'/css/reset.css'|urlres}" type="text/css" media="screen" />
        <link rel="stylesheet" href="{'/css/layout.css'|urlres}" type="text/css" media="screen" />
        {if ! $report}
            <link rel="stylesheet" href="{"/css/schemes/$scheme.css"|urlres}" type="text/css" media="screen" />
        {/if}
        {if $report}
            <link rel="stylesheet" href="{'/css/report/report.css'|urlres}" type="text/css" media="screen" />
        {/if}
        
        <link rel="stylesheet" href="{'/css/print/print.css'|urlres}" type="text/css" media="print" />        
        
        <!-- JS -->
        <script type="text/javascript" src="{'/js/prototype/prototype.js'|urlres}"></script>
        <script type="text/javascript" src="{'/js/scriptaculous/scriptaculous.js'|urlres}"></script>
        <script type="text/javascript" src="{'/js/BSA.Bootstrap.js'|urlres}"></script>
        <script type="text/javascript" src="{'/js/BSA.System.js'|urlres}"></script>
        <script type="text/javascript" src="{'/js/LangBox.class.js'|urlres}"></script>
        <script type="text/javascript" src="{'/js/SearchSuggestor.class.js'|urlres}"></script>
        
        {* Запомнить параметры скриптов в списке параметров *}
        <script type="text/javascript">
            addScriptParams('SearchSuggestor', 'search');
        </script>
        
        {if $feedUrl|strlen > 0 && $feedTitle|strlen > 0}
            <link rel="alternate" type="application/atom+xml" title="{$feedTitle|escape}" href="{$feedUrl|escape}" />
        {/if}
        {if $accordion}
            <script type="text/javascript" src="{'/js/accordion/accordion.js'|urlres}"></script>
            <script type="text/javascript" src="{'/js/AccordionBox.class.js'|urlres}"></script>
            <link rel="stylesheet" href="{'/js/accordion/css/accordion.css'|urlres}" type="text/css" />
        {/if}
        {if $paginator}
            <link rel="stylesheet" href="{'/css/paginator/paginator.css'|urlres}" type="text/css" media="screen" />
            <script type="text/javascript" src="{'/js/Paginator.class.js'|urlres}"></script>
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
        {if $tabs}
            <script type="text/javascript" src="{'/js/Tabs.class.js'|urlres}"></script>
            <link rel="stylesheet" href="{'/css/tabs/tabs-post.css'|urlres}" type="text/css" media="screen" />
        {/if}
        {if $comments}
            <link rel="stylesheet" href="{'/css/comments/rcheComment.css'|urlres}" type="text/css" media="screen" />
            <script type="text/javascript" src="{'/js/BlogComments.class.js'|urlres}"></script>
        {/if}
        {if $flowplayer}
            <script type="text/javascript" src="{'/js/flowplayer/flowplayer-3.2.11.min.js'|urlres}"></script>
        {/if}
        {if $youtube}
            <script type="text/javascript" src="{'/js/prototube/swfobject.js'|urlres}"></script>
            <script type="text/javascript" src="{'/js/prototube/prototube.js'|urlres}"></script>
            <link rel="stylesheet" href="{'/js/prototube/css/prototube.css'|urlres}" type="text/css" />
        {/if}
        {if $media}
            <link rel="stylesheet" href="{'/css/media/media.css'|urlres}" type="text/css" />
        {/if}
        {if $url_lib}
            <script type="text/javascript" src="{'/js/URL.class.js'|urlres}"></script>
        {/if}
        {if $plugins}
            <script type="text/javascript" src="{'/js/BrowserPlugins.class.js'|urlres}"></script>
        {/if}
        {if $maps}
            {*<script type="text/javascript" src="http://www.google.com/jsapi?key={$googleMapsKey|escape}"></script>*}
            
            <script async defer  src="https://maps.googleapis.com/maps/api/js?key={$googleMapsKey|escape}"></script>

            {if $section == 'blogmanager'}
                <script type="text/javascript" src="{'/js/BlogLocationManager.class.js'|urlres}"></script>
            {/if}
        {/if}
        {if isset($highlight)}        
            <script type="text/javascript" src="{'/js/highlight/highlight.pack.js'|urlres}"></script>
            <link rel="stylesheet" href="{'/js/highlight/css/railscasts.css'|urlres}" type="text/css" />
            <script type="text/javascript" src="{'/js/Highlight.class.js'|urlres}"></script>
        {/if}
        
        {* Загрузим mystyle.css *}
        <link rel="stylesheet" href="{'/css/mystyle.css'|urlres}" type="text/css" media="all" />
        
    </head>
    <body>
        {*Общие данные для передачи в JavaScript*}
        <!-- MSG-BOX -->
        {include file='lib/msg-box/common.tpl'}
        <!-- HEADER -->
        <div id="header">
            {include file='lib/header-logotype.tpl'}
            {include file='lib/menu-top.tpl'}
        </div>
        <!-- BREADCRUMBS -->
        <div id="breadcrumbs">
            {breadcrumbs trail=$breadcrumbs->getTrail() separator=' &raquo; '}
        </div>
        <!-- LAYOUT -->
        <div class="layout">
            <!-- LEFT COLUMN -->
            <div id="left-container">
                <div class="column">
                    {include file='lib/box-search.tpl'}
                    {if isset($leftcolumn) && $leftcolumn|strlen > 0}
                        {include file=$leftcolumn}
                    {/if}
                </div>
            </div>
            <!-- RIGHT COLUMN -->
            <div id="right-container">
                <div class="column">
                    {*Аутентификация пользователя*}
                    {include file='account/lib/box-authentication.tpl'}
                    {*Вывод сообщений*}
                    {include file='lib/box-messages.tpl'}
                    {*Меню отладки *}
                    {include file='lib/box-debug.tpl'}
                    {*Правая колонка*}
                    {if isset($rightcolumn) && $rightcolumn|strlen > 0}
                        {include file=$rightcolumn}
                    {/if}
                    {*Предупреждение при отключенном JavaScript*}
                    <noscript>
                        <div class="box2">
                            Если хотите получить дополнительные возможности, то включите JavaScript!
                            <a href="http://www.google.ru/support/bin/answer.py?answer=23852">Как?</a>
                        </div>
                    </noscript>
                </div>
            </div>    
            <!-- CONTENT COLUMN -->
            <div id="content-container" >
                <div id="content" class="container">
                    <div class="wrapper">
                        <div class="box_shadow">
                            <div class="box">
                                <!-- TITLE -->
                                <header class="title" {if $noTitle}style="display: none"{/if}>
                                    <h2><span id="title">{$title|escape}</span>
                                        {if $feedUrl|strlen > 0 && $feedTitle|strlen > 0}
                                            <a href="{$feedUrl|escape}" title="{$feedTitle|escape}">
                                                <i class="fa fa-rss-square"></i><!--[if IE 7]><img src="{'/images/system/feed-icon-14x14.png'|urlres}"
                                                    alt="{$feedTitle|escape}" /><![endif]-->
                                            </a>
                                        {/if}<img id="wait-loading" src="{'/images/system/loading.gif'|urlres}" style="display: none" />
                                    </h2>
                                </header>
                                {if $isHorizontalLine}    
                                    <hr id="horizontal-line" />
                                {/if}
                                <!-- MESSAGE -->
                                {include file='lib/message.tpl'}
