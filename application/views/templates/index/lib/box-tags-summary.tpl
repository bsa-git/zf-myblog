{get_tag_summary user_id=0 assign=summary}

{if $summary|@count > 0}
<div class="box2">
    <h3>{'Метки всех авторов'|translate}</h3>
    <ul>
    {foreach from=$summary item=tag}
        <li>
            <i class="icon-chevron-right icon-white"></i> <a class="ajax-links-summary tag-posts-summary" href="{geturl route='tags_all'
                                     tag=$tag.tag}">
                        {$tag.label|escape}
            </a>
            ({$tag.count})
            <a href="{geturl route='feed_tag_all'
                                     tag=$tag.tag}" title="{"Подписаться на сообщения для метки - '%s'"|translate:$tag.label}">
                <i class="fa fa-rss-square"></i><!--[if IE 7]><img src="{'/images/system/feed-icon-14x14.png'|urlres}" alt="{$tag.label|escape}" /><![endif]-->
            </a>
        </li>
    {/foreach}
    </ul>
</div>
{/if}