{* Определим наличие изображений в сообщении *}
{if $post->audio|@count > 0}
    {assign var=hasAudio value=true}
{else}
    {assign var=hasAudio value=false}
{/if}
<fieldset class="preview-images myfrm" style="">
    <legend>{'Аудио'|translate}</legend>
    <div>
    {'Порядок аудио файлов можно менять с помощью перетаскивания мышью...'|translate}<br />
    {'Комментарий к аудио файлу можно изменить с помощью двойного клика мышью...'|translate}
    </div>
    <ul id="preview-audio">
        {* Список изображений муз.файлов получаемый через AJAX *}
    </ul>
</fieldset>
<fieldset id="upload-audio" class="myfrm">
    <legend>{'Загрузка аудио'|translate}</legend>
    {*<div id="audio-uploader"></div>*}
    <div style="padding-left: 20px;">
        <dt id="upload-label">
            <label class="optional">{'Загрузить файл на сервер'|translate}</label>
        </dt>
        <dd>
            <div id="audio-uploader"></div>
        </dd>
    </div>
</fieldset>