{if $isAjaxRequest}
    {if $posts|@count > 0}
        {capture assign='url'}{geturl route='feed_tag'
                                username=$user->username
                                tag=$tag}{/capture}
        {assign var='feed_Title' value="Подписаться на сообщения автора - '%s' для метки - '%s'"|translate:$user->username:$tagLabel}
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
        {foreach from=$posts item=post name=posts key=post_id}
            {include file='user/lib/blog-post-summary.tpl'
                    post=$post}

            {if $smarty.foreach.posts.last}
                {assign var=date value=$post->ts_created}
            {/if}
        {/foreach}
        {include file='lib/paginator.tpl'
                pages=$pages
                urlPaginator=$url_mvc}
    {/if}
{else}
    {*Заголовок*}
    {include file='header.tpl' section='home' 
        isHorizontalLine=true 
        paginator=true
        leftcolumn='user/lib/left-column.tpl'
        rightcolumn='user/lib/right-column.tpl'
    }

    <div id="tag-posts-summary">
        {capture assign='url'}{geturl route='feed_tag'
                                username=$user->username
                                tag=$tag}{/capture}
        {assign var='feed_Title' value="Подписаться на сообщения автора - '%s' для метки - '%s'"|translate:$user->username:$tagLabel}
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
            {foreach from=$posts item=post name=posts key=post_id}
                {include file='user/lib/blog-post-summary.tpl'
                        post=$post}

                {if $smarty.foreach.posts.last}
                    {assign var=date value=$post->ts_created}
                {/if}
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