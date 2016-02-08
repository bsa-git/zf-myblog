{include file='header.tpl' 
    section='blogmanager' 
    fileuploader=true 
    lightbox=true 
    windows=true 
    accordion=true 
    url_lib=true
    highlight=true
    leftcolumn='blogmanager/lib/left-column.tpl'
    rightcolumn='blogmanager/lib/right-column.tpl'
}

<script type="text/javascript" src="{'/js/BlogImageManager.class.js'|urlres}"></script>
<script type="text/javascript" src="{'/js/FileUploader.class.js'|urlres}"></script>
<script type="text/javascript" src="{'/js/BlogPreview.class.js'|urlres}"></script>
<script type="text/javascript" src="{'/js/BlogPreviewVideo.class.js'|urlres}"></script>
<script type="text/javascript" src="{'/js/BlogView.class.js'|urlres}"></script>

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

{* Скрипт обработки событий суммарных данных (месячных, меток) *}
<script type="text/javascript" src="{'/js/BlogSummary.class.js'|urlres}"></script>
{literal}
    <script type="text/javascript">
        addScriptParams('BlogSummary', {container: 'blog-posts-preview'});
    </script>
{/literal}

{* Запомнить параметры скриптов в списке параметров *}
<script type="text/javascript">
    // Управление изображениями в блоге
    addScriptParams('BlogImageManager', {ldelim}
        container: 'preview-images',
        post_id: {$post->getId()},
        accordion: {ldelim}id: 'accordion', section: 1, url: '/blogmanager/images'{rdelim}
    {rdelim});
    // Управление аудио в блоге
    addScriptParams('BlogImageManager', {ldelim}
        container: 'preview-audio',
        post_id: {$post->getId()},
        accordion: {ldelim}id: 'accordion', section: 2, url: '/blogmanager/audio'{rdelim}
    {rdelim});
    // Управление видео в блоге
    addScriptParams('BlogImageManager', {ldelim}
        container: 'preview-video',
        post_id: {$post->getId()},
        accordion: {ldelim}id: 'accordion', section: 3, url: '/blogmanager/video'{rdelim}
    {rdelim});
</script>

{* Загрузка изображений *}
<script type="text/javascript">
    // Загрузчик файлов 
    addScriptParams('FileUploader', {ldelim}
        container: 'images-uploader',
        settings: {ldelim}allowedExtensions: ['jpg', 'jpeg', 'png', 'gif'],sizeLimit: 1{rdelim},
        objectHandler: {ldelim}classObject:'BlogImageManager', indexObject:0{rdelim},
        ajaxRequest:{ldelim}url: '/blogmanager/images', params:{ldelim}id:{$post->getId()}{rdelim}{rdelim}
    {rdelim});
</script>
{* Загрузка музыки *}
<script type="text/javascript">
    // Загрузчик файлов 
    addScriptParams('FileUploader', {ldelim}
        container: 'audio-uploader',
        settings: {ldelim}allowedExtensions: ['mp3'],sizeLimit: 10{rdelim},
        objectHandler: {ldelim}classObject:'BlogImageManager', indexObject:1{rdelim},
        ajaxRequest:{ldelim}url: '/blogmanager/audio', params:{ldelim}id:{$post->getId()}{rdelim}{rdelim}
    {rdelim});
</script>
{* Загрузка видео *}
<script type="text/javascript">
    // Загрузчик файлов 
    addScriptParams('FileUploader', {ldelim}
        container: 'video-uploader',
        settings: {ldelim}allowedExtensions: ['mp3','swf','flv','mov','mp4','m4v','f4v','wmv'], sizeLimit: 500, type: 'IFrame'{rdelim},
        objectHandler: {ldelim}classObject:'BlogImageManager', indexObject:2{rdelim},
        ajaxRequest:{ldelim}url: '/blogmanager/video', params:{ldelim}id:{$post->getId()}{rdelim}{rdelim}
    {rdelim});
    // Обработчик видеофайлов 
    addScriptParams('BlogPreviewVideo', {ldelim}
        container_video: 'preview-video',
        container_upload: 'upload-video',
        post_id: {$post->getId()}
    {rdelim});
    // Предварительный просмотр статьи
    addScriptParams('BlogPreview', {ldelim}{rdelim});
    
    // Просмотр статьи
    addScriptParams('BlogView', {ldelim}
        containers: ['preview-content']
    {rdelim});
</script>


{* Сообщения/Данные для JavaScript в режиме предварительного просмотра сообщения *}
{include file='lib/msg-box/preview.tpl'}

<form method="post"
      class="myfrm"
      action="{'/blogmanager/setstatus'|url}"
      id="status-form">

    <div class="preview-status">
        <input type="hidden" name="id" value="{$post->getId()}" />
    {if $post->isLive()}
        {*<div class="status live">
            <br />
            <p class="lead">{'Это сообщение опубликовано на вашем блоге. Чтобы снять с публикации сообщение нажмите кнопку <strong>Снять с публикации</strong>, которая расположена ниже.'|translate}</p>
        </div>*}
        <div class="alert alert-block alert-success">
            <p class="lead"><strong>{'Поздравляем'|translate}!</strong><br />
            {'Это сообщение опубликовано на вашем блоге. Чтобы снять с публикации сообщение нажмите кнопку <strong>Снять с публикации</strong>, которая расположена ниже.'|translate}</p>
        </div>
        <div>
            <input type="submit" class="btn btn-success" value="{'Снять с публикации'|translate}"
                    name="unpublish" id="status-unpublish" />
            <input type="submit" class="btn btn-primary" value="{'Редактировать'|translate}"
                    name="edit" id="status-edit" />
            <input type="submit" class="btn" value="{'Удалить'|translate}"
                    name="delete" id="status-delete" />
        </div>
    {else}
        {*<div class="status draft">
            <br />
            <p class="lead">{'Это сообщение еще не опубликовано на вашем блоге. Чтобы опубликовать его на вашем блоге, нажмите кнопку <strong>Опубликовать</strong>, расположенную ниже.'|translate}</p>
        </div>*}
        <div class="alert alert-block">
            <p class="lead"><strong>{'Внимание'|translate}!</strong><br />
            {'Это сообщение еще не опубликовано на вашем блоге. Чтобы опубликовать его на вашем блоге, нажмите кнопку <strong>Опубликовать</strong>, расположенную ниже.'|translate}</p>
        </div>
        <div>
            <input type="submit" class="btn btn-success" value="{'Опубликовать'|translate}"
                    name="publish" id="status-publish" />
            <input type="submit" class="btn btn-primary" value="{'Редактировать'|translate}"
                    name="edit" id="status-edit" />
            <input type="submit" class="btn" value="{'Удалить'|translate}"
                    name="delete" id="status-delete" />
        </div>
    {/if}
    </div>
</form>
<br />
{* Предварит.просмотр при отключенном javascript *}
<noscript>
    {* Метки для сообщения *}
    {include file='blogmanager/lib/preview-tags.tpl'}
    {* Изображения для сообщения *}
    {include file='blogmanager/lib/preview-images-noscript.tpl'}
</noscript>

{* Предварит.просмотр при включенном JS *}
{* Аккордион *}
<div id="accordion-container" style="display:none"> 
<ul class="accordion" id="accordion">
    <li class="section">
        <a href="0" class="title">
            <img src="{'/images/system/link.gif'|urlres}"/> 
            {"Метки"|translate} ({$post->getTagsLabels()|@count})
        </a>
        <div class="toggle">
            <div class="accordion-toggle-container" style="display:none}">
            {* Метки для сообщения *}
            {include file='blogmanager/lib/preview-tags.tpl'}
            </div>
        </div>
    </li>
    <li class="section">
        <a href="1" class="title">
            <img src="{'/images/system/views16x16.png'|urlres}"/> 
            {"Изображения"|translate} ({$post->images|@count})
        </a>
        <div class="toggle">
            <div class="accordion-toggle-container" style="display:none">
            {* Изображения для сообщения *}
            {include file='blogmanager/lib/preview-images.tpl'}
            </div>
        </div>
    </li>
    <li class="section">
        <a href="2" class="title">
            <img src="{'/images/system/music18x18.png'|urlres}"/> 
            {"Аудио"|translate} ({$post->audio|@count})
        </a>
        <div class="toggle">
            <div class="accordion-toggle-container" style="display:none">
            {* Аудио для сообщения *}
            {include file='blogmanager/lib/preview-audio.tpl'}
            </div>
        </div>
    </li>
    <li class="section">
        <a href="3" class="title">
            <img src="{'/images/system/film18x18.png'|urlres}"/> 
            {"Видео"|translate} ({$post->video|@count})
        </a>
        <div class="toggle">
            <div class="accordion-toggle-container" style="display:none">
            {* Видео для сообщения *}
            {include file='blogmanager/lib/preview-video.tpl'}
            </div>
        </div>
    </li>
    <li class="section">
        <a href="4" class="title">
            <img src="{'/images/system/Weblink.png'|urlres}"/> 
            {"Географические координаты"|translate} ({$post->locations|@count})
        </a>
        <div class="toggle">
            <div class="accordion-toggle-container" style="display:none">
            {* Географические координаты для сообщения *}
            {include file='blogmanager/lib/preview-locations.tpl'}
            </div>
        </div>
    </li>
</ul>
</div>
<br />        
<div class="preview-date" style="margin-left:10px;">
    {$post->ts_created|date_format:'%Y-%m-%d'}
</div>

<div id="preview-content-container">
    {include file='lib/report-buttons.tpl'
        report_visible=true
        win_visible=true
        infowin_id=''
        view_visible=true
    }
    <div id="preview-content" >
    {$post->profile->content}
    </div>
</div>

{include file='footer.tpl'}