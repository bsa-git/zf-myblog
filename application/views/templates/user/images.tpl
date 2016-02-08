{foreach from=$images key=k item=image}
    {if ($k >= $_from AND $k <= $_to)}
    <li>
        <a href="{$image->createThumbnail(600, 0, $username)}" rel="lightbox[blog]" title="{$image->comment|escape}">
            <img src="{$image->createThumbnail(120, 0, $username)}" title="{$image->comment|escape}" alt=""  />
        </a>
    </li>
    {/if}
{/foreach}
