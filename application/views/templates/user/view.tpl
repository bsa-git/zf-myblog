{* Определим наличие YouTube Видео *}
{foreach from=$post->video item=video}
    {if $video->type == 'url-youtube'}
        {assign var=isYouTubeVideo value=true}
    {elseif $video->type == 'file-wmv'}
    {else}
        {assign var=isFlowplayer value=true}
    {/if}
    {assign var=isMedia value=true}
{/foreach}

{* Определим наличие карусели*}
{if ($post->images|@count > 0)}
    {assign var=hasCarousel value=true}
{else}
    {assign var=hasCarousel value=false}
{/if}

{* Определим наличие flowplayer *}
{if ($post->audio|@count > 0)}
    {assign var=isMedia value=true}
    {assign var=isFlowplayer value=true}
{/if}

{* Вставим заголовок страницы *}
{include file='header.tpl' section='home' 
    lightbox=true 
    maps=$post->locations|@count 
    carousel=$hasCarousel 
    flowplayer=$isFlowplayer 
    youtube=$isYouTubeVideo 
    media=$isMedia 
    windows=true
    url_lib=true
    accordion=!$report
    isHorizontalLine=true
    tabs=!$report
    ckeditor=$authenticated
    comments=true
    highlight=true
    leftcolumn='user/lib/left-column.tpl'
    rightcolumn='user/lib/right-column.tpl'
    }

{* Загрузим скрипт AccordionBox.class.js *}
{if ! $report }
    {literal}
        <script type="text/javascript">
            // Параметры для аккордиона
            addScriptParams('AccordionBox', {
                id: 'accordion',
                options : { mutuallyExclusive: false  },
                states: {disable: [], show: []}
            });
        </script>
    {/literal}
{/if}

{* Данные для передачи в JavaScript в режиме просмотра *}
{include file='lib/msg-box/view.tpl'}

<div id="post-tags">
    <strong>{'Метки'|translate}:</strong>
    {foreach from=$post->getTagsLabels() item=tag name=tags}
        <a href="{geturl route='tagspace' username=$user->username tag=$tag.tag}"
        rel="tag">{$tag.label}</a>{if !$smarty.foreach.tags.last},{/if}
    {foreachelse}
        ({'Метки не заданы'|translate})
    {/foreach}
</div>

{* Дата создания сообщения *}
<div id="post-created-date" class="post-date">
    {$post->ts_created|dt_format:'dd MMMM YYYY':'U'}
</div>

{* Установим признак авторства *} 
{if $identity->username == $user->username} 
    {assign var=is_autor value=true}
{else}
    {assign var=is_autor value=false}
{/if}  

{* Запомним содержимое сообщения *}
{capture assign=postContent}
    <div id="post-content-container">
        {include file='lib/report-buttons.tpl'
            report_visible=true
            win_visible=true
            infowin_id=''
            edit_visible=$is_autor
        }
        <div id="post-content" >
        {$post->profile->content}
        </div>
    </div>
{/capture}

{* Запомним комментарии для сообщения *}
{capture assign=postComments}
    <div id="rcheComments">
        <div id="allComment">
            {if $treeComments|@count > 0}
                <p id="no-comments" style="display: none">{'Комментарии отсутствуют'|translate}.</p>
                {include file='user/lib/comment-item.tpl'
                    treeComments=$treeComments
                }
            {else}
                <p id="no-comments" >{'Комментарии отсутствуют'|translate}. {if !$authenticated}{'Комментарий может оставить только зарегистрированный пользователь'|translate}{/if}.</p>
            {/if}
        </div>
            
        {if $authenticated}
            {$formAddComment}
            {literal}
                <script type="text/javascript">
                    addScriptParams('CKEditorHtml', {container: 'ckeditor_comment', config:{toolbar : 'min2'}});
                </script>
            {/literal}
            <p><a href="http://rche.ru/835_kommentarii-na-php-ajax-mysql.html" title="{"Пример создания комментариев"|translate}" style="margin-left: 10px; font:11px tahoma;color:#999;text-decoration:none"> &copy; www.rche.ru</a></p>
        {/if}
    </div>
{/capture}

{* Выведем сообщение *}
{if $report }
    {$postContent}
{else}
    <div id="tabs-post" class="tabs">
        {include file='user/lib/tabs-post.tpl'
            postContent=$postContent
            postComments=$postComments
            countComments= $countComments
            viewTab = $viewTab
        } 
    </div>
    {literal}
        <script type="text/javascript">
            // Параметры для закладок
            addScriptParams('Tabs', {
                id: 'tabs-post',
                animate: true // Анимация для всех закладок
                // Параметры для отдельных закладок: 
                // animate - "анимация"; opennum - "совместное открытие закладок"
                //activate : { 
                //    2: {animate: false, opennums:3},
                //    3: {animate: false, opennums:2}
                //}
            });
        </script>
    {/literal}
    {* Запомнить параметры скриптов в списке параметров *}
    {literal}
    <script type="text/javascript">
        addScriptParams('BlogComments', {container: 'rcheComments'})
    </script>
    {/literal}
{/if}
{* Сбросим выравнивание и установим нормальный поток изображений
  это нужно для правильного размещения статьи *}
<div style="clear: both"></div>

{* Обьект -> BlogView создается в скрипте -> BlogView.class.js *}
<script type="text/javascript" src="{'/js/BlogView.class.js'|urlres}"></script>
{* Запомнить параметры скриптов в списке параметров  rcheComments *}
{literal}
<script type="text/javascript">
    addScriptParams('BlogView', {containers: ['post-content','rcheComments']})
</script>
{/literal}

<noscript>
    {foreach from=$post->images item=image}
    <div class="post-image">
        <a href="{$image->createThumbnail(600, 0, $user->username)}" rel="lightbox[blog]" title="{$image->comment|escape}">
            <img src="{$image->createThumbnail(160, 0, $user->username)}" title="{$image->comment|escape}" />
        </a>
    </div>
    {/foreach}
</noscript>
<br />
{* Аккордион *}
<div id="accordion-container" style="display:none"> 
{if ! $report}
    <ul class="accordion" id="accordion">
        {* Изображения для сообщения *}
        {if $post->images|@count > 0}
            {include file='user/lib/view-images.tpl'}
        {/if}
        {* Загрузим audios *}
        {if $post->audio|@count > 0}
            {include file='user/lib/view-audio.tpl'}
        {/if}
        {* Загрузим videos *}
        {if $post->video|@count > 0}
            {include file='user/lib/view-video.tpl'}
        {/if}
        {* Загрузим locations *}
        {if $post->locations|@count > 0}
            {include file='user/lib/view-locations.tpl'}
        {/if}
    </ul>
{/if}
</div>

{include file='footer.tpl'}
