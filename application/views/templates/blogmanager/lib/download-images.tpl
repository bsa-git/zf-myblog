{if $images|@count > 0}
    {foreach from=$images item=image}
    <li id="image_{$image->getId()}">
          <img src="{$image->createThumbnail(200, 65)}"
            alt="" title="{$image->comment|escape}" />
        <form method="post" action="{'/blogmanager/images'|url}">
            <div>
                <input type="hidden"
                    name="id" value="{$post_id}" />
                <input type="hidden"
                    name="image" value="{$image->getId()}" />
                <input type="submit" class="btn btn-mini" value="{'Удалить'|translate}" name="delete" />
            </div>
        </form>
    </li>
    {/foreach}
{/if}