{* Определим наличие изображений в сообщении *}
{if $post->images|@count > 0}
    {assign var=hasImages value=true}
{else}
    {assign var=hasImages value=false}
{/if}
<fieldset class="preview-images myfrm" style="">
    <legend>{'Изображения'|translate}</legend>
    <div>
    {'Порядок изображений можно менять с помощью перетаскивания мышью...'|translate}<br />
    {'Комментарий к изображению можно изменить с помощью двойного клика мышью...'|translate}
    </div>
    <ul id="preview-images">
        {* Список изоображений получаемый через AJAX *}
    </ul>
</fieldset>
<fieldset id="upload-images" class="myfrm">
    <legend>{'Загрузка изображений'|translate}</legend>
    <div style="padding-left: 20px;">
        <dt id="upload-label">
            <label class="optional">{'Загрузить файл на сервер'|translate}</label>
        </dt>
        <dd>
            <div id="images-uploader"></div>
        </dd>
    </div>
</fieldset>