{capture assign='url'}{geturl username=$user->username
                              url=$post->url
                              route='post'}{/capture}

{* Установим признак авторства *} 
{if $identity->username == $user->username} 
    {assign var=is_autor value=true}
{else}
    {assign var=is_autor value=false}
{/if}                              
<div class="teaser">
    <h3>
        <a href="{$url|escape}?view=post" class="entry-title" rel="bookmark">
            {$post->profile->title}
        </a>
    </h3>

    <div class="teaser-date">
        {$post->ts_created|dt_format:'dd MMMM YYYY':'U'}
    </div>

    {if $post->images|@count > 0}
        {assign var=image value=$post->images|@current}
        <div class="teaser-image">
            <a href="{$url|escape}?view=post">
                <img src="{$image->createThumbnail(100, 0, $user->username)}" alt="" title="{$image->comment|escape}" />
            </a>
        </div>
    {/if}

    <div class="teaser-content">
        {if $post->profile->description}
            {$post->profile->description}
        {else}
            {$post->getTeaser(255)}
        {/if}
        
    </div>
   
   
    <div class="teaser-links">
        {if $is_autor}
            <a href="{'/blogmanager/preview'|url}?id={$post->getId()}" style="text-decoration: none" title="{'Редактировать'|translate}" >
                <i class="fa fa-pencil-square-o fa-lg"></i><!--[if IE 7]><img src="{'/images/system/draw.png'|urlres}" /><![endif]-->
            </a>
            |
        {/if}
        <a href="{$url|escape}?view=post">{'Читать больше...'|translate}</a>
        {if $linkToBlog}
            |
            <a href="{geturl username=$user->username route='user'}">
                {'Автор статьи'|translate} {$user->username|escape}
            </a>
        {/if}
        {if $post->getCommentsCount()}
            |
            <a href="{$url|escape}?view=comments">{'Комментарии'|translate} ({$post->getCommentsCount()})</a>
        {/if}    
        {if $post->getTagsLabels()|@count > 0}
            {assign var=tags value=$post->getTagsLabels()}
            | 
            {'Метки'|translate}: 
            {foreach from=$tags item=tag name=tags_ }
                {if $linkToBlog}
                    <a class="ajax-links-summary tag-posts-summary" href="{geturl route='tags_all' tag=$tag.tag}">{$tag.label|escape}</a>
                {else}
                    <a class="ajax-links-summary tag-posts-summary" href="{geturl route='tagspace' username=$user->username tag=$tag.tag}">{$tag.label|escape}</a>
                {/if}
                {if ! $smarty.foreach.tags_.last}, {/if}
            {/foreach}
        {/if}
    </div>
</div>
