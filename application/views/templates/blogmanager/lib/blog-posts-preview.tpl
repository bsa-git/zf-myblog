
{if $month}
    <h2 style="color: black">{'Архив за'|translate} - {$month|dt_format:'YYYY MMMM':'U'}</h2>
{else}
    <h2 style="color: black">{'Метка'|translate} : "{$tagLabel}"</h2>
{/if}
{if $posts|@count == 0}
    <p class="lead">
        {'За этот месяц сообщения в блоге не найдены'|translate}
    </p>
{else}
    <dl>
        {foreach from=$posts item=post}
            <div class="teaser">
                <dt>
                    {$post->ts_created|dt_format:'EE, dd MMMM':'U'}:
                    <a href="{'/blogmanager/preview'|url}?id={$post->getId()}">
                        {$post->profile->title|escape}
                    </a>
                    {if !$post->isLive()}
                        <span class="status draft">{'не опубликован'|translate}</span>
                    {/if}
                </dt>
                <dd>
                    {$post->getTeaser(80)|escape}
                </dd>
            </div>
        {/foreach}
        {include file='lib/paginator.tpl'
                pages=$pages
                urlPaginator=$url_mvc}
    </dl>
{/if}