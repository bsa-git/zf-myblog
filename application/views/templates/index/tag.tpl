{if $isAjaxRequest}
    {if $posts|@count > 0}
        {capture assign='url'}{geturl route='feed_tag_all' tag=$tag}{/capture}
        {assign var='feed_Title' value="Подписаться на сообщения всех авторов для метки - '%s'"|translate:$tagLabel}
        {assign var='feed_Url' value=$url}
        <link rel="alternate" type="application/atom+xml" title="{$feed_Title|escape}" href="{$feed_Url|escape}" />
        <h2 style="color: black">
            {'Метка'|translate} : "{$tagLabel}"
            <a href="{$feed_Url|escape}" title="{$feed_Title|escape}">
                {*<img src="{'/images/system/feed-icon-14x14.png'|urlres}"
                    alt="{$feed_Title|escape}" />*}
                <i class="fa fa-rss-square"></i><!--[if IE 7]><img src="{'/images/system/feed-icon-14x14.png'|urlres}"
                alt="{$feedTitle|escape}" /><![endif]-->
            </a>
        </h2>
        {foreach from=$posts item=post name=posts}
            {assign var='user_id' value=$post->user_id}
            {include file='user/lib/blog-post-summary.tpl'
                    post=$post
                    user=$users.$user_id
                    linkToBlog=true
                    }
        {/foreach}
        {include file='lib/paginator.tpl'
                pages=$pages
                urlPaginator=$url_mvc}
    {/if}
{else}
    {include file='header.tpl' 
        section='home' 
        isHorizontalLine=true
        paginator=true
        rightcolumn='index/lib/right-column.tpl'
    }

    {*Вывод сообщений пользователей*}
    <div id="tag-posts-summary">
        {capture assign='url'}{geturl route='feed_tag_all' tag=$tag}{/capture}
        {assign var='feed_Title' value="Подписаться на сообщения всех авторов для метки - '%s'"|translate:$tagLabel}
        {assign var='feed_Url' value=$url}
        <link rel="alternate" type="application/atom+xml" title="{$feed_Title|escape}" href="{$feed_Url|escape}" />
        <h2 style="color: black">
            {'Метка'|translate} : "{$tagLabel}"
            <a href="{$feed_Url|escape}" title="{$feed_Title|escape}">
                {*<img src="{'/images/system/feed-icon-14x14.png'|urlres}"
                    alt="{$feed_Title|escape}" />*}
                <i class="fa fa-rss-square"></i><!--[if IE 7]><img src="{'/images/system/feed-icon-14x14.png'|urlres}"
                alt="{$feedTitle|escape}" /><![endif]-->
            </a>
        </h2>
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
    {* Скрипт обработки событий суммарных данных (месячных, меток) *}
    <script type="text/javascript" src="{'/js/BlogSummary.class.js'|urlres}"></script>
    {literal}
        <script type="text/javascript">
            addScriptParams('BlogSummary', {container: 'tag-posts-summary'});
        </script>
    {/literal}
    {* Параметры для обьекта Paginator *}
    {literal}
        <script type="text/javascript">
            addScriptParams('Paginator', {container: 'tag-posts-summary'});
        </script>
    {/literal}

    {include file='footer.tpl'}
{/if}