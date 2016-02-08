{* Определим наличие видео в сообщении *}
{if $post->video|@count > 0}
    {assign var=hasVideo value=true}
{else}
    {assign var=hasVideo value=false}
{/if}
<fieldset class="preview-images myfrm" style="">
    <legend>{'Видео'|translate}</legend>
    <div>
    {'Добавить медиа можно также с помощью URL'|translate} <a href="#" id="add-media-via-url" class="help-info-win">{'Подробнее...'|translate}</a><br />
    {'Порядок медиа файлов можно менять с помощью перетаскивания мышью...'|translate}<br />
    {'Комментарий к медиа файлу можно изменить с помощью двойного клика мышью...'|translate}
    ({'Название фильма и его описание можно разделить символом - "#"'|translate})
    </div>
    <ul id="preview-video">
        {* Список изображений видеофайлов получаемый через AJAX *}
    </ul>
</fieldset>
<fieldset id="upload-video" class="myfrm">
    <legend>{'Загрузка видео'|translate}</legend>
    <div style="padding-left: 20px; float: left">
        <dt id="upload-label">
            <label class="optional">{'Загрузить файл на сервер'|translate}</label>
        </dt>
        <dd>
            <div id="video-uploader"> </div>
        </dd>
    </div>
    <div style="padding-left: 20px; float: left ">
        <dt id="comment-label">
            <label for="url-video" class="optional">{'URL'|translate}</label>
        </dt>
        <dd>
            <form id="blogpostvideo-form" class="form-inline" method="post" action="{'/blogmanager/video'|url}">
            <div>
                <input type="text"  name="url-video" size=50px />
                <input type="submit" class="btn btn-primary" value="{'Ввод'|translate}">
            </div>
            </form>
            
        </dd>
    </div>
</fieldset>