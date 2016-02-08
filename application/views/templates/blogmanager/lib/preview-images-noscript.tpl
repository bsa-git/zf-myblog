{* Определим наличие изображений в сообщении *}
{if $post->images|@count > 0}
    {assign var=hasImages value=true}
{else}
    {assign var=hasImages value=false}
{/if}
{if ! $report }
<fieldset id="preview-images" class="myfrm" style="{if !$hasImages}; display:none{/if}">
    <legend>{'Изображения'|translate}</legend>
    <div  id="preview-images-comment" style="display: none">
    {'Порядок изображений можно менять с помощью перетаскивания мышью...'|translate}<br />
    {'Комментарий к изображению можно изменить с помощью двойного клика мышью...'|translate}
    </div>
    {if $post->images|@count > 0}
    <ul id="preview-images">
        {foreach from=$post->images item=image}
        <li id="image_{$image->getId()}">
              <img src="{$image->createThumbnail(200, 65)}"
                alt="{$image->filename|escape}" title="{$image->comment|escape}" />
            <form method="post" action="{'/blogmanager/images'|url}">
                <div>
                    <input type="hidden"
                        name="id" value="{$post->getId()}" />
                    <input type="hidden"
                        name="image" value="{$image->getId()}" />
                    <input type="submit" class="btn btn-primary" value="{'Удалить'|translate}" name="delete" />
                </div>
            </form>
        </li>
        {/foreach}
    </ul>
    {/if}
</fieldset>
{/if}
<fieldset id="upload-images" class="myfrm">
    <legend>{'Загрузка изображений'|translate}</legend>
    <div id="file-uploader">       
        <form method="post"
            action="{'/blogmanager/images'|url}"
            enctype="multipart/form-data">
            <div>
                <input type="hidden" name="id" value="{$post->getId()}" />
                <input type="hidden" name="ajax_upload" id="ajax_upload" value="no" />
                <div style="float: left">{$formBlogPostImage->image}</div>
                <div style="padding-left: 20px; float: left">
                    <dt id="upload-label">
                        <label for="upload" class="optional">{'Загрузить файл на сервер'|translate}</label>
                    </dt>
                    <dd>
                        <input type="submit" class="btn btn-primary" value="{'Загрузить'|translate}" name="upload" />
                    </dd>
                </div>
                <div style="padding-left: 20px; float: left ">
                    <dt id="comment-label">
                        <label for="comment" class="optional">{'Комментарий к изображению'|translate}</label>
                    </dt>
                    <dd>
                        <input type="text"  name="comment" size=50px />
                    </dd>
                </div>
            </div>
        </form>
    </div>
</fieldset>