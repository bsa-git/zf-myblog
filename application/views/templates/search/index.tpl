{if $isAjaxRequest}
    {if $search.performed}
        {if $search.total == 0}
            <p class="lead">
                {'Для этого поиска результаты не были найдены'|translate}
            </p>
        {else}
            <p class="lead">
                {'Отображение результатов'|translate} {$search.start}-{$search.finish} {'из'|translate} {$search.total}
            </p>
            <h2 style="color: black">{'Результаты поиска для запроса'|translate} - "{$q|escape}"</h2>
            {foreach from=$search.results item=post}
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
        <p class="lead">
            {'Для поиска необходимого содержания воспользуйтесь формой поиска, находящейся в левой колонке'|translate}
        </p>
    {/if}
{else}
    {include file='header.tpl' section='home' 
        isHorizontalLine=true
        paginator=true
        windows=true
        rightcolumn='index/lib/right-column.tpl'
    }
    {include file='lib/report-buttons.tpl'
        report_visible=false
        win_visible=false
        infowin_id='full-text-search'
    }
    
    {*Загрузка скриптов класса BlogView*}
    <script type="text/javascript" src="{'/js/BlogView.class.js'|urlres}"></script>
    {* Запомнить параметры скриптов в списке параметров *}
    {literal}
    <script type="text/javascript">
            addScriptParams('BlogView', {});
    </script>
    {/literal}
    
    <div id="search-posts-summary"> 
        {if $search.performed}
            {if $search.total == 0}
                <p class="lead">
                    {'Для этого поиска результаты не были найдены'|translate}
                </p>
            {else}
                <p class="lead">
                    {'Отображение результатов'|translate} {$search.start}-{$search.finish} {'из'|translate} {$search.total}
                </p>
                <h2 style="color: black">{'Результаты поиска для запроса'|translate} - "{$q|escape}"</h2>
                {foreach from=$search.results item=post}
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
            <p class="lead">
                {'Для поиска необходимого содержания воспользуйтесь формой поиска, находящейся в левой колонке'|translate}
            </p>
        {/if}
    </div>
    {* Скрипт обработки событий суммарных данных (месячных, меток) *}
    <script type="text/javascript" src="{'/js/BlogSummary.class.js'|urlres}"></script>
    {literal}
        <script type="text/javascript">
            addScriptParams('BlogSummary', {container: 'search-posts-summary'});
        </script>
    {/literal}
    {* Параметры для обьекта Paginator *}
    {literal}
        <script type="text/javascript">
            addScriptParams('Paginator', {container: 'search-posts-summary'});
        </script>
    {/literal}
    {include file='footer.tpl'}
{/if}