{foreach from=$tags item=tag}
<li>
    <form method="post" action="{'/blogmanager/tags'|url}">
        <div>
                {$tag.label|escape}
            <input type="hidden" name="id" value="{$tag.post_id}" />
            <input type="hidden" name="tag" value="{$tag.tag|escape}" />
            <input type="submit" class="btn btn-mini" value="{'Удалить'|translate}" name="delete" />
        </div>
    </form>
</li>
{/foreach}
