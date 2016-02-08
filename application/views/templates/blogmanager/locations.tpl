{include file='header.tpl' 
    section='blogmanager' 
    maps=true 
    ckeditor=true 
    lightbox=true 
    windows=true
    leftcolumn='blogmanager/lib/left-column.tpl'
    rightcolumn='blogmanager/lib/right-column.tpl'
}

{* Локализованные сообщения для передачи в JavaScript при работе с геокоординатами *}
{include file='lib/msg-box/locations.tpl'}

<fieldset id="add-location" class="myfrm">
    <legend>{'Добавить координату'|translate}</legend>
    <form class="form-inline" method="post"
          action="{'/blogmanager/locationsmanage'|url}"
          id="location-add">

        <div>
            <input type="hidden" name="post_id" value="{$post->getId()}" />
            {'Добавить новую точку географических координат'|translate}:
            <input type="text" name="location" size="60" />
            <input type="submit" class="btn btn-primary" name="save_location" id="save_location" value="{'Добавить'|translate}" />

        </div>
    </form>
</fieldset>
<fieldset id="location-content" class="myfrm" style="display:none">
    <legend>{'Редактировать содержимое'|translate}</legend>
    {*<textarea name="ckeditor_content" id="ckeditor_content" rows="12" cols="70" ></textarea>*}
    <!-- This div will hold the editor. -->
    <div id="ckeditor_content"></div>
    <br />
    <input type="submit" class="btn btn-primary" name="save_content" id="save_content" value="{'Сохранить изменения'|translate}" />
    <input type="submit" class="btn" name="cancel_content" id="cancel_content" value="{'Отмена'|translate}" />
</fieldset>

<label class="checkbox">
    <input type="checkbox" name="is_get_address" id="is_get_address" value="1" >{'Автоматически определять адрес при перемещении маркера координаты'|translate}
</label>

{*<p><input type="checkbox" name="is_get_address" id="is_get_address" value="1" >{'Автоматически определять адрес при перемещении маркера координаты'|translate}</p>*}
<p>{'Описание к координате можно изменить с помощью двойного клика мышью...'|translate}</p>
<div id="location-manager"></div>
{literal}
<script type="text/javascript">
    addScriptParams('BlogLocationManager', {container: 'location-manager', form: 'location-add'});
</script>
{/literal}

{* Скрипт обработки событий суммарных данных (месячных, меток) *}
<script type="text/javascript" src="{'/js/BlogSummary.class.js'|urlres}"></script>
{literal}
    <script type="text/javascript">
        addScriptParams('BlogSummary', {container: 'blog-posts-preview'});
    </script>
{/literal}


{include file='footer.tpl'}
