<fieldset id="preview-tags" class="myfrm">
<legend>{'Метки'|translate}</legend>
<ul>
    {*foreach from=$post->getTags() item=tag*}
    {foreach from=$post->getTagsLabels() item=tag}
    <li>
        <form method="post" action="{'/blogmanager/tags'|url}">
            <div>
                    {$tag.label|escape}
                <input type="hidden" name="id" value="{$post->getId()}" />
                <input type="hidden" name="tag" value="{$tag.tag|escape}" />
                <input type="submit" class="btn btn-mini" value="{'Удалить'|translate}" name="delete" />
            </div>
        </form>
    </li>
    {foreachelse}
    <li>{'Метки не найдены'|translate}</li>
    {/foreach}
</ul>
<br />
<form method="post" class="form-inline" action="{'/blogmanager/tags'|url}">
    <div class="">
        <input type="hidden" name="id" value="{$post->getId()}" />
        <input type="text" class="span2" name="tag" />
        <input type="submit" class="btn btn-primary" value="{'Добавить метку'|translate}" name="add" />
    </div>
</form>
<br /><br />
</fieldset>