{if $videos|@count > 0}
    {foreach from=$videos item=video}
        {* Определим файл видео изображения *}
        {assign var=fileImage value=$video->type}
        {* Определим название видио *}
        {if $video->name|escape }
            {assign var=titleVideo value=$video->name|escape}
        {else}
            {assign var=titleVideo value=$video->identifier|escape}
        {/if}
        <li id="video_{$video->getId()}" >
            <img src="{"/images/media/thumbs/$fileImage.png"|urlres}?id={$video->getId()}" 
                alt="{$video->name|escape}" title="{$video->comment|escape}"/>
            <div style="width: 100%; text-align: center">#{$video->getId()}. {$titleVideo}</div>
            <form method="post" action="{'/blogmanager/video'|url}">
                <div>
                    <input type="hidden"
                        name="id" value="{$post_id}" />
                    <input type="hidden"
                        name="image" value="{$video->getId()}" />
                    <input type="submit" class="btn btn-mini" value="{'Удалить'|translate}" name="delete" />
                </div>
            </form>
        </li>
    {/foreach}
{/if}