{get_tag_summary user_id=$user->getId() assign=summary}

{if $summary|@count > 0}
<div class="box2">
    <h3>{'Метки автора'|translate} - {$user->username|escape}</h3>
    <ul>
        {foreach from=$summary item=tag}
            <li>
                <i class="icon-chevron-right icon-white"></i> <a class="ajax-links-summary tag-posts-summary" href="{geturl route='tagspace'
                                        username=$user->username
                                        tag=$tag.tag}">
                            {$tag.label|escape}
                </a>
                ({$tag.count})
                <a href="{geturl route='feed_tag'
                                        username=$user->username
                                        tag=$tag.tag}" title="{"Подписаться на сообщения автора - '%s' для метки - '%s'"|translate:$user->username:$tag.label}">
                    <i class="fa fa-rss-square"></i><!--[if IE 7]><img src="{'/images/system/feed-icon-14x14.png'|urlres}"  alt="{$tag.label|escape}" /><![endif]-->
                </a>
            </li>
        {/foreach}
    </ul>
</div>
{/if}