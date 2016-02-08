{if $isAjaxRequest}
    {if $posts|@count > 0}
        {foreach from=$posts item=post name=posts}
            {include file='user/lib/blog-post-summary.tpl' post=$post}
        {/foreach}
        {include file='lib/paginator.tpl'
            pages=$pages
            urlPaginator=$url_mvc}
    {/if}
{else}
    {capture assign='url'}{geturl route='user'
                                username=$user->username
                                action='feed'}{/capture}
    {include file='header.tpl' section='home'
            feedTitle="Подписаться на все сообщения автора - '%s'"|translate:$user->username
            feedUrl=$url
            isHorizontalLine=true
            paginator=true
            leftcolumn='user/lib/left-column.tpl'
            rightcolumn='user/lib/right-column.tpl'
    }

    {*Вывод сообщений пользователей*}
    <div id="blog-posts-summary">
        {if $posts|@count > 0}
            {foreach from=$posts item=post name=posts}
                {include file='user/lib/blog-post-summary.tpl' post=$post}
            {/foreach}
            {include file='lib/paginator.tpl'
                pages=$pages
                urlPaginator=$url_mvc}
        {/if}
    </div>
    {* Параметры для обьекта Paginator *}
    {literal}
        <script type="text/javascript">
            addScriptParams('Paginator', {container: 'blog-posts-summary'});
        </script>
    {/literal}

    {include file='footer.tpl'}
{/if}