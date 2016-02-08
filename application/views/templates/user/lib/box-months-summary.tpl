{get_monthly_blog_summary user_id=$user->getId() liveOnly=true public_only=true actuals=true assign=summary}

{if $summary|@count > 0}
<div class="box2">
    <h3>{'Архив автора'|translate} - {$user->username|escape}</h3>
    <ul>
            {foreach from=$summary key=month item=numPosts}
        <li>
            <i class="icon-chevron-right icon-white"></i> <a class="ajax-links-summary archive-posts-summary" href="{geturl username=$user->username
                                     route='archive'
                                     year=$month|date_format:'%Y'
                                     month=$month|date_format:'%m'}">
                        {$month|dt_format:'YYYY MMMM':'YYYY-MM'}
            </a>
            ({$numPosts})
        </li>
            {/foreach}
    </ul>
</div>
{/if}