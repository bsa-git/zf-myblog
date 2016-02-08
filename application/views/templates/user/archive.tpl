{if $isAjaxRequest}
    <h2 style="color: black">{'Архив за'|translate} - {$month|dt_format:'YYYY MMMM':'U'}</h2>
    {if $posts|@count > 0}
        {foreach from=$posts item=post name=posts}
            {include file='user/lib/blog-post-summary.tpl' post=$post}
        {/foreach}
        {include file='lib/paginator.tpl'
                pages=$pages
                urlPaginator=$url_mvc}
    {/if}
{else}
    {include file='header.tpl' section='home' 
        isHorizontalLine=true
        paginator=true
        leftcolumn='user/lib/left-column.tpl'
        rightcolumn='user/lib/right-column.tpl'
    }

    {*Вывод сообщений пользователей*}
    <div id="archive-posts-summary">
        <h2 style="color: black">{'Архив за'|translate} - {$month|dt_format:'YYYY MMMM':'U'}</h2>
        {if $posts|@count > 0}
            {foreach from=$posts item=post name=posts}
                {include file='user/lib/blog-post-summary.tpl' post=$post}
            {/foreach}
            {include file='lib/paginator.tpl'
                pages=$pages
                urlPaginator=$url_mvc}
        {/if}
    </div>

    {* Скрипт обработки событий суммарных данных (месячных, меток) *}
    <script type="text/javascript" src="{'/js/BlogSummary.class.js'|urlres}"></script>
    {literal}
        <script type="text/javascript">
            addScriptParams('BlogSummary', {container: 'archive-posts-summary'});
        </script>
    {/literal}
    {* Параметры для обьекта Paginator *}
    {literal}
        <script type="text/javascript">
            addScriptParams('Paginator', {container: 'archive-posts-summary'});
        </script>
    {/literal}

    {include file='footer.tpl'}
{/if}