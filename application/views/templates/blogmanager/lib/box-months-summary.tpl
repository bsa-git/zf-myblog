{get_monthly_blog_summary user_id=$identity->user_id assign=summary}

{if $summary|@count > 0}
    <div class="box2">
        <h3>{'Архив вашего блога'|translate}</h3>
        <ul>
            {foreach from=$summary key=month item=numPosts}
                <li>
                    <i class="icon-chevron-right icon-white"></i> <a class="ajax-links-summary blog-posts-preview" href="{'/blogmanager'|url}?month={$month}">
                        {$month|dt_format:'YYYY MMMM':'YYYY-MM'}
                    </a>
                    ({$numPosts})
                </li>
            {/foreach}
        </ul>
    </div>
{else}
    <div id="blog-months-summary" class="box2">
        <h3>{'Архив вашего блога отсутствует'|translate} </h3>
    </div>
{/if}