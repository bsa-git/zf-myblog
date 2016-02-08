{if $isAjaxRequest}
    {if $month}
        {include file='blogmanager/lib/blog-posts-preview.tpl'
                month=$month
                posts=$recentPosts}
     {else}
         {include file='blogmanager/lib/blog-posts-preview.tpl'
                tagLabel=$tagLabel
                posts=$recentPosts}
     {/if}
{else}
    {include file='header.tpl' section='blogmanager' 
        isHorizontalLine=true
        paginator=true
        leftcolumn='blogmanager/lib/left-column.tpl' 
        rightcolumn='blogmanager/lib/right-column.tpl'
    }
    
    <div class="teaser">
        <p class="lead">
            {'Количество сообщений на вашем блоге:'|translate} {$totalPosts}
        </p>
        <form method="get" action="{'/blogmanager/edit'|url}">
            <div class="submit">
                <input type="submit" class="btn btn-primary" value="{'Создать новое сообщение в блоге'|translate}" />
            </div>
        </form>
    </div>
    <div id="blog-posts-preview">
        {include file='blogmanager/lib/blog-posts-preview.tpl'
                 month=$month
                 posts=$recentPosts}
    </div>
    
    {* Скрипт обработки событий суммарных данных (месячных, меток) *}
    <script type="text/javascript" src="{'/js/BlogSummary.class.js'|urlres}"></script>
    {literal}
        <script type="text/javascript">
            addScriptParams('BlogSummary', {container: 'blog-posts-preview'});
        </script>
    {/literal}
    {* Параметры для обьекта Paginator *}
    {literal}
        <script type="text/javascript">
            addScriptParams('Paginator', {container: 'blog-posts-preview'});
        </script>
    {/literal}

    {include file='footer.tpl'}
{/if}
