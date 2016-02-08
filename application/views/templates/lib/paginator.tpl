<center>
    <div id="paginator-container" class="my-pager" style="{if $pages->pageCount == 1}display: none{/if}">
    <span class="my-pager-message">
        {'с'|translate} <strong><span id="pages-from">{$pages->firstItemNumber}</span></strong> {'по'|translate} 
        <strong><span id="pages-to">{$pages->lastItemNumber}</span></strong> {'из'|translate} 
        <strong><span id="pages-total">{$pages->totalItemCount}</span></strong>
    </span>
    <table class="my-pager-table" border="0" cellpadding="0" cellspacing="0">
        <tbody>
            <tr>
                <td>
                    <div class="my-pager-separator">&nbsp;</div>
                </td>
                <td>
                    {if $pages->current == $pages->first}
                        <div class="first-page-disabled">&nbsp;</div>
                    {else}
                        <a id="page-first" class="my-pager-control" href="{'/'|urlres}{$urlPaginator}?page={$pages->first}">
                            <div class="first-page">&nbsp;</div>
                        </a>
                    {/if}
                </td>
                <td>
                    {if $pages->previous}
                        <a id="page-prev" class="my-pager-control" href="{'/'|urlres}{$urlPaginator}?page={$pages->previous}">
                            <div class="previous-page">&nbsp;</div>
                        </a>
                    {else}
                        <div class="previous-page-disabled">&nbsp;</div>
                    {/if}
                </td>
                <td>
                    <div class="my-pager-separator">&nbsp;</div>
                </td>
                <td>
                    <strong><span class="my-pager-message">{'Страница'|translate}:</span></strong>
                </td>
                <td>
                    <input type="text" name="page-input" id="page-input" value="{$pages->current}" class="my-page-input" size="3" maxlength="3">
                </td>
                <td>
                    <span class="my-pager-message">{'из'|translate}&nbsp;<strong>{$pages->pageCount}</strong></span>
                </td>
                <td>
                    <div class="my-pager-separator">&nbsp;</div>
                </td>
                <td>
                    {if $pages->next}
                        <a id="page-next" class="my-pager-control" href="{'/'|urlres}{$urlPaginator}?page={$pages->next}">
                            <div class="next-page">&nbsp;</div>
                        </a>
                    {else}
                        <div class="next-page-disabled">&nbsp;</div>
                    {/if}
                </td>
                <td>
                    {if $pages->current == $pages->last}
                        <div class="last-page-disabled">&nbsp;</div>
                    {else}
                        <a id="page-last" class="my-pager-control" href="{'/'|urlres}{$urlPaginator}?page={$pages->last}">
                            <div class="last-page">&nbsp;</div>
                        </a>
                    {/if}
                </td>
            </tr>
        </tbody>
    </table>
    <table class="my-pager-table" border="0" cellpadding="0" cellspacing="0" style="">
        <tbody>
            <tr>
                <td>
                    <div id="page-loader" class="my-pager-loader" style="display: none; ">&nbsp;</div>
                </td>
                <td>
                    <div class="my-pager-separator">&nbsp;</div>
                </td>
                <td>
                    <strong><span class="my-pager-message">{'На странице'|translate}:</span></strong>
                </td>
                <td>
                    <input type="text" name="itemCountPerPage" id="itemCountPerPage" value="{$pages->itemCountPerPage}" class="my-page-input" size="3" maxlength="3">
                </td>
            </tr>
        </tbody>
    </table>
</div>
</center>
