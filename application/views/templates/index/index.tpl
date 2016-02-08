{if $isAjaxRequest}
    {*Вывод сообщений пользователей*}
    {if $posts|@count > 0}
        {foreach from=$posts item=post name=posts}
            {assign var='user_id' value=$post->user_id}
            {include file='user/lib/blog-post-summary.tpl'
                    post=$post
                    user=$users.$user_id
                    linkToBlog=true}
        {/foreach}
        {include file='lib/paginator.tpl'
                pages=$pages
                urlPaginator=$url_mvc}
    {/if}
{else}
    {*Вывод заголовка*}
    {capture assign='url'}{'/index/feed'|url}{/capture}
    {include file='header.tpl' section='home'
            feedTitle="Подписаться на сообщения всех авторов"|translate
            feedUrl=$url
            paginator=true
            isHorizontalLine=false
            rightcolumn="index/lib/right-column.tpl"
    }
    
    {*Вывод сообщений*}
    <div id="blog-posts-summary">
        {if $posts|@count > 0}
            {foreach from=$posts item=post name=posts}
                {assign var='user_id' value=$post->user_id}
                {include file='user/lib/blog-post-summary.tpl'
                        post=$post
                        user=$users.$user_id
                        linkToBlog=true}
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

    {*Вывод нижнего колонтитула*}
    {include file='footer.tpl'}
{/if}