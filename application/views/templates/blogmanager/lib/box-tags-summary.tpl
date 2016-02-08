{* Выведем список меток статей пользователя *}
{get_tag_summary user_id=$identity->user_id assign=summary}

{if $summary|@count > 0}
<div class="box2">
    <h3>{'Метки автора'|translate}</h3>
    <ul>
        {foreach from=$summary item=tag}
            <li>
                <i class="icon-chevron-right icon-white"></i> <a class="ajax-links-summary blog-posts-preview" href="{'/blogmanager'|url}?tag={$tag.tag}">{$tag.label|escape}</a>
                ({$tag.count})
            </li>
        {/foreach}
    </ul>
</div>
{/if}