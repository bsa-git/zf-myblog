<div class="box3" id="search">
    <form class="ajax-links-summary search-posts-summary" method="get" action="{'/search/index'|url}">
        <div>
            <input type="text" name="q" value="{$q|escape}" id="search-query" />
            <input class="btn" type="submit" value="{'Поиск'|translate}" />
        </div>
    </form>
</div>